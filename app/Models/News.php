<?php

namespace App\Models;

use App\Models\Concerns\Contentful;
use App\Models\Concerns\SearchesContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Notícia (restrutura.md §3.4).
 *
 * Único modelo com Spatie Media Library (restrutura.md §7.5):
 *  - coleção `capa` — imagem principal (uma por notícia);
 *  - coleção `contexto` — imagens de contexto do corpo;
 *  - conversão WebP nas duas coleções.
 *
 * Vídeo e documento são APENAS LINK (`video_link`, `documento_link` —
 * restrutura.md §1). A coluna legada `image` mantém-se enquanto as vistas
 * usam o campo antigo; a migração para `capa` acontece nas Fases 4-5.
 */
class News extends Model implements HasMedia
{
    use Contentful, HasFactory, InteractsWithMedia, SearchesContent, SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'category_id', 'author_id', 'guest_author_name', 'guest_author_avatar', 'title', 'slug', 'excerpt', 'content',
        'image', 'image_alt', 'image_caption', 'status', 'published_at', 'date_label',

        'province',

        // Campos de `admin/noticia-nova.html` (tags + SEO).
        'tags', 'meta_title', 'meta_description',

        // Links externos — vídeo e documento são só link (restrutura.md §3.4);
        // link genérico acrescentado na Fase 11 (cms_v.md).
        'video_link', 'documento_link', 'link',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        // Imagem principal — uma (a última submetida substitui a anterior).
        $this->addMediaCollection('capa')->singleFile();

        // Galeria de imagens de contexto do corpo do artigo.
        $this->addMediaCollection('contexto');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Conversão WebP (restrutura.md §3.4). `nonQueued` porque não há
        // worker de filas no servidor atual — a conversão corre no request.
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(85)
            ->performOnCollections('capa', 'contexto')
            ->nonQueued();
    }
}
