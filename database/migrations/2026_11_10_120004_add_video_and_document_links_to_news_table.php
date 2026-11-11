<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos externos das notícias (restrutura.md §3.4).
 *
 * Vídeo e documento suportam APENAS link (restrutura.md §1 — "Vídeos e
 * Documentos suportam apenas Link"); as imagens (capa/contexto) passam a
 * ser geridas pelo Spatie Media Library no modelo News.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('video_link')->nullable()->after('image_caption');
            $table->string('documento_link')->nullable()->after('video_link');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['video_link', 'documento_link']);
        });
    }
};
