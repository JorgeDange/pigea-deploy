<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 14 (cms_v.md) — promover Academia e Oportunidades a `type='news'`.
 *
 * Os 10 filhos dos grupos (type='menu') passam a `type='news'` para ficarem
 * associáveis aos posts de notícias:
 *  - slug da academia "Investigacao" renomeado para "investigacao-academia"
 *    (slug "investigacao" já existe em type='news');
 *  - inserted 2 top-level type=news ('Academia'/'Oportunidades') como pais,
 *    com `menu_group=NULL` — taxonomia de notícias, não itens do Mega Menu;
 *  - os 10 filhos assumem parent_id do top-level correspondente e passam
 *    a `type='news'`; `menu_group` dos filhos fica intacto (URLs públicas
 *    mantidas via DESTINOS — a promoção não lhes muda o destino público).
 *
 * Aditivo e reversível. Idempotente: pode correr em cima de estados
 * parcialmente migrados (resultado de uma tentativa anterior).
 */
return new class extends Migration
{
    public function up(): void
    {
        $agora = now();

        // 1. Renomear o slug colidir de academia para um slug único
        DB::table('categories')
            ->where('menu_group', 'academia')
            ->where('slug', 'investigacao')
            ->update(['slug' => 'investigacao-academia', 'updated_at' => $agora]);

        // 2. Top-level Academia (news) — idempotente.
        //    menu_group=NULL: é taxonomia de notícias, NÃO um item do
        //    Mega Menu (o Mega Menu mostra só as 44 subcategorias do cliente).
        $academiaId = DB::table('categories')->where('type', 'news')->whereNull('parent_id')->where('slug', 'academia')->value('id');
        if ($academiaId === null) {
            $academiaId = DB::table('categories')->insertGetId([
                'name' => 'Academia',
                'slug' => 'academia',
                'type' => 'news',
                'parent_id' => null,
                'menu_group' => null,
                'order' => 9,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        } else {
            DB::table('categories')->where('id', $academiaId)->update([
                'type' => 'news',
                'parent_id' => null,
                'menu_group' => null,
                'order' => 9,
                'updated_at' => $agora,
            ]);
        }

        // 3. Top-level Oportunidades (news) — idempotente. menu_group=NULL (ver acima).
        $oportunidadesId = DB::table('categories')->where('type', 'news')->whereNull('parent_id')->where('slug', 'oportunidades')->value('id');
        if ($oportunidadesId === null) {
            $oportunidadesId = DB::table('categories')->insertGetId([
                'name' => 'Oportunidades',
                'slug' => 'oportunidades',
                'type' => 'news',
                'parent_id' => null,
                'menu_group' => null,
                'order' => 10,
                'created_at' => $agora,
                'updated_at' => $agora,
            ]);
        } else {
            DB::table('categories')->where('id', $oportunidadesId)->update([
                'type' => 'news',
                'parent_id' => null,
                'menu_group' => null,
                'order' => 10,
                'updated_at' => $agora,
            ]);
        }

        // 4. Filhos de academia → news
        DB::table('categories')
            ->where('menu_group', 'academia')
            ->where('id', '!=', $academiaId)
            ->whereNull('parent_id')
            ->update([
                'type' => 'news',
                'parent_id' => $academiaId,
                'updated_at' => $agora,
            ]);

        // 5. Filhos de oportunidades → news
        DB::table('categories')
            ->where('menu_group', 'oportunidades')
            ->where('id', '!=', $oportunidadesId)
            ->whereNull('parent_id')
            ->update([
                'type' => 'news',
                'parent_id' => $oportunidadesId,
                'updated_at' => $agora,
            ]);
    }

    public function down(): void
    {
        // Filhos (10) voltam a type='menu' sem pai.
        DB::table('categories')->where('menu_group', 'academia')->whereNotNull('parent_id')->update(['type' => 'menu', 'parent_id' => null]);
        DB::table('categories')->where('menu_group', 'oportunidades')->whereNotNull('parent_id')->update(['type' => 'menu', 'parent_id' => null]);

        // Top-levels criados: localizar por type=news + slug (sem menu_group).
        DB::table('categories')->where('type', 'news')->whereNull('parent_id')->whereIn('slug', ['academia', 'oportunidades'])->delete();
    }
};
