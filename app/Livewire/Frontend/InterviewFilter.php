<?php

namespace App\Livewire\Frontend;

use App\Models\Interview;
use Livewire\Component;

/**
 * Mesmo seletor de ordenação das notícias, na página de entrevistas.
 * Ver `NewsFilter` — mesmas regras (ordem do protótipo até o utilizador escolher).
 */
class InterviewFilter extends Component
{
    public string $ordem = 'Mais Recentes';

    public bool $escolheu = false;

    public function updatingOrdem(): void
    {
        $this->escolheu = true;
    }

    public function render()
    {
        $items = Interview::published()
            ->with(['category', 'author'])
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

        return view('livewire.frontend.interview-filter', ['items' => $items]);
    }
}
