<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Interview;
use App\Models\Legislation;
use App\Models\News;
use App\Models\Project;
use App\Models\Research;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Formulários genéricos do painel admin: criação dos 4 tipos que o protótipo
 * só apresentava como botão morto e edição de todos os tipos da tabela.
 *
 * Motivo de ser genérico (e não 8 páginas): Event, Research, Interview e
 * Legislation têm **exactamente as mesmas colunas** que a tabela base de
 * conteúdo, e os formulários do protótipo (`noticia-nova.html`) partilham o
 * mesmo desenho. Definir um mapa tipo → modelo/estados/campos, como o
 * `ContentTable` já faz para as tabelas, evita 8 vistas quase idênticas.
 *
 * **Criação**: o protótipo só tem páginas para notícia e projeto (que ficam
 * nos respectivos controladores). Os botões "Novo evento/estudo/entrevista/
 * legislação" eram `type="submit"` sem `<form>` — não faziam nada. Aqui
 * reutilizam o desenho de `noticia-nova.html`, sem inventar linguagem visual
 * nova (fase.md §7).
 *
 * **Edição**: o botão caneta da tabela era renderizado sem `wire:click` nem
 * link — não fazia nada. Não existia página de edição em lado nenhum.
 *
 * A autorização é `Gate::authorize` por tipo dentro do método (e não `can:`
 * na rota) porque a permissão varia com o `$tipo` da URL.
 */
