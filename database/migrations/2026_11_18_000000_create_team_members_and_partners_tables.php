<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Módulos institucionais — Equipa e Parceiros (secção "Sobre Nós").
 *
 *  team_members: membros da equipa PIGEA, organizados por secção hierárquica
 *  (campo `cargo_select`). Quando `cargo_select = 'outros'`, `cargo_outro`
 *  guarda o rótulo a mostrar (obrigatório nesse caso — validado no POST).
 *
 *  partners: parceiros do PIGEA; `tipo` separa a página em duas secções
 *  (Empresas e Profissionais). `imagem_url` é um URL único (logo de empresa
 *  ou foto do profissional), como definido pelo cliente.
 *
 *  Sem upload de imagens por upload por POST — tudo URLs (decisão do cliente).
 *  Softer delete em ambos — eliminação reversível por defeito.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 190);
            $table->string('cargo_select', 50);      // ver Institucional::SECCOES_EQUIPA
            $table->string('cargo_outro', 190)->nullable();
            $table->string('foto_url', 2048)->nullable();
            $table->string('facebook', 2048)->nullable();
            $table->string('instagram', 2048)->nullable();
            $table->string('linkedin', 2048)->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['cargo_select', 'ordem']);
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 190);
            $table->enum('tipo', ['empresa', 'profissional']);
            $table->string('imagem_url', 2048)->nullable();
            $table->text('detalhes')->nullable();
            $table->unsignedSmallInteger('ordem')->default(0);
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tipo', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
        Schema::dropIfExists('team_members');
    }
};
