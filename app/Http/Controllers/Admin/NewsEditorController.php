<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use App\Services\ArticleSanitizer;
use App\Support\CategoriasNews;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $mapa = CategoriasNews::mapa();

        return view('admin.noticia-editor', array_merge([
            'noticia' => null,
            'categoriasSelecionada' => null,
            'utilizadores' => $this->utilizadoresInternos(),
        ], $mapa));
    }

    public function edit(News $noticia): \Illuminate\Contracts\View\View
    {
        $mapa = CategoriasNews::mapa();

        return view('admin.noticia-editor', array_merge([
            'noticia' => $noticia,
            'categoriasSelecionada' => $noticia?->category_id,
            'utilizadores' => $this->utilizadoresInternos(),
        ], $mapa));
    }

    /** Utilizadores internos (admin + editor) para o select "Autor do Artigo". */
    private function utilizadoresInternos(): \Illuminate\Support\Collection
    {
        return \App\Models\User::role(['admin', 'editor'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** Valores possíveis de acao nos botões de submit. */
    private const ACAO_VALIDAS = ['rascunho', 'publicar', 'submeter_revisao', 'devolver_rascunho', 'despublicar'];

    public function store(Request $request): RedirectResponse
    {
        $acao = $this->normalizarAcao($request);
        $publicar = $acao === 'publicar';
        $dados = $this->validar($request, null, $publicar, $acao);

        $permissao = $this->verificarPermissaoPosse($acao, null, $request);
        if ($permissao !== null) {
            return $permissao;
        }

        $noticia = $this->criarNoticia($request, $dados, $publicar, $acao);

        return $this->resposta($noticia, $acao);
    }

    public function update(Request $request, News $noticia): RedirectResponse
    {
        $acao = $this->normalizarAcao($request);
        $publicar = $acao === 'publicar';
        $dados = $this->validar($request, $noticia, $publicar, $acao);

        $permissao = $this->verificarPermissaoPosse($acao, $noticia, $request);
        if ($permissao !== null) {
            return $permissao;
        }

        $this->atualizarNoticia($request, $noticia, $dados, $publicar, $acao);

        return $this->resposta($noticia, $acao);
    }

    /** Normaliza o botão acao: rascunho ou a de AÇÃO_VALIDAS. */
    private function normalizarAcao(Request $request): string
    {
        $acao = $request->input('acao');

        return in_array($acao, self::ACAO_VALIDAS, true) ? $acao : 'rascunho';
    }

    /**
     * Controlo de permissão de fluxo editorial (Fase 6).
     *
     * Regras:
     *   publicar        → admin, ou editor com config('cms.editor_pode_publicar') = true;
     *   devolver/despub → só admin;
     *   submeter_revisao → qualquer news.manage;
     *   rascunho         → qualquer news.manage.
     */
    private function verificarPermissaoPosse(string $acao, ?News $noticia, Request $request): ?RedirectResponse
    {
        $user = $request->user();
        $ehAdmin = $user?->hasRole('admin') ?? false;
        $podePublicar = $ehAdmin || (bool) config('cms.editor_pode_publicar');

        $permitido = match ($acao) {
            'publicar' => $podePublicar,
            'devolver_rascunho', 'despublicar' => $ehAdmin,
            default => true,
        };

        if ($permitido) {
            return null;
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('erro', 'Não tens permissão para esta ação. Fala com o administrador.');
    }

    /**
     * Autosave do editor (cms_v.md Fase 8.1).
     *
     * Regras:
     *  - Sanitiza o corpo com o ArticleSanitizer antes de guardar.
     *  - Novos registos: cria 1 rascunho na 1ª chamada (id deixado de fora).
     *  - Nunca publica: status NÃO muda para 'published' via autosave.
     */
    public function autosave(Request $request): \Illuminate\Http\JsonResponse
    {
        $dados = $request->validate([
            'id' => ['nullable', 'integer', Rule::exists('news', 'id')],
            'titulo' => ['nullable', 'string', 'max:190'],
            'conteudo' => ['nullable', 'string', 'max:2097152'],
            'subtitulo' => ['nullable', 'string', 'max:500'],
            'categoria' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'news')],
        ]);

        $novo = empty($dados['id']);

        if ($novo) {
            $titulo = trim((string) ($dados['titulo'] ?? ''));
            if ($titulo === '') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Escreve um título para ativar o autosave.',
                ], 422);
            }

            $noticia = new News();
            $noticia->title = $titulo;
            $noticia->slug = $this->slugUnico($titulo);
            $noticia->status = 'draft';
            $noticia->published_at = null;
            $noticia->date_label = null;
            $noticia->author_id = $request->user()->id;
        } else {
            $noticia = News::findOrFail($dados['id']);
        }

        if (array_key_exists('titulo', $dados)) {
            $titulo = trim((string) $dados['titulo']);
            if ($titulo !== '') {
                $noticia->title = $titulo;
            }
        }

        if (array_key_exists('conteudo', $dados)) {
            $noticia->content = ArticleSanitizer::clean($dados['conteudo'] ?? null);
        }

        if (array_key_exists('subtitulo', $dados)) {
            $noticia->excerpt = trim((string) ($dados['subtitulo'] ?? '')) ?: null;
        }

        if (array_key_exists('categoria', $dados)) {
            $noticia->category_id = $dados['categoria'];
        }

        // Nunca publica NEM despublica — só o acao normal altera status.
        $noticia->save();

        return response()->json([
            'ok' => true,
            'id' => $noticia->id,
            'saved_at' => now()->toIso8601String(),
            'message' => 'Autosave guardado.',
        ]);
    }

    /**
     * Verificação de unicidade do slug (Fase 6).
     * GET /noticias/slug-unico?slug=meu-slug&ignorar=12 → {"unico": true|false}
     */
    public function slugUnicoCheck(Request $request): \Illuminate\Http\JsonResponse
    {
        $dados = $request->validate([
            'slug' => ['required', 'string', 'max:190'],
            'ignorar' => ['nullable', 'integer'],
        ]);

        $candidato = Str::slug((string) $dados['slug']);

        if ($candidato === '') {
            return response()->json(['unico' => false, 'motivo' => 'vazio']);
        }

        $existe = News::query()
            ->withTrashed()
            ->where('slug', $candidato)
            ->where('id', '!=', $dados['ignorar'] ?? 0)
            ->exists();

        return response()->json(['unico' => ! $existe]);
    }

    /**
     * Pré-visualização do corpo do artigo (cms_v.md Fase 1/2): renderiza o
     * MESMO parcial que o site público usa, com o conteúdo do formulário
     * sanitizado — não grava nada. Devolve HTML para injetar no modal.
     */
    public function previsualizar(Request $request): \Illuminate\Http\Response
    {
        $dados = $request->validate([
            'conteudo' => ['nullable', 'string', 'max:2097152'],
        ]);

        return response(
            view('news.partials.article-body', [
                'content' => ArticleSanitizer::clean($dados['conteudo'] ?? null),
            ])->render()
        )->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Para onde ir depois de guardar (Fase 6).
     *
     * Publicar → lista com flash. Tudo o resto → o próprio editor, para
     * continuar a trabalhar.
     */
    private function resposta(News $noticia, string $acao): RedirectResponse
    {
        return match ($acao) {
            'publicar' => redirect()
                ->route('admin.noticias')
                ->with('estado', 'Notícia publicada!'),
            'submeter_revisao' => redirect()
                ->route('admin.noticia-editar', ['noticia' => $noticia->getKey()])
                ->with('estado', 'Enviada para revisão.'),
            'devolver_rascunho' => redirect()
                ->route('admin.noticia-editar', ['noticia' => $noticia->getKey()])
                ->with('estado', 'Notícia devolvida a rascunho.'),
            'despublicar' => redirect()
                ->route('admin.noticia-editar', ['noticia' => $noticia->getKey()])
                ->with('estado', 'Notícia despublicada (deixou de aparecer no site).'),
            default => redirect()
                ->route('admin.noticia-editar', ['noticia' => $noticia->getKey()])
                ->with('estado', 'Rascunho guardado.'),
        };
    }

    /** @return array<string, mixed> */
    private function validar(Request $request, ?News $noticia, bool $publicar = false, string $acao = 'rascunho'): array
    {
        $regras = [
            'titulo' => ['required', 'string', 'max:190'],
            'subtitulo' => ['nullable', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('news', 'slug')->ignore($noticia?->id)],
            'conteudo' => ['nullable', 'string'],
            'categoria' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'news')],
            'tags' => ['nullable', 'string', 'max:250'],
            'image_alt' => ['nullable', 'string', 'max:250'],
            'image_caption' => ['nullable', 'string', 'max:250'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'publicar_em' => ['nullable', 'date'],
            'videoLink' => ['nullable', 'url:http,https', 'max:300'],
            'documentoLink' => ['nullable', 'url:http,https', 'max:300'],
            'capa_link' => ['nullable', 'url:http,https', 'max:300'],
            'link' => ['nullable', 'url:http,https', 'max:300'],
            'capa' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'contexto' => ['nullable', 'array', 'max:10'],
            'author_choice' => ['nullable', Rule::in(['interno', 'convidado'])],
            'author_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'guest_author_name' => ['nullable', 'string', 'max:100'],
            'guest_author_avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'guest_author_avatar_link' => ['nullable', 'url:http,https', 'max:300'],
            'contexto.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ];

        if ($publicar) {
            $regras['conteudo'] = ['required', 'string'];
            $regras['categoria'] = ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'news')];
        }

        // Regra extra: ficheiro OU link na capa — não os dois.
        $capaLink = trim((string) $request->input('capa_link', ''));

        if ($capaLink !== '' && $request->hasFile('capa')) {
            throw ValidationException::withMessages([
                'capa' => 'Envia o ficheiro OU o link da imagem de capa, não ambos.',
            ]);
        }

        // Autor do Artigo (autor.md §2.2): convidado exige o nome; na foto —
        // ficheiro OU link, nunca os dois.
        $escolha = trim((string) $request->input('author_choice', ''));

        if ($escolha === 'convidado' && trim((string) $request->input('guest_author_name', '')) === '') {
            throw ValidationException::withMessages([
                'guest_author_name' => 'Escreve o nome do autor convidado (ou escolhe "Autor interno").',
            ]);
        }

        $guestLink = trim((string) $request->input('guest_author_avatar_link', ''));

        if ($guestLink !== '' && $request->hasFile('guest_author_avatar')) {
            throw ValidationException::withMessages([
                'guest_author_avatar' => 'Envia o ficheiro OU o link da foto do autor, não ambos.',
            ]);
        }

        return $request->validate($regras, [
            'capa.max' => 'A imagem de capa tem no máximo 2MB.',
            'capa.mimes' => 'A capa tem de ser PNG, JPG ou WebP.',
            'slug.unique' => 'Este endereço já está a ser usado por outra notícia.',
            'videoLink.url' => 'O link do vídeo tem de ser um URL válido (https://…).',
            'documentoLink.url' => 'O link do documento tem de ser um URL válido (https://…).',
            'capa_link.url' => 'O link da capa tem de ser um URL válido (https://…).',
            'conteudo.required' => 'Escreve o corpo da notícia antes de publicar.',
            'categoria.required' => 'Escolhe a categoria antes de publicar.',
        ]);
    }

    private function criarNoticia(Request $request, array $dados, bool $publicar, string $acao): News
    {
        $noticia = new News();
        $noticia->fill($this->atributos($dados, $publicar, $request, null, $acao));
        $noticia->save();

        $this->processarMidia($request, $noticia);

        return $noticia;
    }

    private function atualizarNoticia(Request $request, News $noticia, array $dados, bool $publicar, string $acao): void
    {
        $noticia->fill($this->atributos($dados, $publicar, $request, $noticia, $acao));
        $noticia->save();

        $this->processarMidia($request, $noticia);
    }

    /**
     * Copia os campos do formulário para o modelo (Fase 6).
     *
     * Submissão parcial: em edição, um campo ausente do pedido mantém o valor
     * actual (mesma regra do formulário genérico — um POST incompleto não pode
     * apagar excerto/conteúdo/categoria). Campo presente mas vazio limpa-se.
     *
     * Fluxo editorial: quem define o estado é a acao do botão (verificarPermissaoPosse).
     *
     * @return array<string, mixed>
     */
    private function atributos(array $dados, bool $publicar, Request $request, ?News $atual = null, string $acao = 'rascunho'): array
    {
        $tem = fn (string $campo) => $request->has($campo);

        $status = match ($acao) {
            'publicar' => 'published',
            'submeter_revisao' => 'em_revisao',
            'devolver_rascunho', 'despublicar' => 'draft',
            default => $atual?->status ?? 'draft',
        };

        $agora = now();

        // Fase 8.2: data/hora de publicação pedida ("Publicar em").
        // Se for no futuro, mantém status=published mas o artigo permanece
        // invisível no frontend até à hora chegar (pelo scopePublished).
        $agendado = null;
        if ($acao === 'publicar') {
            $vindo = trim((string) ($dados['publicar_em'] ?? ''));
            if ($vindo !== '') {
                $candidato = \Illuminate\Support\Carbon::parse($vindo);
                if ($candidato->isFuture()) {
                    $agendado = $candidato;
                }
            }
        }

        $publishedAt = match ($acao) {
            'publicar' => ($agendado ?? ($atual?->published_at ?? $agora)),
            'submeter_revisao', 'devolver_rascunho', 'despublicar' => null,
            default => $atual?->published_at,
        };

        // Autor do Artigo (autor.md §2.2/§2.3):
        //  - "interno"   → o utilizador interno selecionado (ou eu, por omissão);
        //  - "convidado" → sem conta: nome do convidado, author_id fica nulo;
        //  - campo ausente → mantém o atual; na criação, eu sou o autor.
        $escolha = trim((string) $request->input('author_choice', ''));
        $guestName = $tem('guest_author_name')
            ? (trim((string) ($dados['guest_author_name'] ?? '')) ?: null)
            : $atual?->guest_author_name;

        $authorId = $atual?->author_id;
        $guestNomeFinal = $atual?->guest_author_name;

        if ($escolha === 'interno') {
            $authorId = (int) ($dados['author_id'] ?? 0) ?: $request->user()?->id;
            $guestNomeFinal = null;
        } elseif ($escolha === 'convidado') {
            $authorId = null;
            $guestNomeFinal = $guestName;
        } elseif ($atual === null) {
            $authorId = $request->user()?->id;
        }

        $dateLabel = $publishedAt !== null
            ? $publishedAt->locale('pt')->translatedFormat('d M Y')
            : null;

        return [
            'title' => trim($dados['titulo']),
            'slug' => $this->slugDoPedido($dados, $request, $atual),
            'excerpt' => $tem('subtitulo')
                ? (trim((string) ($dados['subtitulo'] ?? '')) ?: null)
                : $atual?->excerpt,
            'content' => $tem('conteudo')
                ? ArticleSanitizer::clean($dados['conteudo'] ?? null)
                : $atual?->content,
            'category_id' => $tem('categoria')
                ? ($dados['categoria'] ?? null)
                : $atual?->category_id,
            'tags' => $tem('tags')
                ? (trim((string) ($dados['tags'] ?? '')) ?: null)
                : $atual?->tags,
            'image_alt' => $tem('image_alt')
                ? (trim((string) ($dados['image_alt'] ?? '')) ?: null)
                : $atual?->image_alt,
            'image_caption' => $tem('image_caption')
                ? (trim((string) ($dados['image_caption'] ?? '')) ?: null)
                : $atual?->image_caption,
            'meta_title' => $tem('meta_title')
                ? (trim((string) ($dados['meta_title'] ?? '')) ?: null)
                : $atual?->meta_title,
            'meta_description' => $tem('meta_description')
                ? (trim((string) ($dados['meta_description'] ?? '')) ?: null)
                : $atual?->meta_description,
            'video_link' => $tem('videoLink')
                ? ($dados['videoLink'] ?? null)
                : $atual?->video_link,
            'documento_link' => $tem('documentoLink')
                ? ($dados['documentoLink'] ?? null)
                : $atual?->documento_link,
            'link' => $tem('link')
                ? ($dados['link'] ?? null)
                : $atual?->link,
            'status' => $status,
            'published_at' => $publishedAt,
            'date_label' => $dateLabel,
            'author_id' => $authorId,
            'guest_author_name' => $guestNomeFinal,
        ];
    }

    /**
     * Slug: pedido do utilizador (editável) ou derivado do título (novo registo
     * ou campo vazio). Em edição, campo ausente mantém o atual.
     */
    private function slugDoPedido(array $dados, Request $request, ?News $atual): string
    {
        $fornecido = trim((string) ($dados['slug'] ?? ''));

        if ($fornecido !== '') {
            $base = Str::slug($fornecido);
            $candidato = $base !== '' ? $base : Str::slug($dados['titulo']);

            $ignore = $atual?->id;
            $final = $candidato;
            $n = 1;

            while (News::query()->withTrashed()->where('slug', $final)->where('id', '!=', $ignore ?? 0)->exists()) {
                $final = $candidato . '-' . $n++;
            }

            return $final;
        }

        return $atual?->slug ?? $this->slugUnico($dados['titulo']);
    }

    private function processarMidia(Request $request, News $noticia): void
    {
        // Capa por LINK (sem ficheiro): a URL fica gravada em `news.image`.
        $capaLink = trim((string) $request->input('capa_link', ''));

        if ($capaLink !== '' && ! $request->hasFile('capa')) {
            $noticia->image = $capaLink;
            $noticia->save();
        } elseif ($request->hasFile('capa')) {
            $media = $noticia->addMedia($request->file('capa'))->toMediaCollection('capa');
            $noticia->image = 'storage/' . $media->file_name;
            $noticia->save();
        }

        foreach ($request->file('contexto', []) as $ficheiro) {
            $noticia->addMedia($ficheiro)->toMediaCollection('contexto');
        }

        // Foto do autor convidado (autor.md §2.2): ficheiro → miniatura
        // 150x150 em avatars/; link → URL tal como está; "interno" limpa.
        $escolhaAutor = trim((string) $request->input('author_choice', ''));
        $guestLink = trim((string) $request->input('guest_author_avatar_link', ''));

        if ($escolhaAutor === 'interno') {
            if ($noticia->guest_author_avatar !== null) {
                \App\Support\Autor::apagarLocal($noticia->guest_author_avatar);
                $noticia->guest_author_avatar = null;
                $noticia->save();
            }

            return;
        }

        // convidado OU campo ausente — sem pedido completo não se mexe.
        if ($guestLink !== '' && ! $request->hasFile('guest_author_avatar')) {
            \App\Support\Autor::apagarLocal($noticia->guest_author_avatar);
            $noticia->guest_author_avatar = $guestLink;
            $noticia->save();
        } elseif ($request->hasFile('guest_author_avatar')) {
            $antigo = $noticia->guest_author_avatar;
            $novo = null;

            try {
                $novo = \App\Support\Autor::guardarMiniatura($request->file('guest_author_avatar'), 'guest');
            } catch (\Throwable $excecao) {
                report($excecao);
            }

            if ($novo !== null) {
                \App\Support\Autor::apagarLocal($antigo);
                $noticia->guest_author_avatar = $novo;
                $noticia->save();
            }
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
