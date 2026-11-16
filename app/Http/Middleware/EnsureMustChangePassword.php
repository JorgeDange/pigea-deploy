<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Troca obrigatória de senha a seguir a um reset pelo admin (Fase B2).
 *
 * Utilizadores com `users.must_change_password = true` são reencaminhados
 * para a página do perfil até trocarem a senha. As rotas do próprio perfil
 * (GET/POST), a troca de senha e o logout ficam livres; todo o resto do
 * painel fica trancado com um aviso explicando o que fazer.
 *
 * A troca acontece no formulário «A minha senha» (Fase A) — ao gravar,
 * o próprio controlador limpa a flag.
 */
final class EnsureMustChangePassword
{
    /** Rotas livres para quem tem a flag ligada. */
    private const LIVRES = [
        'admin.perfil',
        'admin.perfil.update',
        'admin.perfil.senha',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $utilizador = $request->user();

        if ($utilizador !== null && (bool) $utilizador->must_change_password) {
            $rota = $request->route()?->getName() ?? '';

            if (! in_array($rota, self::LIVRES, true) && ! str_starts_with($rota, 'admin.perfil')) {
                return redirect()->route('admin.perfil')
                    ->with('estado', 'A sua senha foi redefinida pela equipa. Defina uma nova senha para continuar.');
            }
        }

        return $next($request);
    }
}
