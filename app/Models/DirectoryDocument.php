<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Artigo/Documento do Diretório (restrutura.md §3.3).
 *
 * Carregado EXCLUSIVAMENTE pela equipa interna no /admin (Fase 4); no
 * público (aba "Artigos/Documentos" de /directorio) apresenta botão
 * "Download" que abre `link_download`. Sem status — tudo o que está
 * aqui é conteúdo aprovado pela equipa. SoftDeletes: a eliminação no
 * painel é reversível (mesma regra das tabelas de conteúdo).
 */
class DirectoryDocument extends Model
{
    use SoftDeletes;
    protected $table = 'directory_documents';

    protected $fillable = [
        'titulo', 'descricao', 'tipo', 'link_download', 'areas_atuacao',
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

    /** Rótulo legível do tipo — usado nas tabelas admin e na listagem pública. */
    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            'artigo' => 'Artigo',
            'documento' => 'Documento',
            'lei' => 'Lei',
            default => ucfirst($this->tipo ?? ''),
        };
    }

    public function scopePorArea(Builder $query, string $area): Builder
    {
        return $query->whereJsonContains('areas_atuacao', $area);
    }
}
