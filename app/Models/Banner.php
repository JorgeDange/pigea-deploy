<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Banner publicitário (restrutura.md §3.5).
 *
 * Exibido nas posições `topo`/`sidebar`/`rodape`/`lateral` (FASE 18: slot
 * vertical na sidebar — largura da coluna, enquadramento 1:1) enquanto
 * `ativo` e dentro
 * do período `data_inicio`..`data_fim` (limites opcionais = sempre válido
 * nesse limite). O frontend (Fase 6) substitui o `banner-placeholder` do
 * header pelo banner da posição `topo`. SoftDeletes: a eliminação no
 * painel é reversível (mesma regra das tabelas de conteúdo).
 */
class Banner extends Model
{
    use SoftDeletes;
    protected $table = 'banners';

    protected $fillable = [
        'titulo', 'imagem_url', 'link_destino', 'posicao', 'data_inicio', 'data_fim', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'ativo' => 'boolean',
        ];
    }

    /**
     * Banners exibíveis hoje: ativos e dentro do período (quando definido).
     */
    public function scopeAtivos(Builder $query): Builder
    {
        $hoje = now()->toDateString();

        return $query
            ->where('ativo', true)
            ->where(fn (Builder $q) => $q->whereNull('data_inicio')->orWhere('data_inicio', '<=', $hoje))
            ->where(fn (Builder $q) => $q->whereNull('data_fim')->orWhere('data_fim', '>=', $hoje));
    }

    public function scopePosicao(Builder $query, string $posicao): Builder
    {
        return $query->where('posicao', $posicao);
    }
}
