<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Publicidade — banners (restrutura.md §3.5).
 *
 * Paradigma da tabela única `banners` (superscreve o antigo pub.md com
 * banner_slides). A imagem é `imagem_url` (upload ou link) — o Spatie Media
 * é usado APENAS no modelo News (restrutura.md §7.5). Banners exibem-se
 * quando `ativo` e dentro do período data_inicio..data_fim (limites
 * opcionais).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('imagem_url');
            $table->string('link_destino')->nullable();
            $table->enum('posicao', ['topo', 'sidebar', 'rodape'])->index();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
