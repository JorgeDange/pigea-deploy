<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * FASE 5.1 (menu.md §4): `parent_id`/`menu_group`/`order` estruturam as
     * subcategorias do Mega Menu. `menu_group` NULL = categoria de conteúdo
     * normal (sem painel no menu); as rows de menu usam `type` 'news'
     * (subcategoria filtrável em /noticias?cat=slug) ou 'menu' (link de
     * navegação, inerte para os selects de conteúdo do admin).
     */
    protected $fillable = [
        'name', 'slug', 'type', 'description', 'parent_id', 'menu_group', 'order',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'order' => 'integer',
        ];
    }
}
