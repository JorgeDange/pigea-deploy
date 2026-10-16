<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * `admin/noticia-nova.html` — formulário de criação de notícia.
 *
 * No protótipo o `<form>` não tem `action` («Guardar» não gravava nada).
 * Aqui valida (fase.md §2.2) e cria o registo em `news`. O botão *Publicar* do
 * protótipo é `type="button"` (fica inerte); o estado vem do select
 * (rascunho → `draft`, publicar → `published`). Categoria vem da BD
 * (mesmo precedente das tabelas do painel), as restantes opções do protótipo
 * mantêm-se. *Pré-visualizar* não existe no protótipo como ação → inerte.
 */
class NewsFormController extends Controller
{
    public function create(): View
    {
        return view('admin.noticia-nova', [
            'categorias' => Category::query()->where('type', 'news')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'titulo' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('news', 'slug')],
            'categoria' => ['nullable', Rule::exists('categories', 'id')->where('type', 'news')],
            'provincia' => ['nullable', Rule::in(['luanda', 'benguela', 'huila', 'namibe', 'nacional'])],
            'estado' => ['nullable', Rule::in(['rascunho', 'publicar'])],
            'agendamento' => ['nullable', 'date'],
            'excerto' => ['nullable', 'string', 'max:500'],
            'conteudo' => ['nullable', 'string'],
            'imagem' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:4096'],
            'tags' => ['nullable', 'string', 'max:300'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ]);

        $publicada = ($dados['estado'] ?? '') === 'publicar';
        $publicadoEm = $request->date('agendamento') ?? ($publicada ? now() : null);

        $noticia = new News([
            'category_id' => $dados['categoria'] ?? null,
            'author_id' => $request->user()->id,
            'title' => $dados['titulo'],
            'slug' => $this->slugUnico($dados['slug'] ?? '', $dados['titulo']),
            'excerpt' => $dados['excerto'] ?? null,
            'content' => $dados['conteudo'] ?? null,
            'status' => $publicada ? 'published' : 'draft',
            'published_at' => $publicadoEm,
            'date_label' => $publicadoEm?->locale('pt')->translatedFormat('d M Y'),
            'province' => $dados['provincia'] ?? null,
            'tags' => $dados['tags'] ?? null,
            'meta_title' => $dados['meta_title'] ?? null,
            'meta_description' => $dados['meta_description'] ?? null,
        ]);

        if ($request->hasFile('imagem')) {
            $noticia->image = 'storage/'.$request->file('imagem')->store('news', 'public');
        }

        $noticia->save();

        return redirect()->route('admin.noticias');
    }

    /** Slug indicado ou derivado do título, sufixado até ficar único. */
    private function slugUnico(string $slug, string $titulo): string
    {
        $base = Str::slug($slug !== '' ? $slug : $titulo);
        $candidato = $base;
        $n = 1;

        while (News::query()->withTrashed()->where('slug', $candidato)->exists()) {
            $candidato = $base.'-'.$n++;
        }

        return $candidato;
    }
}
