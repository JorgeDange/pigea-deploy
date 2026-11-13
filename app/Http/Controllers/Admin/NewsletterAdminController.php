<?php

namespace App\Http\Controllers\Admin;

use App\Mail\NewsletterConfirmation;
use App\Http\Controllers\Controller;
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
 *   POST /newsletter/adicionar                — adicionar manualmente (confirmed)
 *   POST /newsletter/reenviar/{id}            — reenviar confirmação a UM pendente
 *   POST /newsletter/reenviar-pendentes       — reenviar confirmação a TODOS os pendentes
 *   DELETE /newsletter/{id}                   — remover linha
 *   GET /newsletter/exportar.csv              — exportar por estado (?estado=confirmed|pending|unsubscribed, BOM do Excel)
 *
 * A subscrição pública e a dupla ótica ficam em Frontend\NewsletterController.
 */
class NewsletterAdminController extends Controller
{
    public function index(Request $request): View
    {
        $estado = (string) $request->query('estado', '');
        if (! in_array($estado, ['', 'pending', 'confirmed', 'unsubscribed'], true)) {
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
        $confirmados = NewsletterSubscriber::confirmed()->count();
        $pendentes = NewsletterSubscriber::pending()->count();
        $cancelados = NewsletterSubscriber::cancelados()->count();

        return view('admin.newsletter', [
            'subscritores' => $subscritores,
            'estado' => $estado,
            'busca' => $busca,
            'total' => $total,
            'confirmados' => $confirmados,
            'pendentes' => $pendentes,
            'cancelados' => $cancelados,
            'taxaConfirmacao' => $total > 0 ? (int) round($confirmados / $total * 100) : 0,
            'rotulos' => ['confirmed' => 'Confirmados', 'pending' => 'Pendentes', 'unsubscribed' => 'Cancelados'],
        ]);
    }

    /** Confirmar UM subscritor pendente. */
    public function confirmar(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::pending()->findOrFail($id);
        $subscritor->update(['status' => 'confirmed', 'confirmed_at' => now()]);

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => '«' . $subscritor->email . '» confirmado.',
        ]);
    }

    /** Cancelar um subscritor (ativo ou pendente) — sai da lista de envio. */
    public function cancelar(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::whereIn('status', ['confirmed', 'pending'])->findOrFail($id);
        $subscritor->update(['status' => 'unsubscribed']);

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
        \Mail::to($subscritor->email)->send(new NewsletterConfirmation($subscritor->fresh()));

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
            $p->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        }

        return back()->with('newsletter_msg', [
            'tipo' => 'ok',
            'texto' => $pendentes->count() . ' subscritor(es) confirmados.',
        ]);
    }

    /** Reenviar confirmação (um subscritor pendente). */
    public function reenviarConfirmacao(int $id): RedirectResponse
    {
        $subscritor = NewsletterSubscriber::pending()->findOrFail($id);
        $subscritor->update(['token' => Str::random(64)]);
        \Mail::to($subscritor->email)->send(new NewsletterConfirmation($subscritor->fresh()));

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
                \Mail::to($p->email)->send(new NewsletterConfirmation($p->fresh()));
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
            'status' => 'confirmed',
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

    /** Exporta em CSV o estado escolhido (por omissão: confirmados). */
    public function exportar(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $estado = (string) $request->query('estado', 'confirmed');
        if (! in_array($estado, ['confirmed', 'pending', 'unsubscribed'], true)) {
            $estado = 'confirmed';
        }

        $rotulos = ['confirmed' => 'confirmados', 'pending' => 'pendentes', 'unsubscribed' => 'cancelados'];
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
