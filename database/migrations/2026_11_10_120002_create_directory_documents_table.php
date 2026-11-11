<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Artigos/Documentos do Diretório (restrutura.md §3.3).
 *
 * Carregados EXCLUSIVAMENTE pela equipa interna no /admin (Fase 4) — não
 * há submissão pública. Vídeo/documento são apenas LINK (restrutura.md §1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('directory_documents', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('descricao', 500);
            $table->enum('tipo', ['artigo', 'documento', 'lei']);
            $table->string('link_download');
            $table->json('areas_atuacao');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directory_documents');
    }
};
