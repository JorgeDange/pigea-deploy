<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Autor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Página "O Meu Perfil" (autor.md §2.1).
 *
 * Acessível a todos os utilizadores internos (admin e editor) — sem
 * `can:` granular, apenas o middleware do grupo (auth + role:admin,editor).
 *
 * Atualiza nome e foto do próprio utilizador. A foto pode ser carregada
 * (convertida para miniatura 150x150 em `storage/app/public/avatars`) ou
 * apontada por link quando a imagem já está hospedada na internet.
 */
class ProfileController extends Controller
{
    public function index(): View
    {
        return view('admin.perfil', ['utilizador' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $utilizador = auth()->user();

        $dados = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'avatar_link' => ['nullable', 'url:http,https', 'max:300'],
        ], [
            'name.required' => 'O nome não pode ficar vazio.',
            'avatar.image' => 'A foto tem de ser uma imagem.',
            'avatar.max' => 'A foto tem no máximo 1MB.',
            'avatar_link.url' => 'O link tem de ser um URL válido (https://…).',
        ]);

        $link = trim((string) $request->input('avatar_link', ''));

        if ($link !== '' && $request->hasFile('avatar')) {
            throw ValidationException::withMessages([
                'avatar' => 'Envia o ficheiro OU o link da foto, não ambos.',
            ]);
        }

        $utilizador->name = trim((string) $dados['name']);

        $antigo = $utilizador->avatar;

        if ($request->hasFile('avatar')) {
            $utilizador->avatar = Autor::guardarMiniatura($request->file('avatar'), 'avatar');
            Autor::apagarLocal($antigo);
        } elseif ($link !== '') {
            $utilizador->avatar = $link;
            Autor::apagarLocal($antigo);
        }

        // `avatar` não é mass-assignable por design (ver User::$fillable);
        // atribuição direta intencional.
        $utilizador->save();

        return redirect()->route('admin.perfil')->with('estado', 'Perfil atualizado.');
    }

    /**
     * Troca a senha do próprio utilizador (auto-serviço — admin e editor).
     *
     * Valida a senha atual, aplica a régua `Password::default()` e termina
     * TODAS as outras sessões da conta (tabela `sessions`) na gravação —
     * a sessão corrente mantém-se, mas um leak noutro dispositivo morre aqui.
     */
    public function senha(Request $request): RedirectResponse
    {
        $utilizador = auth()->user();

        $dados = $request->validate([
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'string', 'confirmed', Password::default()],
        ], [
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.confirmed' => 'A confirmação não coincide com a nova senha.',
        ]);

        // `password` não é mass-assignable por design (User::$fillable) —
        // atribuição direta intencional, como no UserFormController.
        $utilizador->forceFill(['password' => Hash::make($dados['password'])])->save();

        // Encerra todas as OUTRAS sessões desta conta (mantém a atual).
        DB::table('sessions')
            ->where('user_id', $utilizador->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return redirect()->route('admin.perfil')
            ->with('estado', 'Senha alterada com sucesso. As suas outras sessões foram terminadas.');
    }
}
