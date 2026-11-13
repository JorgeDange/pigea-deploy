<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 6 (cms_v.md) — texto alternativo da imagem de capa.
 *
 * `image_caption` (crédito/legenda) já existe desde a criação das tabelas;
 * o texto alternativo (a11y) é o que faltava. Sem perda de dados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('image_alt')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('image_alt');
        });
    }
};
