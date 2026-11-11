<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diretório de Empresas (restrutura.md §3.1).
 *
 * Sem login: as empresas submetem o formulário público (/registo-empresa,
 * Fase 3) e ficam `pendente` até aprovação da equipa interna no /admin.
 * `areas_atuacao` e `redes_sociais` são JSON (checkboxes múltiplos /
 * pares rede→URL opcionais).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('nome_empresa');
            $table->string('nif');
            $table->string('contactos');
            $table->json('areas_atuacao');
            $table->json('redes_sociais')->nullable();
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
