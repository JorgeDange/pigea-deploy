<?php

namespace App\Models;

use App\Models\Concerns\Contentful;
use App\Models\Concerns\SearchesContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Research extends Model
{
    use Contentful, HasFactory, SearchesContent, SoftDeletes;

    protected $table = 'research';

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt', 'content',
        'image', 'image_caption', 'status', 'published_at', 'date_label',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'views_count' => 'integer',
        ];
    }
}
