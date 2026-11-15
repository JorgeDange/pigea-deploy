<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Subscritor da Newsletter do PIGEA (doc/newsletter.md §4).
 *
 * `status`:
 *  - pending:   criou a inscrição; o opt-in de confirmação está pendente;
 *  - active:    confirmou por email — única lista para envio;
 *  - cancelled: cancelou pelo link único ou foi removido no painel.
 *
 * Três escopos úteis: active(), pending(), cancelados().
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCancelados($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function emailNormalizado(): string
    {
        return mb_strtolower(trim((string) $this->email));
    }
}
