<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Edição da Newsletter (Fase N2).
 *
 * `estado`: pending → a enviar (enquanto não esgota os destinatários) /
 * done → enviados+falhados == total; failed → qualquer coisa rebentou.
 */
class NewsletterEdition extends Model
{
    protected $table = 'newsletter_editions';

    protected $fillable = ['article_id', 'total', 'enviados', 'falhados', 'estado', 'criado_por', 'finished_at'];

    protected $casts = [
        'finished_at' => 'datetime',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(News::class, 'article_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function items(): HasMany
    {
        return $this->hasMany(NewsletterEditionItem::class, 'edition_id');
    }

    /** Ficam ainda subscritoresativos por enviar (total − já registados). */
    public function pendentes(): int
    {
        return max(0, $this->total - $this->enviados - $this->falhados);
    }

    public function estaCompleta(): bool
    {
        return ($this->enviados + $this->falhados) >= $this->total;
    }
}
