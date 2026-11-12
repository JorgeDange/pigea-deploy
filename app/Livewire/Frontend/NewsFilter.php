<?php

namespace App\Livewire\Frontend;

use App\Models\Category;
use App\Models\News;
use Livewire\Component;

/**
 * Seletor "Mais Recentes | Mais Lidos | Mais Vistos | Mais Comentados"
 * das notícias (fase.md §2.2 — "filtros visuais → filtros funcionais (Livewire)").
 *
 * O valor é o texto da opção (o protótipo não tem `value=`), por isso nada
 * muda no HTML para além de `wire:model`/`wire:ignore`.
 *
 * Filtro `?cat=` (links do menu principal e do aside-panel):
 *  - Whitelist: só categorias `type='news'` são aceites como filtro.
 *    Slugs desconhecidos/outros tipos devolvem resultado vazio controlado.
 *  - Expansão hierárquica: o filtro aceita um topo (`academia`) e inclui
 *    as notícias etiquetadas com qualquer filho (`cursos-e-formacao`, …).
 */
class NewsFilter extends Component
{
    public string $ordem = 'Mais Recentes';

    /**
     * Falso até o utilizador tocar no seletor: a primeira carga mantém a ordem
     * do protótipo (`id`), que é a ordem dos itens no HTML original.
     */
    public bool $escolheu = false;

    /** Categoria vinda de `?cat=` (links do menu principal e do aside-panel). */
    public string $cat = '';

    /** Nome da categoria ativa (null = lista completa). */
    public ?string $catNome = null;

    public function mount(): void
    {
        $this->cat = (string) request()->query('cat', '');

        if ($this->cat === '') {
            return;
        }

        // Whitelist: só type=news é aceite
        $categoria = Category::where('type', 'news')
            ->where('slug', $this->cat)
            ->first();

        if ($categoria === null) {
            $this->cat = ''; // slug inválido/outro tipo — sem filtro
            $this->catNome = null;

            return;
        }

        $this->catNome = $categoria->name;
    }

    public function updatingOrdem(): void
    {
        $this->escolheu = true;
    }

    public function render()
    {
        $itens = News::published()
            ->with(['category', 'author'])
            ->when($this->cat !== '', function ($query) {
                // Expansão hierárquica: aceita id da categoria pedida + todos os filhos
                $categoria = Category::where('type', 'news')
                    ->where('slug', $this->cat)
                    ->first();

                if ($categoria === null) {
                    return $query->whereRaw('1 = 0'); // sem resultados — defesa em profundidade
                }

                $ids = Category::query()
                    ->where('parent_id', $categoria->id)
                    ->where('type', 'news')
                    ->pluck('id')
                    ->push($categoria->id);

                return $query->whereHas('category', fn ($q) => $q->whereIn('id', $ids));
            })
            ->tap(function ($query) {
                if (! $this->escolheu) {
                    return $query->orderBy('id');
                }

                return match ($this->ordem) {
                    'Mais Lidos', 'Mais Vistos' => $query->orderByDesc('views_count')->orderByDesc('id'),
                    default => $query->orderByDesc('published_at')->orderByDesc('id'),
                };
            })
            ->paginate(8)
            ->withQueryString();

        return view('livewire.frontend.news-filter', ['items' => $itens]);
    }
}
