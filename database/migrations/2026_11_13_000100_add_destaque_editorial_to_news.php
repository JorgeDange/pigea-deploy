<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * editoria_cms.md — FASE 2.
 *
 * Adicionar `news.destaque_editorial` (boolean) para controlo editorial
 * do hero e da zona "Destaques" — pin manual pelo editor; quando sem
 * marcadas suficientes, os sítios caem no fallback das mais recentes
 * (FASE 2 do spec).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->boolean('destaque_editorial')->default(false)->after('date_label');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('destaque_editorial');
        });
    }
};
