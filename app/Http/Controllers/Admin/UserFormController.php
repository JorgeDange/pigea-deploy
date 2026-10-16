<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Modal "Adicionar utilizador" (`admin/utilizadores.html`).
 *
 * No protótipo o `<form>` não tinha `action`, não tinha botão de submissão e
 * o "Adicionar" ficava no rodapé, fora do form — o modal era só visual (a
 * própria vista tinha essa nota em comentário).
 *
 * Aqui cria mesmo a conta, com as mesmas rules do registo público
 * (`Fortify\CreateNewUser`) e com o papel escolhido no select. O `status` cai
 * no default `active` da migração — a conta nasce pronta a usar.
 *
 * O modal do protótipo não tem campo de password, e sem ele a conta ficava
 * com password inválida e o utilizador não conseguia entrar. Acrescentam-se
 * por isso os dois campos: o admin define-a e comunica-a por fora — o convite
 * por email depende do SMTP, ainda em `MAIL_MAILER=log`.
 */
class UserFormController extends Controller
{
    /** Papéis atribuíveis — os mesmos que o filtro do `UsersTable` mostra. */
    private const PAPEIS = ['user', 'company', 'editor', 'admin'];

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique(User::class)],
            'perfil' => ['required', Rule::in(self::PAPEIS)],
            // Mesmas rules do registo público, para não haver dois padrões de
            // força de password na mesma aplicação.
            'password' => ['required', 'string', Password::default()],
            'password_confirmation' => ['nullable', 'same:password'],
        ]);

        $utilizador = User::create([
            'name' => $dados['nome'],
            'email' => $dados['email'],
            'password' => Hash::make($dados['password']),
        ]);

        // Papel concreto: quem cria contas tem de escolher uma role conhecida,
        // senão o utilizador nascia sem acesso a nenhum painel.
        $utilizador->assignRole($dados['perfil']);

        return redirect()->route('admin.utilizadores');
    }
}
