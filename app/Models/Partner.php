<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Parceiro do PIGEA — secção "Sobre Nós" (/parceiros).
 *
 * `tipo` define em que das duas secções aparece (Empresas / Profissionais);
 * `imagem_url` é URL única (logo ou foto). Gestão no painel admin.
 */
class Partner extends Model
{
    use SoftDeletes;

    protected $table = 'partners';

    protected $fillable = [
        'nome', 'tipo', 'imagem_url', 'detalhes', 'ordem', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'ordem' => 'integer',
        ];
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('ordem')->orderBy('id');
    }
}
