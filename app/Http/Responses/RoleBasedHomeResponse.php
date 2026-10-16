<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

/**
 * Redireciona após login/registo consoante a role do utilizador.
 *
 * Usa redirect()->intended(): o URL pretendido é guardado pela própria
 * aplicação (sessão, mesma origem), logo não é um vetor de open redirect.
 * A proteção contra acesso indevido fica na camada de autorização (middleware
 * `role:`) — não aqui.
 */
class RoleBasedHomeResponse implements LoginResponseContract, RegisterResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        return redirect()->intended($this->homeFor($request->user()));
    }

    protected function homeFor(?Authenticatable $user): string
    {
        if ($user === null || ! method_exists($user, 'hasAnyRole')) {
            return '/painel';
        }

        if ($user->hasAnyRole(['admin', 'editor'])) {
            // Caminho configurável (ADMIN_PATH) — ver `config/app.php`.
            return '/'.config('app.admin_path');
        }

        if ($user->hasRole('company')) {
            return '/painel-empresa';
        }

        return '/painel';
    }
}
