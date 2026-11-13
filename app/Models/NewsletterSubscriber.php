<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Subscritor da Newsletter do PIGEA (doc/newsletter.md §4).
 *
 * `status`:
 *  - pending:     criou a inscrição; o opt-in de confirmação está pendente;
 *  - confirmed:   confirmou por email — única lista para envio (FASE N2);
 *  - unsubscribed: cancelou pelo link único ou foi removido no painel.
 *
 * Três escopos úteis: confirmed(), pending(), cancelados().
 */
class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';

    protected $fillable = [
        'email',
        'token',
        'status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'confirmed_at' => 'datetime',
        ];
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'unsubscribed');
    }

    public function emailNormalizado(): string
    {
        return mb_strtolower(trim((string) $this->email));
    }
}
