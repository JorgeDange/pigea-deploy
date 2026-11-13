<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Destaque publicitário rotativo do destaque da home e das sidebars
 * (Fase 15 — pub.md). Mesmas regras de exposição do Banner:
 * ativo + dentro de data_inicio..data_fim (nulos = sem limite).
 *
 * Escopo por colocação do widget «Escolha da Equipa»:
 *  - site    → home e páginas interiores (ex.: página de projeto)
 *  - inicio  → só a página inicial
 *  - sidebar → só páginas interiores (ex.: página de projeto)
 */
class Destaque extends Model
{
    use SoftDeletes;

    protected $table = 'destaques';

    protected $fillable = [
        'titulo',
        'imagem_url',
        'link_destino',
        'escopo',
        'data_inicio',
        'data_fim',
        'ativo',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'ativo' => 'boolean',
            'ordem' => 'integer',
        ];
    }

    /** Destaques exibíveis hoje: ativos e dentro do período. */
    public function scopeAtivos(Builder $query): Builder
    {
        $hoje = now()->toDateString();

        return $query
            ->where('ativo', true)
            ->where(fn (Builder $q) => $q->whereNull('data_inicio')->orWhere('data_inicio', '<=', $hoje))
            ->where(fn (Builder $q) => $q->whereNull('data_fim')->orWhere('data_fim', '>=', $hoje));
    }

    /** Destaques visíveis numa colocação ('site' entra em todas). */
    public function scopeEscopo(Builder $query, string $contexto): Builder
    {
        return $query->where(fn (Builder $q) => $q->where('escopo', 'site')->orWhere('escopo', $contexto));
    }

    /** Escopos válidos — usados na validação do admin e nos selects. */
    public const ESCOPOS = [
        'site' => 'Todo o site (home e páginas interiores)',
        'inicio' => 'Só página inicial',
        'sidebar' => 'Só páginas interiores (ex.: página de projeto)',
    ];
}
