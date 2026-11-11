<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

/**
 * Redireciona após login consoante a role do utilizador.
 *
 * Usa redirect()->intended(): o URL pretendido é guardado pela própria
 * aplicação (sessão, mesma origem), logo não é um vetor de open redirect.
 * A proteção contra acesso indevido fica na camada de autorização (middleware
 * `role:`) — não aqui.
 *
 * Reestruturação (restrutura.md): só a equipa interna (admin/editor) tem
 * login — empresas e profissionais submetem formulários públicos e não são
 * utilizadores do sistema. O binding de RegisterResponse foi removido junto
 * com o registo público (Fortify sem `Features::registration()`).
 */
class RoleBasedHomeResponse implements LoginResponseContract
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
            return '/';
        }

        if ($user->hasAnyRole(['admin', 'editor'])) {
            // Caminho configurável (ADMIN_PATH) — ver `config/app.php`.
            return '/'.config('app.admin_path');
        }

        return '/';
    }
}
