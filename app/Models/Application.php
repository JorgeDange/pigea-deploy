<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Candidatura a uma vaga (`applications`).
 *
 * `user_id` NULL = visitante sem sessão (o formulário público de `empregos-single`
 * pede nome + email); autenticado = conta do painel do utilizador.
 * Estados: `em-analise` → `aceite` → `rejeitada` (empresa decide nas
 * `candidaturas recebidas`).
 */
class Application extends Model
{
    protected $fillable = [
        'user_id', 'job_id', 'name', 'email', 'cv_url', 'message', 'status',
    ];

    /** Estados → [classe do badge, rótulo] (textos do protótipo). */
    public const BADGES = [
        'em-analise' => ['pending', 'Em análise'],
        'aceite' => ['active', 'Aceite'],
        'rejeitada' => ['rejected', 'Rejeitada'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        // A tabela do módulo de vagas é `vacancies` (modelo `Job`).
        return $this->belongsTo(Job::class, 'job_id');
    }
}
