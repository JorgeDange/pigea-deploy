<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * autor.md §1 — autores convidados (sem conta no sistema) no corpo da notícia.
 *
 * users.avatar já existe (2026_09_28_220003); news.author_id já existe
 * (2026_09_28_220002) — aqui apenas as colunas do convidado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('guest_author_name')->nullable()->after('author_id');
            $table->string('guest_author_avatar')->nullable()->after('guest_author_name');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['guest_author_name', 'guest_author_avatar']);
        });
    }
};
