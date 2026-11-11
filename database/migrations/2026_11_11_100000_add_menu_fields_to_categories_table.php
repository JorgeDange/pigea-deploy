<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 5.1 — Mega Menu (menu.md §4): colunas de estrutura do menu na tabela
 * `categories`.
 *
 *  - `parent_id`: pai da subcategoria (as subcategorias de notícias —
 *    Mineração / Petróleo e Gás / Geociências — apontam para a categoria
 *    de notícias do grupo; as de navegação ficam sem pai);
 *  - `menu_group`: grupo do Mega Menu ('inicio', 'noticias', 'mineracao',
 *    'petroleo-e-gas', 'geociencias', 'academia', 'oportunidades',
 *    'directorio', 'sobre') — NULL nas categorias de conteúdo normais;
 *  - `order`: ordem do link na grelha do painel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('menu_group')->nullable()->index()->after('type');
            $table->unsignedInteger('order')->nullable()->after('menu_group');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropIndex(['menu_group']);
            $table->dropColumn(['menu_group', 'order']);
        });
    }
};
