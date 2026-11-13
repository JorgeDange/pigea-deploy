<?php

namespace App\Http\Controllers\Admin;

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
 *   DELETE /newsletter/{id}                   — remover linha
 *   GET /newsletter/exportar.csv              — exportar confirmados (BOM do Excel)
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

        return view('admin.newsletter', [
            'subscritores' => $consulta->paginate(15)->withQueryString(),
            'estado' => $estado,
            'busca' => $busca,
            'confirmados' => NewsletterSubscriber::confirmed()->count(),
            'pendentes' => NewsletterSubscriber::pending()->count(),
            'cancelados' => NewsletterSubscriber::cancelados()->count(),
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

    /** Exporta os confirmados em CSV (BOM na primeira linha para o Excel). */
    public function exportar(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $ficheiro = 'newsletter-subscritores.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $ficheiro . '"',
        ];

        return response()->streamDownload(function () {
            $saida = fopen('php://output', 'w');
            // BOM UTF-8 — o Excel do Windows precisa disto para ler acentos.
            fwrite($saida, "\xEF\xBB\xBF");
            fputcsv($saida, ['Email', 'Confirmado em', 'Criado em']);

            NewsletterSubscriber::confirmed()
                ->orderBy('created_at')
                ->cursor()
                ->each(function (NewsletterSubscriber $s) use ($saida) {
                    fputcsv($saida, [
                        $s->email,
                        $s->confirmed_at?->format('d/m/Y') ?? '',
                        $s->created_at?->format('d/m/Y') ?? '',
                    ]);
                });

            fclose($saida);
        }, $ficheiro, $headers);
    }
}
