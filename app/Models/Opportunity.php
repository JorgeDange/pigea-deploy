<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Oportunidade de negócio da empresa (`painel-empresa-oportunidades.html`).
 * Tipos do protótipo: Parceria / Fornecimento / Joint venture. Estados do
 * protótipo: Ativa (`ativa`), Em análise (`pendente`), Expirada (`expirada`).
 */
class Opportunity extends Model
{
    protected $fillable = ['user_id', 'title', 'type', 'deadline', 'status'];

    public const BADGES = [
        'ativa' => ['active', 'Ativa'],
        'pendente' => ['pending', 'Em análise'],
        'expirada' => ['rejected', 'Expirada'],
    ];

    protected function casts(): array
    {
        return ['deadline' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
