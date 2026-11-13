<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Profissional do Diretório (restrutura.md §3.2).
 *
 * Sem login: submissão pública (/registo-profissional, Fase 3) e aprovação
 * da equipa interna no /admin (Fase 4). Público só mostra `aprovado`.
 */
class Professional extends Model
{
    protected $table = 'professionals';

    protected $fillable = [
        'nome_completo', 'formacao_academica', 'contactos', 'areas_atuacao', 'status',
    ];

    protected function casts(): array
    {
        return [
            'areas_atuacao' => 'array',
        ];
    }

    /** Grava o JSON com acentos literais — ver Company::areasAtuacao (FASE 7). */
    protected function areasAtuacao(): Attribute
    {
        return Attribute::set(
            static fn ($valor) => json_encode($valor ?? [], JSON_UNESCAPED_UNICODE)
        );
    }

    /** Aprovados — os únicos visíveis no público. */
    public function scopeAprovado(Builder $query): Builder
    {
        return $query->where('status', 'aprovado');
    }

    public function scopePendente(Builder $query): Builder
    {
        return $query->where('status', 'pendente');
    }

    public function scopeRejeitado(Builder $query): Builder
    {
        return $query->where('status', 'rejeitado');
    }
}
