<?php

namespace App\Livewire\Admin;

use App\Models\News;
use App\Models\User;
use App\Support\CategoriasNews;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Fase 7 (cms_v.md) — lista de notícias do painel.
 *
 * Tabela dedicada (o ContentTable genérico fica intocado para os outros
 * módulos): título, categoria, autor, estado, data, visitas, ações.
 * Busca por título, filtros estado/categoria/autor, ordenação por coluna
 * e "lixo" (SoftDeletes) com restauração.
 */
class NewsListTable extends Component
{
    use WithPagination;

    /** Busca por título (debounce no Blade). */
    public string $busca = '';

    /** @var array<string, string> Filtros ligados à BD. */
    public array $filtros = [
        'estado' => '',
        'categoria' => '',
        'autor' => '',
    ];

    /** Coluna ordenada e direção. */
    public string $ordenar = 'id';
    public string $direccao = 'desc';

    /** Lixo: mostrar apenas registos SoftDeleted. */
    public bool $mostrarApagados = false;

    public function mount(): void
    {
        $this->ordenar = 'published_at';
        $this->direccao = 'desc';
    }

    public function updatedBusca(): void
    {
        $this->resetPage();
    }

    public function updatedFiltros(): void
    {
        $this->resetPage();
    }

    public function updatedMostrarApagados(): void
    {
        $this->resetPage();
    }

    public function ordenarPor(string $coluna): void
    {
        $permitidas = ['id', 'title', 'published_at', 'views_count', 'created_at'];

        if (! in_array($coluna, $permitidas, true)) {
            return;
        }

        if ($this->ordenar === $coluna) {
            $this->direccao = $this->direccao === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->ordenar = $coluna;
        $this->direccao = $coluna === 'title' ? 'asc' : 'desc';
    }

    /* ---------------------------------------------------------------- */
    /* Ações de linha (Gate news.manage em todas, defesa em profundidade) */
    /* ---------------------------------------------------------------- */

    public function editar(int $id): void
    {
        Gate::authorize('news.manage');

        $this->redirectRoute('admin.noticia-editar', ['noticia' => $id]);
    }

    /**
     * Pré-visualização pública: só disponível para notícias publicadas.
     * Rascunhos/em revisão não existem no frontend público.
     */
    public function ver(int $id): void
    {
        Gate::authorize('news.manage');

        $noticia = $this->procurar($id, onlyTrashed: $this->mostrarApagados);

        if ($noticia === null) {
            return;
        }

        if ($noticia->status !== 'published') {
            $this->dispatch('toast', mensagem: 'A pré-visualização pública só existe depois de publicar.', tipo: 'error');

            return;
        }

        $this->redirect(route('news.show', $noticia->slug));
    }

    /** Duplicar: clona como rascunho com slug único e título «(cópia)». */
    public function duplicar(int $id): void
    {
        Gate::authorize('news.manage');

        $original = News::findOrFail($id);

        $copia = $original->replicate();
        $copia->title = rtrim($original->title) . ' (cópia)';
        $copia->slug = $this->slugUnico($copia->title);
        $copia->status = 'draft';
        $copia->published_at = null;
        $copia->date_label = null;
        $copia->views_count = 0;
        $copia->author_id = auth()->id();
        $copia->save();

        $this->dispatch('toast', mensagem: 'Rascunho "' . $copia->title . '" criado a partir de "' . $original->title . '".', tipo: 'success');
    }

    /** Eliminar: soft delete (abre o #confirm-modal via JS na vista). */
    public function eliminar(int $id): void
    {
        Gate::authorize('news.manage');

        $noticia = News::findOrFail($id);
        $noticia->delete();

        $this->dispatch('toast', mensagem: 'Notícia movida para o lixo.', tipo: 'success');
    }

    /** Restaurar: devolve do lixo (SoftDeletes). */
    public function restaurar(int $id): void
    {
        Gate::authorize('news.manage');

        $noticia = News::onlyTrashed()->findOrFail($id);
        $noticia->restore();

        $this->dispatch('toast', mensagem: 'Notícia restaurada.', tipo: 'success');
    }

    /** Limpar busca + filtros + lixo (botão do estado vazio). */
    public function atualizarTabela(): void
    {
        $this->busca = '';
        $this->filtros = ['estado' => '', 'categoria' => '', 'autor' => ''];
        $this->mostrarApagados = false;
        $this->resetPage();
    }

    /** Paginação em ziguezague: página 1, p. 2, ... mais a anterior/seguinte. */
    public function gotoPage(int $pagina): void
    {
        $this->setPage($pagina, 'page');
    }

    private function procurar(int $id, bool $onlyTrashed = false): ?News
    {
        return $onlyTrashed
            ? News::onlyTrashed()->find($id)
            : News::find($id);
    }

    private function slugUnico(string $titulo): string
    {
        $base = Str::slug($titulo);
        $candidato = $base !== '' ? $base : 'noticia';
        $n = 1;

        while (News::query()->withTrashed()->where('slug', $candidato)->exists()) {
            $candidato = $base . '-' . $n++;
        }

        return $candidato;
    }

    public function render()
    {
        $query = News::query()
            ->with(['category', 'author']);

        if ($this->mostrarApagados) {
            $query = News::onlyTrashed()->with(['category', 'author']);
        }

        foreach ($this->filtros as $nome => $valor) {
            if ($valor === '') {
                continue;
            }

            if ($nome === 'categoria') {
                $query->where('category_id', $valor);
            } elseif ($nome === 'autor') {
                $query->where('author_id', $valor);
            } elseif ($nome === 'estado') {
                $query->where('status', $valor);
            }
        }

        if ($this->busca !== '') {
            $query->where('title', 'like', '%' . $this->busca . '%');
        }

        $ordenar = in_array($this->ordenar, ['id', 'title', 'published_at', 'views_count', 'created_at'], true) ? $this->ordenar : 'id';

        $registos = $query
            ->orderBy($ordenar, $this->direccao === 'asc' ? 'asc' : 'desc')
            ->orderByDesc('id')
            ->paginate(8);

        $autores = User::query()
            ->whereIn('id', News::query()->withTrashed()->select('author_id')->whereNotNull('author_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $mapa = CategoriasNews::mapa();

        return view('livewire.admin.news-list-table', [
            'registos' => $registos,
            'categoriasTop' => $mapa['tops'],
            'categoriasFilhos' => $mapa['filhos'],
            'autores' => $autores,
        ]);
    }
}
