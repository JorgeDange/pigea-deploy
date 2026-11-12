<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 15 (publicidade no destaque da home + sidebars).
 *
 * Destaques rotativos (até 3 em exibição; mais podem existir admin-side,
 * só os `ativo`s e válidos no período entram).
 *
 *  - escopo: onde o destaque aparece
 *      site    → todas as colocações (home e sidebars das restantes páginas)
 *      inicio  → só a página inicial
 *      sidebar → só as sidebars das páginas interiores (notícias, legislação,
 *                eventos, pesquisa, fornecedores, contactos, projetos show)
 *  - imagem_url: upload (storage/destaques/…) OU link http(s) OU asset
 *      público (como banners, ver BannerFormController);
 *  - ordem: a posição na grelha quando há vários destaques (1º, 2º, 3º);
 *  - sem limite rígido no modelo — o limite de 3 é da seleção editorial
 *      (ativos + ordem) no painel.
 *
 * Aditiva e idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('destaques')) {
            return;
        }

        Schema::create('destaques', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 190);
            $table->string('imagem_url', 2048);
            $table->string('link_destino', 2048)->nullable();
            $table->string('escopo', 16)->default('site');
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedTinyInteger('ordem')->default(1);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['escopo', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destaques');
    }
};
