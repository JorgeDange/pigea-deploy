<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * Empresa do Diretório (restrutura.md §3.1).
 *
 * Sem login: a empresa submete o formulário público (/registo-empresa,
 * Fase 3) e fica `pendente` até aprovação da equipa interna no /admin
 * (Fase 4). Na página pública só aparecem as `aprovado` e SEM o NIF
 * (restrutura.md §6).
 */
class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'nome_empresa', 'nif', 'contactos', 'areas_atuacao', 'redes_sociais', 'status',
    ];

    protected function casts(): array
    {
        return [
            'areas_atuacao' => 'array',
            'redes_sociais' => 'array',
        ];
    }

    /**
     * Grava o JSON com acentos literais (JSON_UNESCAPED_UNICODE) — FASE 7:
     * o json_encode default escapava ("Petr\u00f3leo…") e a busca por área
     * do diretório público nunca batia nos registos novos.
     */
    protected function areasAtuacao(): Attribute
    {
        return Attribute::set(
            static fn ($valor) => json_encode($valor ?? [], JSON_UNESCAPED_UNICODE)
        );
    }

    protected function redesSociais(): Attribute
    {
        return Attribute::set(
            static fn ($valor) => $valor === null ? null : json_encode($valor, JSON_UNESCAPED_UNICODE)
        );
    }

    /** Aprovadas — as únicas visíveis no público. */
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
