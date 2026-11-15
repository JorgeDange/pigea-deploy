<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterCancelamento;
use App\Mail\NewsletterConfirmacao;
use App\Mail\NewsletterSubscricao;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Newsletter do PIGEA — subscrição pública com dupla opt-in.
 *
 * POST /newsletter            cria/atualiza a inscrição (estado `pending`)
 *                             e envia o email de confirmação
 * GET  /newsletter/confirmar/{token}   confirma a subscrição
 * GET  /newsletter/cancelar/{token}    cancela a subscrição
 * GET  /newsletter/resubscrever/{token}  reativa a subscrição cancelada
 *
 * Sem JavaScript e sem dados além de email — ver doc/newsletter.md.
 */
class NewsletterController extends Controller
{
    public function subscribe(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ], [
            'email.required' => 'Escreva o seu email.',
            'email.email' => 'Escreva um endereço de email válido.',
            'email.max' => 'O email pode ter no máximo 190 carateres.',
        ]);

        $email = mb_strtolower(trim($dados['email']));

        $existente = NewsletterSubscriber::where('email', $email)->first();

        if ($existente && $existente->status === 'active') {
            return $this->resposta($request, true, 'Este email já está subscrito. Obrigado!');
        }

        if ($existente && $existente->status === 'pending') {
            // Reenvio: roda o token (invalida o link antigo) e prepara novo email.
            $existente->update([
                'token' => Str::random(64),
            ]);
            \Mail::to($email)->send(new NewsletterSubscricao($existente->fresh()));

            return $this->resposta($request, true, 'Enviámos-lhe um novo email de confirmação. Verifique a sua caixa de email.');
        }

        $subscritor = NewsletterSubscriber::updateOrCreate(
            ['email' => $email],
            [
                'token' => Str::random(64),
                'status' => 'pending',
                'confirmed_at' => null,
            ]
        );

        \Mail::to($email)->send(new NewsletterSubscricao($subscritor));

        return $this->resposta($request, true, 'Verifique o seu email — enviámos-lhe o link de confirmação.');
    }

    /**
     * Resposta única da subscrição: se o pedido veio por AJAX/JSON (modal),
     * devolve JSON; senão, volta como flash de sessão para o formulário normal.
     */
    private function resposta(Request $request, bool $ok, string $texto): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => $ok, 'mensagem' => $texto], $ok ? 200 : 200);
        }

        return back()->with('newsletter_msg', ['tipo' => $ok ? 'ok' : 'erro', 'texto' => $texto]);
    }

    public function confirmar(string $token): View
    {
        $subscritor = NewsletterSubscriber::where('token', $token)->first();

        if ($subscritor === null) {
            return view('newsletter.token-invalido');
        }

        if ($subscritor->status === 'active') {
            return view('newsletter.confirmado', ['jaConfirmado' => true]);
        }

        $subscritor->update([
            'status' => 'active',
            'confirmed_at' => now(),
        ]);

        \Mail::to($subscritor->email)->send(new NewsletterConfirmacao($subscritor->fresh()));

        return view('newsletter.confirmado', ['jaConfirmado' => false]);
    }

    public function cancelar(string $token): View
    {
        $subscritor = NewsletterSubscriber::where('token', $token)->first();

        if ($subscritor === null) {
            return view('newsletter.token-invalido');
        }

        $subscritor->update(['status' => 'cancelled']);

        \Mail::to($subscritor->email)->send(new NewsletterCancelamento($subscritor->fresh()));

        return view('newsletter.cancelado');
    }

    public function resubscrever(string $token): View
    {
        $subscritor = NewsletterSubscriber::where('token', $token)->first();

        if ($subscritor === null) {
            return view('newsletter.token-invalido');
        }

        if ($subscritor->status === 'active') {
            return view('newsletter.confirmado', ['jaConfirmado' => true]);
        }

        if ($subscritor->status === 'pending') {
            return view('newsletter.confirmado', ['jaConfirmado' => true]);
        }

        // Cancelado — reativa o opt-in com novo token
        $subscritor->update([
            'status' => 'pending',
            'token' => Str::random(64),
            'confirmed_at' => null,
        ]);

        \Mail::to($subscritor->email)->send(new NewsletterSubscricao($subscritor->fresh()));

        return view('newsletter.confirmado', ['jaConfirmado' => false]);
    }
}
