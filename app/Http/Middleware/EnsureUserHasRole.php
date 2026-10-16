<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorização por role (Spatie) — usado como middleware de alias:
 *   'role:admin' / 'role:company' / 'role:editor'
 *
 * Recusa com 403 (não 401/redirect): quem já está autenticado mas sem a
 * role não deve ser levado para o login — isso mascara erros de autorização.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        // hasAnyRole compara com roles da BD; nunca confiar em input do cliente.
        abort_unless($user->hasAnyRole($roles), 403);

        return $next($request);
    }
}
