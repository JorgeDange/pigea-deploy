<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterConfirmation;
use App\Models\NewsletterSubscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Newsletter do PIGEA — subscrição pública com dupla opt-in.
 *
 *  POST /newsletter            cria/atualiza a inscrição (estado `pending`)
 *                             e envia o email de confirmação
 *  GET  /newsletter/confirmar/{token} marca o opt-in (confirmed)
 *  GET  /newsletter/cancelar/{token}  marca unsubscribed
 *
 * Sem JavaScript e sem dados além de nome e email — ver doc/newsletter.md.
 * Nada aqui envia edições da newsletter (isso é a FASE N2).
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

        if ($existente && $existente->status === 'confirmed') {
            return $this->resposta($request, true, 'Este email já está subscrito. Obrigado!');
        }

        if ($existente && $existente->status === 'pending') {
            // Reenvio: roda o token (invalida o link antigo) e prepara novo email.
            $existente->update([
                'token' => Str::random(64),
            ]);
            \Mail::to($email)->send(new NewsletterConfirmation($existente->fresh()));

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

        \Mail::to($email)->send(new NewsletterConfirmation($subscritor));

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

        if ($subscritor->status === 'confirmed') {
            return view('newsletter.confirmado', ['jaConfirmado' => true]);
        }

        $subscritor->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return view('newsletter.confirmado', ['jaConfirmado' => false]);
    }

    public function cancelar(string $token): View
    {
        $subscritor = NewsletterSubscriber::where('token', $token)->first();

        if ($subscritor === null) {
            return view('newsletter.token-invalido');
        }

        $subscritor->update(['status' => 'unsubscribed']);

        return view('newsletter.cancelado');
    }
}
