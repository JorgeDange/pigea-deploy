<?php

namespace App\Http\Controllers\Admin;

use App\Mail\NewsletterSubscricao;
use App\Mail\NewsletterArtigo;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsletterEdition;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Gestão da Newsletter do PIGEA (doc/newsletter.md — FASE N1).
 *
 * Área exclusiva de `role:admin` (dados pessoais de contacto — PII):
 *   GET /gestao-pigea/newsletter              — estatísticas + lista (+ CSV)
 *   POST /newsletter/adicionar                — adicionar manualmente (active)
 *   POST /newsletter/reenviar/{id}            — reenviar confirmação a UM pendente
 *   POST /newsletter/reenviar-pendentes       — reenviar confirmação a TODOS os pendentes
 *   DELETE /newsletter/{id}                   — remover linha
 *   GET /newsletter/exportar.csv              — exportar por estado (?estado=active|pending|cancelled, BOM do Excel)
 *
 * A subscrição pública e a dupla ótica ficam em Frontend\NewsletterController.
 */
class NewsletterAdminController extends Controller
{
    public function index(Request $request): View
    {
        $estado = (string) $request->query('estado', '');
        if (! in_array($estado, ['', 'pending', 'active', 'cancelled'], true)) {
            $estado = '';
        }

        $busca = trim((string) $request->query('q', ''));

        $consulta = NewsletterSubscriber::query()->orderByDesc('created_at');

        if ($estado !== '') {
            $consulta->where('status', $estado);
        }

        if ($busca !== '') {
            $consulta->whereRaw('LOWER(email) LIKE ?', ['%' . mb_strtolower($busca) . '%']);
        }

        $subscritores = $consulta->paginate(15)->withQueryString();

        $total = NewsletterSubscriber::count();
        $ativos = NewsletterSubscriber::active()->count();
        $pendentes = NewsletterSubscriber::pending()->count();
        $cancelados = NewsletterSubscriber::cancelados()->count();

        return view('admin.newsletter', [
            'subscritores' => $subscritores,
            'estado' => $estado,
            'busca' => $busca,
            'total' => $total,
            'ativos' => $ativos,
            'pendentes' => $pendentes,
            'cancelados' => $cancelados,
            'taxaConfirmacao' => $total > 0 ? (int) round($ativos / $total * 100) : 0,
            'rotulos' => ['active' => 'Confirmados', 'pending' => 'Pendentes', 'cancelled' => 'Cancelados'],
        ]);
    }

