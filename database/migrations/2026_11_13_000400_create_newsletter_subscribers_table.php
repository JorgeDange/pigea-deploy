<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE N1 — Newsletter do PIGEA.
 *
 * Lista de subscritores da newsletter enviada pelo portal (Opção B:
 * newsletter dentro do próprio site, sem serviço externo). Dupla opt-in:
 *
 *   pending     — criado pelo utilizador (ou pelo admin — teste manual),
 *                 token enviado por email; só após o clique ⇒ confirmed;
 *   confirmed   — confirmou o opt-in; única lista usada para envio (N2);
 *   unsubscribed — pediu cancelamento (link único) ou foi removido pelo admin.
 *
 * Sem dados pessoais além de nome e email (o essencial para dirigir a
 * mensagem pelo nome e evitar duplicados). O `token` é aleatório (64 ch.),
 * nunca sequencial nem derivado de dados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            // Decisão do cliente (2026-11-13): só o email é necessário — sem nome.
            $table->string('email', 190)->unique();
            $table->string('token', 64)->index();
            $table->enum('status', ['pending', 'confirmed', 'unsubscribed'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
