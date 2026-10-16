<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\FiltraData;
use App\Models\Category;
use App\Models\Directory;
use App\Models\Event;
use App\Models\Interview;
use App\Models\Job;
use App\Models\Legislation;
use App\Models\News;
use App\Models\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela genérica das páginas `admin/*.html` do protótipo.
 *
 * Cada página define aqui as suas colunas/filtros/badges (texto exacto do
 * protótipo) e a vista só muda o cabeçalho do cartão + o `tipo`.
 *
 * Colunas sem fonte de dados (ex.: "Mineral", "Setor" — o extrator do
 * protótipo não as captou e não existem na BD) ficam com "—" para não
 * inventar conteúdo nem alterar a estrutura da tabela.
 */
class ContentTable extends Component
{
    use FiltraData;
    use WithPagination;

    /** @var array<string, array> Definição por página (chave = `tipo`). */
    protected const TABELAS = [
        'noticias' => [
            'modelo' => News::class,
            'vazio' => 'Nenhuma notícia encontrada.',
            'ver' => 'news.show',
            'categoria' => 'news',
            'ordem' => 'published_at',
            'acoes' => 'crud',
            'permissao' => 'news.manage',
            'colunas' => [
                ['rotulo' => 'Título', 'campo' => 'title'],
                ['rotulo' => 'Categoria', 'campo' => 'categoria'],
                ['rotulo' => 'Autor', 'campo' => 'autor'],
                ['rotulo' => 'Data', 'campo' => 'data'],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            'filtros' => [
                ['nome' => 'categoria', 'rotulo' => 'Categoria: todas', 'campo' => 'categoria'],
                ['nome' => 'estado', 'rotulo' => 'Estado: todos', 'campo' => 'status', 'opcoes' => ['Publicada', 'Rascunho', 'Rejeitada'], 'mapa' => ['Publicada' => 'published', 'Rascunho' => 'draft', 'Rejeitada' => 'rejected']],
            ],
            'busca' => ['tipo' => 'data', 'placeholder' => 'Filtrar por data (mm/aaaa)'],
            'estado' => ['published' => ['active', 'Publicada'], 'draft' => ['pending', 'Rascunho'], 'rejected' => ['rejected', 'Rejeitada'], 'pending' => ['pending', 'Em análise']],
        ],

        'eventos' => [
            'modelo' => Event::class,
            'vazio' => 'Nenhum evento encontrado.',
            'ordem' => 'published_at',
            'acoes' => 'crud',
            'permissao' => 'event.manage',
            'colunas' => [
                ['rotulo' => 'Título', 'campo' => 'title'],
                ['rotulo' => 'Data', 'campo' => 'data'],
                ['rotulo' => 'Local', 'campo' => null],
                ['rotulo' => 'Organizador', 'campo' => null],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            'filtros' => [
                ['nome' => 'estado', 'rotulo' => 'Estado: todos', 'campo' => 'status', 'opcoes' => ['Publicado', 'Rascunho', 'Rejeitado'], 'mapa' => ['Publicado' => 'published', 'Rascunho' => 'draft', 'Rejeitado' => 'rejected']],
            ],
            'busca' => ['tipo' => 'data', 'placeholder' => 'Filtrar por data (mm/aaaa)'],
            'estado' => ['published' => ['active', 'Publicado'], 'draft' => ['pending', 'Rascunho'], 'rejected' => ['rejected', 'Rejeitado'], 'pending' => ['pending', 'Em análise']],
        ],

        'investigacao' => [
            'modelo' => \App\Models\Research::class,
            'vazio' => 'Nenhum estudo encontrado.',
            'ordem' => 'published_at',
            'acoes' => 'crud',
            'permissao' => 'research.manage',
            'colunas' => [
                ['rotulo' => 'Título', 'campo' => 'title'],
                ['rotulo' => 'Tipo', 'campo' => null],
                ['rotulo' => 'Instituição', 'campo' => null],
                ['rotulo' => 'Província', 'campo' => null],
                ['rotulo' => 'Ano', 'campo' => 'ano'],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            'filtros' => [
                ['nome' => 'tipo', 'rotulo' => 'Tipo: todos', 'opcoes' => ['Estudo', 'Artigo', 'Bolsa', 'Projeto']],
            ],
            'busca' => ['tipo' => 'titulo', 'placeholder' => 'Procurar estudos…'],
            'estado' => ['published' => ['active', 'Publicada'], 'draft' => ['pending', 'Rascunho'], 'rejected' => ['rejected', 'Rejeitada'], 'pending' => ['pending', 'Em análise']],
        ],

        'entrevistas' => [
            'modelo' => Interview::class,
            'vazio' => 'Nenhuma entrevista encontrada.',
            'ver' => null,
            'ordem' => 'published_at',
            'acoes' => 'crud',
            'permissao' => 'interview.manage',
            'colunas' => [
                ['rotulo' => 'Nome', 'campo' => 'title'],
                ['rotulo' => 'Setor', 'campo' => null],
                ['rotulo' => 'Data', 'campo' => 'data'],
                ['rotulo' => 'Leitura', 'campo' => null],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            'filtros' => [
                ['nome' => 'estado', 'rotulo' => 'Estado: todos', 'campo' => 'status', 'opcoes' => ['Publicada', 'Rascunho', 'Rejeitada'], 'mapa' => ['Publicada' => 'published', 'Rascunho' => 'draft', 'Rejeitada' => 'rejected']],
            ],
            'busca' => ['tipo' => 'data', 'placeholder' => 'Filtrar por data (mm/aaaa)'],
            'estado' => ['published' => ['active', 'Publicada'], 'draft' => ['pending', 'Rascunho'], 'rejected' => ['rejected', 'Rejeitada'], 'pending' => ['pending', 'Em análise']],
        ],

        'legislacao' => [
            'modelo' => Legislation::class,
            'vazio' => 'Nenhum diploma encontrado.',
            'ordem' => 'published_at',
            'acoes' => 'crud',
            'permissao' => 'legislation.manage',
            'colunas' => [
                ['rotulo' => 'Título', 'campo' => 'title'],
                ['rotulo' => 'Tipo', 'campo' => null],
                ['rotulo' => 'Província', 'campo' => null],
                ['rotulo' => 'Publicação', 'campo' => 'data'],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            'filtros' => [
                ['nome' => 'tipo', 'rotulo' => 'Tipo: todos', 'opcoes' => ['Lei', 'Decreto', 'Resolução', 'Despacho']],
            ],
            'busca' => ['tipo' => 'titulo', 'placeholder' => 'Procurar diplomas…'],
            'estado' => ['published' => ['active', 'Publicada'], 'draft' => ['pending', 'Rascunho'], 'rejected' => ['rejected', 'Rejeitada'], 'pending' => ['pending', 'Em análise']],
        ],

        'projetos' => [
            'modelo' => Project::class,
            'vazio' => 'Nenhum projeto encontrado.',
            'ver' => 'projects.show',
            'ordem' => 'published_at',
            'acoes' => 'crud',
            'permissao' => 'project.manage',
            'colunas' => [
                ['rotulo' => 'Nome', 'campo' => 'title'],
                ['rotulo' => 'Mineral', 'campo' => 'mineral'],
                ['rotulo' => 'Província', 'campo' => 'province'],
                ['rotulo' => 'Fase', 'campo' => 'phase'],
                ['rotulo' => 'Operador', 'campo' => 'operator'],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            // Rótulos do protótipo para os valores guardados (colunas criadas
            // na Fase 8); os registos do seeder ficam com `—` (sem dados).
            'mapas' => [
                'mineral' => ['ferro' => 'Ferro', 'ouro' => 'Ouro', 'diamantes' => 'Diamantes', 'litio' => 'Lítio', 'manganes' => 'Manganês', 'cobre' => 'Cobre', 'fosfatos' => 'Fosfatos'],
                'province' => ['huila' => 'Huíla', 'namibe' => 'Namibe', 'uige' => 'Uíge', 'cuanza-sul' => 'Cuanza Sul', 'benguela' => 'Benguela', 'cuando-cubango' => 'Cuando Cubango'],
                'phase' => ['prospeccao' => 'Prospecção', 'exploracao' => 'Exploração', 'producao' => 'Produção'],
            ],
            'filtros' => [
                ['nome' => 'fase', 'rotulo' => 'Fase: todas', 'campo' => 'phase', 'opcoes' => ['Prospecção', 'Exploração', 'Produção'], 'mapa' => ['Prospecção' => 'prospeccao', 'Exploração' => 'exploracao', 'Produção' => 'producao']],
                ['nome' => 'estado', 'rotulo' => 'Estado: todos', 'campo' => 'status', 'opcoes' => ['Ativo', 'Em análise', 'Expirada'], 'mapa' => ['Ativo' => 'published', 'Em análise' => 'draft', 'Expirada' => 'rejected']],
            ],
            'busca' => null,
            'estado' => ['published' => ['active', 'Ativa'], 'draft' => ['pending', 'Em análise'], 'rejected' => ['rejected', 'Expirada'], 'pending' => ['pending', 'Em análise']],
        ],

        'empresas' => [
            'modelo' => Directory::class,
            'vazio' => 'Nenhuma empresa encontrada.',
            'ordem' => 'published_at',
            'acoes' => 'aprovar',
            'permissao' => 'company.moderate',
            'colunas' => [
                ['rotulo' => 'Nome', 'campo' => 'title'],
                ['rotulo' => 'Setor', 'campo' => null],
                ['rotulo' => 'Província', 'campo' => null],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            'filtros' => [
                ['nome' => 'setor', 'rotulo' => 'Setor: todos', 'opcoes' => ['Mineração', 'Água', 'Ambiente', 'Energia', 'Serviços']],
                ['nome' => 'estado', 'rotulo' => 'Estado: todos', 'campo' => 'status', 'opcoes' => ['Pendente', 'Aprovada', 'Rejeitada'], 'mapa' => ['Pendente' => 'draft', 'Aprovada' => 'published', 'Rejeitada' => 'rejected']],
            ],
            'busca' => ['tipo' => 'titulo', 'placeholder' => 'Procurar empresa…'],
            'estado' => ['published' => ['active', 'Aprovada'], 'draft' => ['pending', 'Em análise'], 'rejected' => ['rejected', 'Rejeitada'], 'pending' => ['pending', 'Em análise']],
        ],

        'vagas' => [
            'modelo' => Job::class,
            'vazio' => 'Nenhuma vaga encontrada.',
            'ver' => 'jobs.show',
            'ordem' => 'published_at',
            'acoes' => 'crud',
            'permissao' => 'job.moderate',
            'colunas' => [
                ['rotulo' => 'Título', 'campo' => 'title'],
                ['rotulo' => 'Empresa', 'campo' => null],
                ['rotulo' => 'Data', 'campo' => 'data'],
                ['rotulo' => 'Prazo', 'campo' => null],
                ['rotulo' => 'Estado', 'campo' => 'estado'],
            ],
            'filtros' => [
                ['nome' => 'estado', 'rotulo' => 'Estado: todas', 'campo' => 'status', 'opcoes' => ['Ativa', 'Em análise', 'Expirada'], 'mapa' => ['Ativa' => 'published', 'Em análise' => 'draft', 'Expirada' => 'rejected']],
                ['nome' => 'empresa', 'rotulo' => 'Empresa: todas', 'opcoes' => ['Minex Angola', 'Hidrotec', 'EcoAmbiente Lda', 'Ferrangol']],
            ],
            'busca' => ['tipo' => 'data', 'placeholder' => 'Filtrar por data (mm/aaaa)'],
            'estado' => ['published' => ['active', 'Ativa'], 'draft' => ['pending', 'Em análise'], 'rejected' => ['rejected', 'Expirada'], 'pending' => ['pending', 'Em análise']],
        ],
    ];

    public string $tipo = '';

    /** @var array<string, string> Valores dos filtros ligados à BD. */
    public array $filtros = [];

    public string $busca = '';

    public function mount(string $tipo): void
    {
        $this->tipo = $tipo;

        foreach ($this->configuracao()['filtros'] as $filtro) {
            if (! empty($filtro['nome'])) {
                $this->filtros[$filtro['nome']] = '';
            }
        }
    }

    /** Qualquer filtro muda sempre a página para 1. */
    public function updated(string $propriedade): void
    {
        if (str_starts_with($propriedade, 'filtros.') || $propriedade === 'busca') {
            $this->resetPage();
        }
    }

    /** Ver: abre o registo no site público (só onde existe página de detalhe). */
    public function ver(int $id): void
    {
        $conf = $this->configuracao();

        if (empty($conf['ver'])) {
            return;
        }

        $registo = $conf['modelo']::find($id);

        if ($registo !== null) {
            $this->redirect(route($conf['ver'], $registo->slug));
        }
    }

    /**
     * Editar: abre a página de edição do tipo desta tabela.
     *
     * O botão caneta do protótipo era estático (sem `wire:click` nem link);
     * aponta ao formulário genérico, porque o protótipo não tem página de
     * edição para nenhum dos tipos.
     */
    public function editar(int $id): void
    {
        $conf = $this->configuracao();

        // Mesma permissão granular do módulo (Fase 13) — o endpoint Livewire
        // é partilhado pelas 8 páginas, o `can:` da rota não chega.
        Gate::authorize($conf['permissao']);

        $this->redirect(route('admin.conteudo.editar', ['tipo' => $this->tipo, 'id' => $id]));
    }

    /** Eliminar: soft delete (dá para reverter com restore()). */
    public function eliminar(int $id): void
    {
        $conf = $this->configuracao();

        // Permissão granular do módulo (Fase 13) — defesa em profundidade para
        // além do `can:` da rota (o endpoint Livewire é partilhado pelas 8 páginas).
        Gate::authorize($conf['permissao']);

        $registo = $conf['modelo']::find($id);

        if ($registo !== null) {
            $registo->delete();
        }
    }

    /** Aprovação rápida (botões da tabela de empresas). */
    public function aprovar(int $id): void
    {
        $this->mudarEstado($id, 'published');
    }

    public function rejeitar(int $id): void
    {
        $this->mudarEstado($id, 'rejected');
    }

    protected function mudarEstado(int $id, string $estado): void
    {
        $conf = $this->configuracao();

        // Mesma permissão granular do módulo que `eliminar` (Fase 13).
        Gate::authorize($conf['permissao']);

        $registo = $conf['modelo']::find($id);

        if ($registo !== null) {
            $registo->update(['status' => $estado]);
        }
    }

    protected function configuracao(): array
    {
        return self::TABELAS[$this->tipo] ?? self::TABELAS['noticias'];
    }

    public function render()
    {
        $conf = $this->configuracao();

        $query = $conf['modelo']::query()->with(['category', 'author']);

        foreach ($conf['filtros'] as $filtro) {
            if (empty($filtro['nome']) || empty($filtro['campo'])) {
                continue; // filtro só visual (sem coluna correspondente na BD)
            }

            $valor = $this->filtros[$filtro['nome']] ?? '';

            if ($valor === '') {
                continue;
            }

            if ($filtro['campo'] === 'categoria') {
                $query->where('category_id', $valor);

                continue;
            }

            // `status` e quaisquer outras colunas directas (ex.: `phase`) com
            // o valor traduzido pelo `mapa` quando existe.
            $query->where($filtro['campo'], $filtro['mapa'][$valor] ?? $valor);
        }

        if ($this->busca !== '' && ! empty($conf['busca'])) {
            if ($conf['busca']['tipo'] === 'titulo') {
                $query->where('title', 'like', '%'.$this->busca.'%');
            } else {
                $this->filtrarData($query, $this->busca, 'published_at');
            }
        }

        $registos = $query
            ->orderByDesc($conf['ordem'])
            // Desempate: as datas vêm do seeder em série (várias iguais) e sem
            // ele a paginação podia repetir/saltar linhas entre páginas.
            ->orderByDesc('id')
            ->paginate(6);

        return view('livewire.admin.content-table', [
            'conf' => $conf,
            'registos' => $registos,
            'categorias' => ! empty($conf['categoria'])
                ? Category::where('type', $conf['categoria'])->orderBy('name')->get()
                : collect(),
        ]);
    }
}
