<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Video extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'titulo',
        'resumo',
        'url',
        'imagem',
        'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    /** Mais recentes primeiro — a ordem de exibição no site. */
    public function scopeRecency(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /** Miniatura — imagem própria, senão thumbnail do YouTube, senão null. */
    public function thumb(): ?string
    {
        if ($this->imagem) {
            return $this->imagem;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $this->url, $m)) {
            return 'https://img.youtube.com/vi/' . $m[1] . '/maxresdefault.jpg';
        }

        return null;
    }
}
