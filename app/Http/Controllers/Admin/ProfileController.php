<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Autor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
