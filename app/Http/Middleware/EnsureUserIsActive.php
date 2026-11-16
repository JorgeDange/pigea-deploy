<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Defesa real contra contas desativadas (Fase B / prop.md §2.3).
 *
 * O antigo «Bloquear» só trocava um selo em `users.status` — a conta
 * continuava a fazer login e as sessões abertas continuavam válidas.
 * Este middleware é aplicado ao grupo `web` (corre depois do auth):
 * qualquer utilizador autenticado com `status` diferente de `active` é
 * **deslogado na hora** e o pedido termina em redirect para o login com
 * a mensagem «A sua conta foi desativada. Contacte a equipa.».
 *
 * O login em si nunca chega aqui autenticado (utilizador anónimo passa
 * adiante), e um utilizador desativado que tenta autenticar-se acaba por
 * cair neste middleware no primeiro pedido autenticado seguinte.
 */
final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $utilizador = $request->user();

        if ($utilizador !== null && ($utilizador->status ?? 'active') !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $caminho = trim((string) env('PIGEA_LOGIN_PATH', 'login'), '/');

            return redirect()->to('/'.$caminho)
                ->with('estado', 'A sua conta foi desativada. Contacte a equipa.');
        }

        return $next($request);
    }
}
