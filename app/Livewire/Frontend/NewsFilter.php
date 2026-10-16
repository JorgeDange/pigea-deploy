<?php

namespace App\Livewire\Frontend;

use App\Models\News;
use Livewire\Component;

/**
 * Seletor "Mais Recentes | Mais Lidos | Mais Vistos | Mais Comentados"
 * das notícias (fase.md §2.2 — "filtros visuais → filtros funcionais (Livewire)").
 *
 * O valor é o texto da opção (o protótipo não tem `value=`), por isso nada
 * muda no HTML para além de `wire:model`/`wire:ignore`.
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

    public function mount(): void
    {
        $this->cat = (string) request()->query('cat', '');
    }

    public function updatingOrdem(): void
    {
        $this->escolheu = true;
    }

    public function render()
    {
        $items = News::published()
            ->with(['category', 'author'])
            ->when($this->cat !== '', fn ($query) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $this->cat)
            ))
            ->tap(function ($query) {
                if (! $this->escolheu) {
                    return $query->orderBy('id');
                }

                return match ($this->ordem) {
                    'Mais Lidos', 'Mais Vistos' => $query->orderByDesc('views_count')->orderByDesc('id'),
                    'Mais Comentados' => $query->orderByDesc('comments_count')->orderByDesc('id'),
                    default => $query->orderByDesc('published_at')->orderByDesc('id'),
                };
            })
            ->paginate(8)
            ->withQueryString();

        return view('livewire.frontend.news-filter', ['items' => $items]);
    }
}
