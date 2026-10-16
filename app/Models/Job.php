<?php

namespace App\Models;

use App\Models\Concerns\Contentful;
use App\Models\Concerns\SearchesContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use Contentful, HasFactory, SearchesContent, SoftDeletes;

    protected $table = 'vacancies';

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt', 'content',
        'image', 'image_caption', 'status', 'published_at', 'date_label',
        'comments_count', 'views_count',

        // Campos do formulário `painel-empresa-vaga-nova.html` (painel empresa).
        'contract_type', 'province', 'deadline', 'requirements', 'responsibilities',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'comments_count' => 'integer',
            'views_count' => 'integer',
            'deadline' => 'date',
        ];
    }

    /** Candidaturas a esta vaga (`applications`). */
    public function applications()
    {
        return $this->hasMany(Application::class, 'job_id');
    }
}