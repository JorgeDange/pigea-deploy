<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE E1 — o `province` passa a aceitar texto livre («Outros») com até
 * 100 caracteres (validação do pedido). A coluna de `projects` tinha
 * VARCHAR(64), o que cortaria ou daria erro em textos longos. Alargamos
 * para VARCHAR(120) (igual ao `news.province`). Não toca em dados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('province', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('province', 64)->nullable()->change();
        });
    }
};
