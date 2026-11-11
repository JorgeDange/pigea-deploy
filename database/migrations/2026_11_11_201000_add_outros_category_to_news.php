<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Fase 11 (cms_v.md) — categoria «Outros» para notícias sem categoria.
 *
 * Os 9 grupos + 44 subcategorias do menu não cobrem todos os artigos;
 * sem uma captura genérica o redator fica preso. A opção «Outros» aparece
 * no fim do select do editor (e do filtro da lista).
 *
 * Idempotente — pode correr de novo sem duplicar.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        DB::table('categories')->insertOrIgnore([
            [
                'name' => 'Outros',
                'slug' => 'outros',
                'type' => 'news',
                'parent_id' => null,
                'menu_group' => null,
                'order' => 999,
                'created_at' => $agora,
                'updated_at' => $agora,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('categories')
            ->where('type', 'news')
            ->where('slug', 'outros')
            ->delete();
    }
};
