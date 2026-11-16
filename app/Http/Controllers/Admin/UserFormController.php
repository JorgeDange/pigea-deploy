<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    /** Papéis atribuíveis pela equipa — apenas admin/editor (reestruturação
     *  restrutura.md: a equipa interna tem login; empresas/profissionais não).
     *  Os papéis legados `user`/`company` continuam na BD de testes por
     *  compatibilidade, mas não são atribuíveis pela UI. */
    private const PAPEIS = ['editor', 'admin'];

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

    /**
     * Formulário de gestão de um utilizador (Fase B / prop.md §3):
     * dados da conta + telefone/província + reposição de senha.
     * Apenas admin (can:user.manage na rota).
     */
    public function edit(User $user): View
    {
        return view('admin.utilizador-editar', [
            'utilizador' => $user->load('roles'),
            'perfisPermitidos' => self::PAPEIS,
        ]);
    }

    /**
     * Atualiza nome/email/papel/telefone/província.
     *
     * Regras de segurança (prop.md Fase B):
     *  - o admin não pode mudar o PRÓPRIO papel (evita auto-rebaixar);
     *  - o último admin existente não pode ser rebaixado de papel;
     *  - mudanças de papel ficam para o painel de auditoria (Fase C).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique(User::class)->ignore($user)],
            'perfil' => ['required', Rule::in(self::PAPEIS)],
            'telefone' => ['nullable', 'string', 'max:40'],
            'provincia' => ['nullable', 'string', 'max:64'],
        ], [
            'email.unique' => 'Já existe um utilizador com esse email.',
        ]);

        $souEu = $request->user()->is($user);
        $papelAtual = $user->getRoleNames()->first();
        $papelNovo = $dados['perfil'];

        if ($souEu && $papelNovo !== $papelAtual) {
            return back()->withErrors(['perfil' => 'Não pode alterar o seu próprio perfil.'])->withInput();
        }

        if ($papelAtual === 'admin' && $papelNovo !== 'admin' && $this->contarAdmins() <= 1) {
            return back()->withErrors(['perfil' => 'Não pode rebaixar o último administrador existente.'])->withInput();
        }

        $user->forceFill([
            'name' => trim($dados['nome']),
            'email' => strtolower(trim($dados['email'])),
            'phone' => trim((string) ($dados['telefone'] ?? '')),
            'province' => trim((string) ($dados['provincia'] ?? '')),
        ])->save();

        if ($papelNovo !== $papelAtual) {
            $user->syncRoles([$papelNovo]);
        }

        return redirect()->route('admin.utilizador.editar', $user)
            ->with('estado', 'Dados de '.$user->name.' atualizados.');
    }

    /**
     * Reposição de senha pelo admin (prop.md Fase B2).
     *
     * Define a senha indicada (sem precisar da atual), termina TODAS as
     * sessões da conta na hora e — por decisão do cliente — marca
     * `must_change_password` (troca obrigatória no próximo acesso).
     */
    public function senha(Request $request, User $user): RedirectResponse
    {
        $dados = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::default()],
        ], [
            'password.confirmed' => 'A confirmação não coincide com a nova senha.',
        ]);

        $user->forceFill([
            'password' => Hash::make($dados['password']),
            'must_change_password' => true, // troca obrigatória — decisão do cliente
        ])->save();

        // Encerra imediatamente todas as sessões da conta alterada.
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return redirect()->route('admin.utilizador.editar', $user)
            ->with('estado', 'Senha reposta. O utilizador terá de trocar a senha no próximo acesso e as sessões abertas foram terminadas.');
    }

    /** Número de administradores que existem hoje (proteção ao último admin). */
    private function contarAdmins(): int
    {
        return User::role('admin')->count();
    }
}