class ContentFormController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | Rótulos por tipo de estado
    |----------------------------------------------------------------------
    |
    | Mesma vocabulário do protótipo e do `ContentTable`: cada tipo chama ao
    | mesmo par de valores de `status` de maneira diferente (uma vaga é
    | "Ativa", um projeto é "Ativa", uma empresa é "Aprovada"). Cada entrada
    | é [valor do select, coluna `status`, rótulo apresentado].
    |
    */

    private const ESTADOS_CONTEUDO = [
        'publicar' => ['published', 'Publicada'],
        'rascunho' => ['draft', 'Rascunho'],
        'rejeitada' => ['rejected', 'Rejeitada'],
    ];

    private const ESTADOS_PROJETO = [
        'ativo' => ['published', 'Ativa'],
        'em-analise' => ['draft', 'Em análise'],
        'expirada' => ['rejected', 'Expirada'],
    ];

    /**
     * Rótulo da lista de origem, para o breadcrumb (Admin → Notícias → Nova).
     *
     * @var array<string, string>
     */
    private const ROTULOS_LISTA = [
        'eventos' => 'Eventos',
        'investigacao' => 'Investigação',
        'entrevistas' => 'Entrevistas',
        'legislacao' => 'Legislação',
        'noticias' => 'Notícias',
        'projetos' => 'Projetos',
    ];

    /**
     * Mapa tipo (segmento da URL) → definição do formulário.
     *
     * @var array<string, array>
     */
    public const TIPOS = [
        // --- Os 4 que faltavam criar (colunas da tabela base) ---------------
        'eventos' => [
            'modelo' => Event::class,
            'categoria' => 'events',
            'lista' => 'admin.eventos',
            'permissao' => 'event.manage',
            'titulo' => 'Novo evento',
            'tituloEditar' => 'Editar evento',
            'criavel' => true,
            'estados' => self::ESTADOS_CONTEUDO,
        ],
        'investigacao' => [
            'modelo' => Research::class,
            'categoria' => 'research',
            'lista' => 'admin.investigacao',
            'permissao' => 'research.manage',
            'titulo' => 'Novo estudo',
            'tituloEditar' => 'Editar estudo',
            'criavel' => true,
            'estados' => self::ESTADOS_CONTEUDO,
        ],
        'entrevistas' => [
            'modelo' => Interview::class,
            'categoria' => 'interviews',
            'lista' => 'admin.entrevistas',
            'permissao' => 'interview.manage',
            'titulo' => 'Nova entrevista',
            'tituloEditar' => 'Editar entrevista',
            'criavel' => true,
            'estados' => self::ESTADOS_CONTEUDO,
        ],
        'legislacao' => [
            'modelo' => Legislation::class,
            'categoria' => 'legislation',
            'lista' => 'admin.legislacao',
            'permissao' => 'legislation.manage',
            'titulo' => 'Nova legislação',
            'tituloEditar' => 'Editar legislação',
            'criavel' => true,
            'estados' => self::ESTADOS_CONTEUDO,
        ],

        // --- Já criáveis por controlador próprio (só aqui se editam) --------
        'noticias' => [
            'modelo' => News::class,
            'categoria' => 'news',
            'lista' => 'admin.noticias',
            'permissao' => 'news.manage',
            'titulo' => null, // criação vive no editor de notícias (Livewire + TinyMCE)
            'tituloEditar' => 'Editar notícia',
            'criavel' => false,
            'estados' => self::ESTADOS_CONTEUDO,
            'extras' => [
                ['input' => 'provincia', 'coluna' => 'province', 'rotulo' => 'Província', 'tipo' => 'provincia'],
                // Reestruturação (restrutura.md §3.4): vídeo e documento são
                // APENAS link — validação url (regras por tipo 'text' com url?).
                ['input' => 'video_link', 'coluna' => 'video_link', 'rotulo' => 'Link do vídeo', 'tipo' => 'url', 'placeholder' => 'https://… (YouTube/Vimeo)'],
                ['input' => 'documento_link', 'coluna' => 'documento_link', 'rotulo' => 'Link do documento', 'tipo' => 'url', 'placeholder' => 'https://… (PDF/legislação)'],
                ['input' => 'tags', 'coluna' => 'tags', 'rotulo' => 'Etiquetas', 'tipo' => 'text', 'placeholder' => 'separadas por vírgulas, ex.: mineração, diamantes'],
                ['input' => 'meta_title', 'coluna' => 'meta_title', 'rotulo' => 'Título nas pesquisas (Google)', 'tipo' => 'text', 'placeholder' => 'Título que aparece no Google (até 60 caracteres)'],
                ['input' => 'meta_description', 'coluna' => 'meta_description', 'rotulo' => 'Descrição nas pesquisas (Google)', 'tipo' => 'textarea', 'linhas' => 2, 'placeholder' => 'Descrição que aparece no Google (até 160 caracteres)'],
            ],
        ],
        'projetos' => [
            'modelo' => Project::class,
            'categoria' => 'projects',
            'lista' => 'admin.projetos',
            'permissao' => 'project.manage',
            'titulo' => null, // criação vive no ProjectFormController
            'tituloEditar' => 'Editar projeto',
            'criavel' => false,
            'estados' => self::ESTADOS_PROJETO,
            'rotuloTitulo' => 'Nome',
            'extras' => [
                ['input' => 'mineral', 'coluna' => 'mineral', 'rotulo' => 'Mineral', 'tipo' => 'select', 'opcoes' => [
                    'ferro' => 'Ferro', 'ouro' => 'Ouro', 'diamantes' => 'Diamantes', 'litio' => 'Lítio',
                    'manganes' => 'Manganês', 'cobre' => 'Cobre', 'fosfatos' => 'Fosfatos',
                ]],
                ['input' => 'provincia', 'coluna' => 'province', 'rotulo' => 'Província', 'tipo' => 'provincia'],
                ['input' => 'fase', 'coluna' => 'phase', 'rotulo' => 'Fase', 'tipo' => 'select', 'opcoes' => [
                    'prospeccao' => 'Prospecção', 'exploracao' => 'Exploração', 'producao' => 'Produção',
                ]],
                ['input' => 'operador', 'coluna' => 'operator', 'rotulo' => 'Operador', 'tipo' => 'text', 'placeholder' => 'Empresa operadora'],
                ['input' => 'area', 'coluna' => 'area', 'rotulo' => 'Área (km²)', 'tipo' => 'number'],
            ],
        ],
    ];

    /** Tipos com página de criação própria neste controlador. */
    public static function tiposCriaveis(): array
    {
        return array_keys(array_filter(self::TIPOS, fn ($conf) => $conf['criavel']));
    }

    /** Tipos com página de edição (todos — é para aí que vai o botão caneta). */
    public static function tiposEditaveis(): array
    {
        return array_keys(self::TIPOS);
    }

    /*
    |----------------------------------------------------------------------
    | Criação
    |----------------------------------------------------------------------
    */

    public function create(string $tipo): View
    {
        $conf = $this->configuracao($tipo);

        abort_unless($conf['criavel'], 404);

        return $this->formulario($conf, $tipo, null, $conf['titulo']);
    }

    public function store(Request $request, string $tipo): RedirectResponse
    {
        $conf = $this->configuracao($tipo);

        abort_unless($conf['criavel'], 404);

        // Permissão granular do módulo (Fase 13) — mesma defesa em
        // profundidade do ContentTable, aqui porque a rota não pode ter
        // `can:` fixo: a permissão varia com o `$tipo` da URL.
        Gate::authorize($conf['permissao']);

        $dados = $request->validate($this->regras($conf));

        $modelo = new $conf['modelo']();
        $status = $this->statusFinal($conf, $dados, 'draft');

        $this->preencher($modelo, $conf, $dados, $request, $status);
        $modelo->author_id = $request->user()->id;
        $modelo->title = $dados['titulo'];
        $modelo->slug = $this->slugUnico($conf['modelo'], $dados['slug'] ?? '', $dados['titulo']);
        $modelo->status = $status;
        $modelo->views_count = 0;

        $this->guardarImagem($modelo, $request);

        $modelo->save();

        return redirect()->route($conf['lista']);
    }

    /*
    |----------------------------------------------------------------------
    | Edição
    |----------------------------------------------------------------------
    */

    public function edit(string $tipo, int $id): View|RedirectResponse
    {
        // Notícias têm o editor próprio (CMS Pro §4.2, Livewire + TinyMCE) —
        // nunca passam pelo formulário genérico.
        if ($tipo === 'noticias') {
            return redirect()->route('admin.noticia-editar', ['noticia' => $id]);
        }

        $conf = $this->configuracao($tipo);

        Gate::authorize($conf['permissao']);

        $registo = $conf['modelo']::whereKey($id)->firstOrFail();

        return $this->formulario($conf, $tipo, $registo, $conf['tituloEditar']);
    }

    public function update(Request $request, string $tipo, int $id): RedirectResponse
    {
        $conf = $this->configuracao($tipo);

        Gate::authorize($conf['permissao']);

        $registo = $conf['modelo']::whereKey($id)->firstOrFail();

        $dados = $request->validate($this->regras($conf, $registo));

        $status = $this->statusFinal($conf, $dados, $registo->status);

        $this->preencher($registo, $conf, $dados, $request, $status);
        $registo->title = $dados['titulo'];

        // Slug: mantém o actual quando o campo vem vazio. Sem isto, editar só
        // o título rederiva o slug e quebrava as URLs já publicadas (o
        // `unique` continua a ignorar o próprio registo quando há valor novo).
        $registo->slug = ($dados['slug'] ?? '') !== ''
            ? $this->slugUnico($conf['modelo'], $dados['slug'], $dados['titulo'], $registo->id)
            : $registo->slug;

        $registo->status = $status;

        $this->guardarImagem($registo, $request);

        $registo->save();

        return redirect()->route($conf['lista']);
    }

    /*
    |----------------------------------------------------------------------
    | Internals
    |----------------------------------------------------------------------
    */

    /** Monta a vista partilhada por criar e editar (mesmo desenho do protótipo). */
    private function formulario(array $conf, string $tipo, ?object $registo, string $titulo): View
    {
        return view('admin.conteudo-form', [
            'conf' => $conf,
            'tipo' => $tipo,
            'registo' => $registo,
            'titulo' => $titulo,
            // Migalha "Novo/Nova/Editar": derivada do próprio título, que já
            // traz o género ("Nova entrevista" vs "Novo evento") — evita uma
            // tabela de género à parte.
            'migalha' => $registo === null
                ? (str_starts_with($titulo, 'Nova') ? 'Nova' : 'Novo')
                : 'Editar',
            'rotuloLista' => self::ROTULOS_LISTA[$tipo] ?? '',
            'categorias' => Category::query()->where('type', $conf['categoria'])->orderBy('name')->get(),
            'campos' => $this->campos($conf, $registo),
            'estadoAtual' => $this->chaveEstado($conf, $registo?->status),
        ]);
    }

    /**
     * Lista plana de campos para a vista: [{nome, rotulo, tipo, valor, ...}].
     *
     * `valor` já vem resolvido (registo actual ou `old()`), para que a vista
     * só precise de alternar no `tipo` de cada campo.
     *
     * @return list<array>
     */
    private function campos(array $conf, ?object $registo): array
    {
        $para = fn ($input, $coluna) => old($input, $registo?->{$coluna});

        $campos = [
            ['nome' => 'titulo', 'rotulo' => $conf['rotuloTitulo'] ?? 'Título', 'tipo' => 'text',
                'placeholder' => $conf['rotuloTitulo'] ?? 'Título', 'obrigatorio' => true, 'valor' => $para('titulo', 'title')],
            ['nome' => 'slug', 'rotulo' => 'Endereço da página', 'tipo' => 'text',
                'placeholder' => 'endereco-da-pagina', 'hint' => 'Deixe como está, a menos que saiba o que está a mudar.', 'valor' => $para('slug', 'slug')],
            ['nome' => 'categoria', 'rotulo' => 'Categoria', 'tipo' => 'categoria',
                'valor' => $para('categoria', 'category_id')],
            ['nome' => 'estado', 'rotulo' => 'Estado', 'tipo' => 'estado', 'valor' => null],
            ['nome' => 'agendamento', 'rotulo' => 'Data e hora de publicação', 'tipo' => 'datetime',
                'valor' => old('agendamento', $registo?->published_at?->format('Y-m-d\TH:i'))],
            ['nome' => 'excerto', 'rotulo' => 'Resumo curto', 'tipo' => 'textarea', 'linhas' => 3,
                'placeholder' => 'Resumo que aparece nas listagens…', 'valor' => $para('excerto', 'excerpt')],
            ['nome' => 'conteudo', 'rotulo' => 'Conteúdo', 'tipo' => 'textarea', 'linhas' => 7,
                'placeholder' => 'Corpo do registo…', 'valor' => $para('conteudo', 'content')],
            ['nome' => 'imagem', 'rotulo' => 'Imagem de destaque', 'tipo' => 'ficheiro',
                'valor' => null, 'actual' => $registo?->image],
        ];

        foreach ($conf['extras'] ?? [] as $extra) {
            // A vista chama o campo pelo nome do input; `input`/`coluna` são
            // os nomes que usam a validação e as colunas da BD.
            $extra['nome'] = $extra['input'];
            $extra['valor'] = $para($extra['input'], $extra['coluna']);
            $campos[] = $extra;
        }

        return $campos;
    }

    /**
     * Regras de validação — as de criação e as de edição são as mesmas, com
     * o `unique` do slug a ignorar o próprio registo quando se edita.
     */
    private function regras(array $conf, ?object $registo = null): array
    {
        $tabela = $conf['modelo']::query()->getModel()->getTable();

        $regras = [
            'titulo' => ['required', 'string', 'max:190'],
            // O `ignore` impede que guardar sem mexer no slug dê choque
            // consigo próprio (unicidade da coluna `slug`).
            'slug' => $registo === null
                ? ['nullable', 'string', 'max:190', Rule::unique($tabela, 'slug')]
                : ['nullable', 'string', 'max:190', Rule::unique($tabela, 'slug')->ignore($registo->id)],
            'categoria' => ['nullable', Rule::exists('categories', 'id')->where('type', $conf['categoria'])],
            'estado' => ['nullable', Rule::in(array_keys($conf['estados']))],
            'agendamento' => ['nullable', 'date'],
            'excerto' => ['nullable', 'string', 'max:500'],
            'conteudo' => ['nullable', 'string'],
            'imagem' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:4096'],
        ];

        foreach ($conf['extras'] ?? [] as $extra) {
            if ($extra['tipo'] === 'provincia') {
                $regras['provincia'] = ['nullable', 'string', 'max:100'];
                $regras['provincia_outro'] = ['nullable', 'string', 'max:100', 'required_if:provincia,outros'];
                continue; // a iteração define apenas a regra deste campo
            }

            $regras[$extra['input']] = match ($extra['tipo']) {
                'select' => ['nullable', Rule::in(array_keys($extra['opcoes']))],
                'textarea' => ['nullable', 'string', 'max:2000'],
                'number' => ['nullable', 'numeric', 'min:0'],
                'date' => ['nullable', 'date'],
                'url' => ['nullable', 'url', 'max:300'],
                default => ['nullable', 'string', 'max:190'],
            };
        }

        return $regras;
    }

    /**
     * Copia os campos do formulário para as colunas do modelo.
     *
     * Em edição só se atribui o que veio no pedido (`$request->has`): o
     * formulário completo envia todos os campos, mas um POST incompleto não
     * pode apagar excerto/conteúdo/categoria de um registo. Em criação tudo
     * é atribuído (o registo é novo e os ausentes ficam a null).
     */
    private function preencher(object $modelo, array $conf, array $dados, Request $request, string $status): void
    {
        $edicao = $modelo->exists;

        if (! $edicao || $request->has('categoria')) {
            $modelo->category_id = $dados['categoria'] ?? null;
        }

        if (! $edicao || $request->has('excerto')) {
            $modelo->excerpt = $dados['excerto'] ?? null;
        }

        if (! $edicao || $request->has('conteudo')) {
            $modelo->content = $dados['conteudo'] ?? null;
        }

        foreach ($conf['extras'] ?? [] as $extra) {
            if (! $edicao || $request->has($extra['input'])) {
                $modelo->{$extra['coluna']} = $extra['tipo'] === 'provincia'
                    ? \App\Support\Provincias::resolve($dados['provincia'] ?? null, $request->input('provincia_outro'))
                    : ($dados[$extra['input']] ?? null);
            }
        }

        $agendamento = $request->date('agendamento');

        if ($agendamento !== null) {
            $modelo->published_at = $agendamento;
        } elseif (! $edicao) {
            // Criação: só marca data se o formulário pediu já publicar.
            $modelo->published_at = $status === 'published' ? now() : null;
        } elseif ($status === 'published' && $modelo->published_at === null) {
            // Edição: aprovar um registo sem data deixava-o invisível nos
            // cartões públicos (que ordenam por `published_at`).
            $modelo->published_at = now();
        }
        // Edição sem agendamento novo mantém a data que já tinha — não se
        // apaga uma publicação por omissão de campo vazio.

        $modelo->date_label = $modelo->published_at?->locale('pt')->translatedFormat('d M Y');
    }

    /**
     * `status` final do formulário: o escolhido no select, ou o que o registo
     * já tinha quando o select vem vazio (edição).
     */
    private function statusFinal(array $conf, array $dados, string $atual): string
    {
        $chave = $dados['estado'] ?? '';

        return ($chave !== '' && isset($conf['estados'][$chave])) ? $conf['estados'][$chave][0] : $atual;
    }

    /** Grava a imagem enviada (substitui a anterior e apaga o ficheiro velho). */
    private function guardarImagem(object $modelo, Request $request): void
    {
        if (! $request->hasFile('imagem')) {
            return;
        }

        $antiga = $modelo->image;

        // Pasta = nome da tabela, a mesma convenção dos outros forms do painel
        // (`news/`) e ProjectFormController (`projects/`).
        $modelo->image = 'storage/'.$request->file('imagem')->store($modelo->getTable(), 'public');

        // Só apaga o ficheiro se fôr nosso (prefixo `storage/`): as imagens do
        // seeder vivem em `images/` e são assets partilhados do protótipo.
        if ($antiga !== null && str_starts_with($antiga, 'storage/')) {
            Storage::disk('public')->delete(substr($antiga, strlen('storage/')));
        }
    }

    /** Chave do select "Estado" correspondente ao `status` do registo. */
    private function chaveEstado(array $conf, ?string $status): string
    {
        if ($status === null) {
            return '';
        }

        foreach ($conf['estados'] as $chave => [$valor]) {
            if ($valor === $status) {
                return $chave;
            }
        }

        return '';
    }

    /** Slug indicado ou derivado do título, sufixado até ficar único. */
    private function slugUnico(string $modelo, string $slug, string $titulo, ?int $ignorar = null): string
    {
        $base = Str::slug($slug !== '' ? $slug : $titulo);
        $candidato = $base;
        $n = 1;

        $consulta = $modelo::query()->withTrashed()->where('slug', $candidato);

        if ($ignorar !== null) {
            $consulta->where('id', '!=', $ignorar);
        }

        while ($consulta->exists()) {
            $candidato = $base.'-'.$n++;
            $consulta = $modelo::query()->withTrashed()->where('slug', $candidato);

            if ($ignorar !== null) {
                $consulta->where('id', '!=', $ignorar);
            }
        }

        return $candidato;
    }

    /** Definição do tipo, com 404 se não for um tipo conhecido. */
    private function configuracao(string $tipo): array
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);

        return self::TIPOS[$tipo];
    }
}
