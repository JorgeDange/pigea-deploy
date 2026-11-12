<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulo Vídeos do admin — posts de vídeo por link (YouTube, Vimeo, etc.).
 *
 * - `url`: o link do vídeo (watch/embed/... — o frontend extrai o embed);
 * - `imagem`: miniatura opcional (fallback: YouTube `img.youtube.com`);
 * - sem rascunho: criado = publicado; apagar para retirar do site.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 190);
            $table->text('resumo')->nullable();
            $table->string('url');
            $table->string('imagem')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
