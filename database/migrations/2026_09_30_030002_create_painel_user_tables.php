<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabelas do painel do utilizador/empresa (decisão do utilizador 30/09/2026):
 *
 * - `applications`: candidatura a uma vaga. `user_id` NULL quando o visitante se
 *   candidata sem sessão (o formulário público de `jobs.show` só pede nome/email).
 *   `status`: em-analise / aceite / rejeitada (definido pela empresa).
 * - `saved_jobs`: vagas guardadas no painel (`vagas guardadas`).
 * - `notifications`: notificações do painel (texto/ícone/lida) — ícone `fa-*`
 *   gravado porque o próprio protótipo varia o ícone por item.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_id')->constrained('vacancies')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('cv_url')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('em-analise');
            $table->timestamps();
        });

        Schema::create('saved_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('vacancies')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'job_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('icon', 32);
            $table->string('title');
            $table->string('text');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('saved_jobs');
        Schema::dropIfExists('notifications');
    }
};
