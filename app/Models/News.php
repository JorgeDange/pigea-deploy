<?php

namespace App\Models;

use App\Models\Concerns\Contentful;
use App\Models\Concerns\SearchesContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use Contentful, HasFactory, SearchesContent, SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt', 'content',
        'image', 'image_caption', 'status', 'published_at', 'date_label',
        'comments_count', 'views_count',

        'province',

        // Campos de `admin/noticia-nova.html` (tags + SEO).
        'tags', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'comments_count' => 'integer',
            'views_count' => 'integer',
        ];
    }
}