<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remove a subcategoria de navegação «Missão, visão e valores» do mega menu
 * (grupo `sobre`) — pedido do cliente 2026-11-15.
 *
 * O item apontava para a mesma página (/sobre) que «Quem somos»: redundância
 * que o cliente pediu eliminar. Nenhum artigo estava associado a esta
 * subcategoria (verificado: `news` = 0 referências). Idempotente.
 *
 * Também renumera o grupo `sobre` para 1..N — o item removido era o 2º, e o
 * MegaMenuTest exige `order` sequencial dentro de cada grupo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')
            ->where('menu_group', 'sobre')
            ->where('slug', 'missao-visao-e-valores')
            ->delete();

        // Renumerar o grupo para 1..N pela ordem atual (o item 2 desapareceu).
        $ids = array_values(
            DB::table('categories')->where('menu_group', 'sobre')->orderBy('order')->pluck('id')->all()
        );

        foreach ($ids as $i => $id) {
            DB::table('categories')->where('id', $id)->update(['order' => $i + 1, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        $existe = DB::table('categories')
            ->where('menu_group', 'sobre')
            ->where('slug', 'missao-visao-e-valores')
            ->exists();

        if (! $existe) {
            DB::table('categories')->insert([
                'name' => 'Missão, visão e valores',
                'slug' => 'missao-visao-e-valores',
                'type' => 'menu',
                'parent_id' => null,
                'menu_group' => 'sobre',
                'order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Renumerar na descida também — o item volta a ocupar a posição 2.
        $ids = array_values(
            DB::table('categories')->where('menu_group', 'sobre')->orderByRaw("CASE WHEN slug = 'missao-visao-e-valores' THEN 2 ELSE `order` END ASC")->pluck('id')->all()
        );

        foreach ($ids as $i => $id) {
            DB::table('categories')->where('id', $id)->update(['order' => $i + 1, 'updated_at' => now()]);
        }
    }
};
