<?php

namespace App\Models\Concerns;

use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

/**
 * Pesquisa funcional de todo o conteúdo público (fase.md §2.2 — "pesquisa
 * visual → pesquisa funcional": Scout + MySQL Full-Text).
 *
 * Todas as tabelas de conteúdo têm o mesmo esquema (`title`, `excerpt`,
 * `content`, `slug`), pelo que basta esta trait. O driver `database` do Scout
 * consulta as tabelas a cada pesquisa — não há índices de procura para
 * sincronizar (`scout:import` é desnecessário).
 */
trait SearchesContent
{
    use Searchable;

    /**
     * `title`/`excerpt` entram no índice Full-Text (MySQL `MATCH ... AGAINST`);
     * `content` e `slug` são procurados com `LIKE`.
     */
    #[SearchUsingFullText(['title', 'excerpt'])]
    public function toSearchableArray(): array
    {
        return [
            'title' => (string) $this->title,
            'excerpt' => (string) $this->excerpt,
            'content' => (string) $this->content,
            'slug' => (string) $this->slug,
        ];
    }
}
