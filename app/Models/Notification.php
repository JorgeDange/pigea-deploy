<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notificação de painel (`notifications` próprias — não as do Laravel).
 *
 * O protótipo põe um ícone diferente em cada item (fa-paper-plane, fa-check…),
 * por isso o ícone é gravado com o texto.
 */
class Notification extends Model
{
    protected $fillable = ['user_id', 'icon', 'title', 'text', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
