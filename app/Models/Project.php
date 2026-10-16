<?php

namespace App\Models;

use App\Models\Concerns\Contentful;
use App\Models\Concerns\SearchesContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use Contentful, HasFactory, SearchesContent, SoftDeletes;

    protected $table = 'projects';

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt', 'content',
        'image', 'image_caption', 'status', 'published_at', 'date_label',
        'comments_count', 'views_count',

        // Campos de `admin/projeto-novo.html` (mineral, fase, operador, área)
        // e uploads múltiplos guardados sem exibição (o proto não mostra galeria).
        'province', 'mineral', 'phase', 'operator', 'area', 'documents', 'images',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'comments_count' => 'integer',
            'views_count' => 'integer',
            'area' => 'decimal:2',
            'documents' => 'array',
            'images' => 'array',
        ];
    }
}