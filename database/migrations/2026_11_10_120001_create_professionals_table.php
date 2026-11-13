<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diretório de Profissionais (restrutura.md §3.2).
 *
 * Sem login: submissão pública (/registo-profissional, Fase 3) e aprovação
 * da equipa interna no /admin. `areas_atuacao` é JSON (checkboxes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professionals', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->string('formacao_academica');
            $table->string('contactos');
            $table->json('areas_atuacao');
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professionals');
    }
};