    /** Confirmar UM subscritor pendente. */
    public function confirmar(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::pending()->findOrFail($id);
        $subscritor->update(['status' => 'active', 'confirmed_at' => now()]);

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => '«' . $subscritor->email . '» confirmado.',
        ]);
    }

    /** Cancelar um subscritor (ativo ou pendente) — sai da lista de envio. */
    public function cancelar(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::whereIn('status', ['active', 'pending'])->findOrFail($id);
        $subscritor->update(['status' => 'cancelled']);

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => '«' . $subscritor->email . '» cancelado.',
        ]);
    }

    /** Reabilitar um cancelado — volta para pendente (confirmação outra vez). */
    public function reabilitar(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::cancelados()->findOrFail($id);
        $subscritor->update(['status' => 'pending', 'token' => Str::random(64), 'confirmed_at' => null]);
        \Mail::to($subscritor->email)->send(new NewsletterSubscricao($subscritor->fresh()));

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => '«' . $subscritor->email . '» reabilitado — email de confirmação reenviado.',
        ]);
    }

    /** Confirmar TODOS os pendentes. */
    public function confirmarPendentes(): RedirectResponse
    {
        $pendentes = NewsletterSubscriber::pending()->get();
        foreach ($pendentes as $p) {
            $p->update(['status' => 'active', 'confirmed_at' => now()]);
        }

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => $pendentes->count() . ' subscritor(es) ativos.',
        ]);
    }

    /** Reenviar confirmação (um subscritor pendente). */
    public function reenviarConfirmacao(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::pending()->findOrFail($id);
        $subscritor->update(['token' => Str::random(64)]);
        \Mail::to($subscritor->email)->send(new NewsletterSubscricao($subscritor->fresh()));

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => 'Confirmacao enviada para: ' . $subscritor->email,
        ]);
    }

    /** Reenviar confirmação a TODOS os pendentes de uma vez. */
    public function reenviarPendentes(): RedirectResponse
    {
        $pendentes = NewsletterSubscriber::pending()->get();
        $enviados = 0;
        foreach ($pendentes as $p) {
            try {
                $p->update(['token' => Str::random(64)]);
                \Mail::to($p->email)->send(new NewsletterSubscricao($p->fresh()));
                $enviados++;
            } catch (\Throwable) {
                // Ignora falha individual — continua com os restantes.
            }
        }

        return back()->with('newsletter_msg', [
            'tipo' => $enviados > 0 ? 'ok' : 'erro',
            'texto' => $enviados > 0
                ? "Confirmacao reenviada para {$enviados} subscritor(es)."
                : 'Nao foi possivel enviar nenhuma confirmacao.',
        ]);
    }
    /** Adiciona manualmente (admin) como «confirmed» — útil para campanhas. */
    public function adicionar(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'email', 'max:190', 'unique:newsletter_subscribers,email'],
        ], [
            'email.unique' => 'Este email já está na lista.',
        ]);

        NewsletterSubscriber::create([
            'email' => mb_strtolower(trim($dados['email'])),
            'token' => Str::random(64),
            'status' => 'active',
            'confirmed_at' => now(),
        ]);

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => 'Subscritor adicionado manualmente.',
        ]);
    }

    public function remover(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::findOrFail($id);
        $subscritor->delete();

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => 'Subscritor removido.',
        ]);
    }

    /**
     * Ecrã «Aviso de novo artigo» (Fase N2 / aprovado 2026-11-17):
     * o admin escolhe um artigo recente publicado e o sistema gera o email
     * a partir dele; o CLIQUE EMVIA NO MÁXIMO 100 SUBSCRITORES ACTIVOS —
     * o utilizador clica novamente para enviar os restantes.
     */
    public function enviar(): View
    {
        $artigos = News::published()
            ->latest('published_at')
            ->take(10)
            ->get(['id', 'title', 'slug', 'image', 'image_alt', 'excerpt', 'published_at']);

        $ativos = NewsletterSubscriber::active()->count();
        $edicoes = NewsletterEdition::with('article', 'creator')->latest()->take(5)->get();

        return view('admin.newsletter-enviar', compact('artigos', 'ativos', 'edicoes'));
    }

    /**
     * Aciona o envio de um LOTE (≤100) do artigo escolhido:
     *  - só subscritores `status = active`;
     *  - cada clique emvia para os que ainda NÃO receberam nesta edição;
     *  - falhas são registadas e voltam a tentar no clique seguinte.
     *
     * `teste=1`: manda apenas um email para a caixa de correio do utilizador.
     */
    public function enviarArtigo(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'artigo_id' => ['required', 'integer', 'exists:news,id'],
            'teste' => ['nullable', 'boolean'],
        ], [
            'artigo_id.required' => 'Escolha o artigo a enviar.',
        ]);

        $artigo = News::published()->findOrFail($dados['artigo_id']);

        if ($request->boolean('teste')) {
            $eu = auth()->user();
            $sub = new NewsletterSubscriber(['email' => $eu->email, 'token' => Str::random(64)]);
            \Mail::to($eu->email)->send(new NewsletterArtigo($artigo, $sub));

            return back()->with('newsletter_msg', [
                'tipo' => 'ok',
                'texto' => 'Email de teste enviado para ' . $eu->email,
            ]);
        }

        $ativos = NewsletterSubscriber::active()->count();

        if ($ativos === 0) {
            return back()->with('newsletter_msg', [
                'tipo' => 'erro',
                'texto' => 'Não há subscritores activos para enviar.',
            ]);
        }

        // Retoma a edição aberta do mesmo artigo (se houver) ou abre uma nova.
        $edicao = NewsletterEdition::where('article_id', $artigo->id)
            ->where('estado', '!=', 'done')
            ->latest('id')
            ->first();

        if (! $edicao) {
            $edicao = NewsletterEdition::create([
                'article_id' => $artigo->id,
                'total' => $ativos,
                'estado' => 'pending',
                'criado_por' => auth()->id(),
            ]);
        } elseif ($edicao->total < $ativos) {
            $edicao->update(['total' => $ativos]);
        }

        // Quem já foi marcado como enviado nesta edição é saltado.
        $jaEnviados = $edicao->items()->where('estado', 'sent')->pluck('email')->all();

        $lotes = NewsletterSubscriber::active()
            ->when($jaEnviados !== [], fn ($q) => $q->whereNotIn('email', $jaEnviados))
            ->orderBy('id')
            ->take(100)
            ->get();

        $ok = 0;
        $falha = 0;

        foreach ($lotes as $sub) {
            try {
                \Mail::to($sub->email)->send(new NewsletterArtigo($artigo, $sub));
                $edicao->items()->updateOrCreate(['email' => $sub->email], ['estado' => 'sent']);
                $ok++;
            } catch (\Throwable $e) {
                report($e);
                $edicao->items()->updateOrCreate(['email' => $sub->email], ['estado' => 'failed']);
                $falha++;
            }
        }

        $edicao->increment('enviados', $ok);
        $edicao->increment('falhados', $falha);

        if ($edicao->fresh()->estaCompleta()) {
            $edicao->update(['estado' => 'done', 'finished_at' => now()]);
        }

        $restante = $edicao->fresh()->pendentes();

        return back()->with('newsletter_msg', [
            'tipo' => $falha === 0 ? 'ok' : 'erro',
            'texto' => "Lote processado: {$ok} enviados, {$falha} falhados — faltam {$restante} subscritor(es).",
        ]);
    }

    /** Exporta em CSV o estado escolhido (por omissão: ativos). */
    public function exportar(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $estado = (string) $request->query('estado', 'active');
        if (! in_array($estado, ['active', 'pending', 'cancelled'], true)) {
            $estado = 'active';
        }

        $rotulos = ['active' => 'ativos', 'pending' => 'pendentes', 'cancelled' => 'cancelados'];
        $ficheiro = 'newsletter-' . $rotulos[$estado] . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $ficheiro . '"',
        ];

        return response()->streamDownload(function () use ($estado) {
            $saida = fopen('php://output', 'w');
            // BOM UTF-8 — o Excel do Windows precisa disto para ler acentos.
            fwrite($saida, "\xEF\xBB\xBF");
            fputcsv($saida, ['Email', 'Estado', 'Criado em', 'Confirmado em']);

            NewsletterSubscriber::where('status', $estado)
                ->orderBy('created_at')
                ->cursor()
                ->each(function (NewsletterSubscriber $s) use ($saida) {
                    fputcsv($saida, [
                        $s->email,
                        $s->status,
                        $s->created_at?->format('d/m/Y') ?? '',
                        $s->confirmed_at?->format('d/m/Y') ?? '',
                    ]);
                });

            fclose($saida);
        }, $ficheiro, $headers);
    }
}
