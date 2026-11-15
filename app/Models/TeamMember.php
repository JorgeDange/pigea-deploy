<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Membro da Equipa PIGEA — secção "Sobre Nós" (/equipa).
 *
 * Organizado por secção hierárquica (`cargo_select`, ver Institucional::SECCOES_EQUIPA);
 * `cargo_outro` só existe quando `cargo_select = 'outros'`. Sem login — apenas
 * conteúdo público renderizado por vistas; gestão no painel admin.
 */
class TeamMember extends Model
{
    use SoftDeletes;

    protected $table = 'team_members';

    protected $fillable = [
        'nome', 'cargo_select', 'cargo_outro', 'foto_url',
        'facebook', 'instagram', 'linkedin', 'ordem', 'ativo',
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
