<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase N2 (evolução da Newsletter — aprovada 2026-11-17, opção B):
 * «Aviso de novo artigo» — o admin compõe a partir do painel, o sistema
 * gera o email a partir do artigo escolhido e envia aos subscritores
 * ativos, em lotes de no máximo 100 por clique (restrição do alojamento).
 *
 * `newsletter_editions`     — registo da edição (artigo, progresso, estado).
 * `newsletter_edition_items`— log por destinatário (reenvio de falhados fica
 *                             a cargo dos cliques seguintes, que só saltam
 *                             quem já recebeu (estado=sent)).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->nullable()->constrained('news')->nullOnDelete();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('enviados')->default(0);
            $table->unsignedInteger('falhados')->default(0);
            $table->string('estado', 16)->default('pending'); // pending | done | failed
            $table->foreignId('criado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->timestamp('finished_at')->nullable();
            $table->index('estado');
        });

        Schema::create('newsletter_edition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained('newsletter_editions')->cascadeOnDelete();
            $table->string('email', 190);
            $table->string('estado', 16)->default('sent'); // sent | failed
            $table->timestamps();
            $table->unique(['edition_id', 'email']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_edition_items');
        Schema::dropIfExists('newsletter_editions');
    }
};
