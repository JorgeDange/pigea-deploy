<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Editor de notícias (CMS Pro §4.2) — versão Blade tradicional.
 *
 * Substitui a versão Livewire para evitar o problema de morph incremental
 * do Livewire destruir o DOM do TinyMCE (o textarea é substituído pelo
 * TinyMCE; o Livewire, sem `wire:ignore` assistido, remonta o componente
 * reescrevendo o textarea e apagando o editor).
 *
 * O TinyMCE mantém-se self-hosted. O autosave/preview são opcionais na
 * arquitetura (mantidos com JS vanilla pela robustez).
 */
class NewsEditorController extends Controller
{
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('admin.noticia-editor', [
            'noticia' => null,
            'categorias' => Category::where('type', 'news')->orderBy('name')->get(),
        ]);
    }

    public function edit(News $noticia): \Illuminate\Contracts\View\View
    {
        return view('admin.noticia-editor', [
            'noticia' => $noticia,
            'categorias' => Category::where('type', 'news')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $publicar = $request->input('acao') === 'publicar';
        $dados = $this->validar($request, null, $publicar);

        $noticia = $this->criarNoticia($request, $dados, $publicar);

        return $this->resposta($noticia, $publicar);
    }

    public function update(Request $request, News $noticia): RedirectResponse
    {
        $publicar = $request->input('acao') === 'publicar';
        $dados = $this->validar($request, $noticia, $publicar);

        $this->atualizarNoticia($request, $noticia, $dados, $publicar);

        return $this->resposta($noticia, $publicar);
    }

    /**
     * Para onde ir depois de guardar.
     *
     * Rascunho mantém o redator no editor (fluxo «guardar e continuar a escrever»
     * — o editor abre já com o registo acabado de criar/atualizar). Publicar
     * fecha o ciclo e leva à lista, com a confirmação em flash na própria
     * lista (que a renderiza).
     */
    private function resposta(News $noticia, bool $publicar): RedirectResponse
    {
        if ($publicar) {
            return redirect()
                ->route('admin.noticias')
                ->with('estado', 'Notícia publicada!');
        }

        return redirect()
            ->route('admin.noticia-editar', ['noticia' => $noticia->getKey()])
            ->with('estado', 'Rascunho guardado.');
    }

    /** @return array<string, mixed> */
    private function validar(Request $request, ?News $noticia, bool $publicar = false): array
    {
        $regras = [
            'titulo' => ['required', 'string', 'max:190'],
            'subtitulo' => ['nullable', 'string', 'max:500'],
            'conteudo' => ['nullable', 'string'],
            'categoria' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'news')],
            'videoLink' => ['nullable', 'url:http,https', 'max:300'],
            'documentoLink' => ['nullable', 'url:http,https', 'max:300'],
            'capa' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'contexto' => ['nullable', 'array', 'max:10'],
            'contexto.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ];

        if ($publicar) {
            $regras['conteudo'] = ['required', 'string'];
            $regras['categoria'] = ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'news')];
        }

        return $request->validate($regras, [
            'capa.max' => 'A imagem de capa tem no máximo 2MB.',
            'capa.mimes' => 'A capa tem de ser PNG, JPG ou WebP.',
            'videoLink.url' => 'O link do vídeo tem de ser um URL válido (https://…).',
            'documentoLink.url' => 'O link do documento tem de ser um URL válido (https://…).',
            'conteudo.required' => 'Escreve o corpo da notícia antes de publicar.',
            'categoria.required' => 'Escolhe a categoria antes de publicar.',
        ]);
    }

    private function criarNoticia(Request $request, array $dados, bool $publicar): News
    {
        $noticia = new News();
        $noticia->fill($this->atributos($dados, $publicar, $request));
        $noticia->save();

        $this->processarMidia($request, $noticia);

        return $noticia;
    }

    private function atualizarNoticia(Request $request, News $noticia, array $dados, bool $publicar): void
    {
        $noticia->fill($this->atributos($dados, $publicar, $request, $noticia));
        $noticia->save();

        $this->processarMidia($request, $noticia);
    }

    /**
     * Copia os campos do formulário para o modelo.
     *
     * Submissão parcial: em edição, um campo ausente do pedido mantém o valor
     * actual (mesma regra do formulário genérico — um POST incompleto não pode
     * apagar excerto/conteúdo/categoria). Campo presente mas vazio limpa-se.
     *
     * @return array<string, mixed>
     */
    private function atributos(array $dados, bool $publicar, Request $request, ?News $atual = null): array
    {
        $tem = fn (string $campo) => $request->has($campo);

        return [
            'title' => trim($dados['titulo']),
            'slug' => $atual?->slug ?? $this->slugUnico($dados['titulo']),
            'excerpt' => $tem('subtitulo')
                ? (trim((string) ($dados['subtitulo'] ?? '')) ?: null)
                : $atual?->excerpt,
            'content' => $tem('conteudo')
                ? ($dados['conteudo'] ?? null)
                : $atual?->content,
            'category_id' => $tem('categoria')
                ? ($dados['categoria'] ?? null)
                : $atual?->category_id,
            'video_link' => $tem('videoLink')
                ? ($dados['videoLink'] ?? null)
                : $atual?->video_link,
            'documento_link' => $tem('documentoLink')
                ? ($dados['documentoLink'] ?? null)
                : $atual?->documento_link,
            'status' => $publicar ? 'published' : ($atual?->status ?? 'draft'),
            'published_at' => $publicar ? ($atual?->published_at ?? now()) : $atual?->published_at,
            'date_label' => $publicar
                ? ($atual?->date_label ?? now()->locale('pt')->translatedFormat('d M Y'))
                : $atual?->date_label,
            'author_id' => $request->user()?->id,
        ];
    }

    private function processarMidia(Request $request, News $noticia): void
    {
        if ($request->hasFile('capa')) {
            $media = $noticia->addMedia($request->file('capa'))->toMediaCollection('capa');
            $noticia->image = 'storage/' . $media->file_name;
            $noticia->save();
        }

        foreach ($request->file('contexto', []) as $ficheiro) {
            $noticia->addMedia($ficheiro)->toMediaCollection('contexto');
        }
    }

    /** Slug único global (rascunhos e publicações convivem). */
    private function slugUnico(string $titulo): string
    {
        $base = Str::slug($titulo);
        $candidato = $base;
        $n = 1;

        while (News::query()->withTrashed()->where('slug', $candidato)->exists()) {
            $candidato = $base . '-' . $n++;
        }

        return $candidato;
    }
}
