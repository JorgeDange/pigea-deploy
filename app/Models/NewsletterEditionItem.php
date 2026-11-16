<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log por destinatário de uma edição da newsletter (Fase N2).
 * Uma linha por email por edição — único(edition_id, email).
 * `estado`: sent | failed.
 */
class NewsletterEditionItem extends Model
{
    protected $table = 'newsletter_edition_items';

    protected $fillable = ['edition_id', 'email', 'estado'];

    protected $casts = [];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(NewsletterEdition::class, 'edition_id');
    }
}
