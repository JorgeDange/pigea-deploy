<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Decisão do cliente (17/11/2026): «Inovação e tecnologia» e «Eventos»
 * passam a `type='news'` — ficam editáveis como categorias de artigo
 * (sem grupo; `parent_id` continua NULL; `menu_group` continua 'noticias'
 * para continuarem visíveis no Mega Menu).
 *
 * O Mega Menu continua a linkar para /tecnologias e /eventos (DESTINOS)
 * porque as duas páginas continuam a existir e valem a pena.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('categories')
            ->whereIn('slug', ['inovacao-e-tecnologia', 'eventos'])
            ->where('menu_group', 'noticias')
            ->update(['type' => 'news', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('categories')
            ->whereIn('slug', ['inovacao-e-tecnologia', 'eventos'])
            ->where('menu_group', 'noticias')
            ->update(['type' => 'menu', 'updated_at' => now()]);
    }
};
