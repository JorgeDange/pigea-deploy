<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Collection;

/**
 * Árvore de categorias de notícias para selects (Fase 11 / cms_v.md).
 *
 * Categorias de notícias de tipo `type=news`:
 *  - de topo (sem pai) e sem filhos  → `<option>` direto;
 *  - com filhos                     → `<optgroup label="nome">` contendo
 *                                      opção «nome» + as subcategorias;
 *  - «Outros» (final da lista)      → captura genérica quando nada encaixa.
 */
class CategoriasNews
{
    /**
     * @return array{tops: Collection<int, Category>, filhos: Collection<int, Collection<int, Category>>, topsComFilhos: array<int, int>}
     */
    public static function mapa(): array
    {
        $tops = Category::where('type', 'news')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $filhos = Category::where('type', 'news')
            ->whereNotNull('parent_id')
            ->orderBy('name')
            ->get()
            ->groupBy('parent_id');

        return [
            'tops' => $tops,
            'filhos' => $filhos,
            'topsComFilhos' => array_keys($filhos->toArray()),
        ];
    }
}
