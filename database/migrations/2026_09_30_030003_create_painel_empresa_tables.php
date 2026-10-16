<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Painel da empresa (decisão do utilizador, 30/09/2026):
 *
 * - `company_profiles`: ficha privada da empresa (nome, NIF, setor, província,
 *   contactos, descrição, logo/website) — **separada do `directory`** (diretório
 *   público) porque o NIF e os dados internos não entram no portal público e uma
 *   empresa pode existir no painel sem estar listada.
 * - `opportunities`: oportunidades de negócio da empresa
 *   (`painel-empresa-oportunidades.html` — título/tipo/prazo/estado/autor).
 * - `vacancies` (jobs) ganha colunas do formulário `painel-empresa-vaga-nova.html`:
 *   tipo de contrato, província, prazo, requisitos e responsabilidades.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('nif', 32)->nullable();
            $table->string('sector', 64)->nullable();
            $table->string('province', 64)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 64)->nullable();
            $table->date('deadline')->nullable();
            $table->string('status', 20)->default('ativa');
            $table->timestamps();
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('contract_type', 64)->nullable();
            $table->string('province', 64)->nullable();
            $table->date('deadline')->nullable();
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('opportunities');
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn(['contract_type', 'province', 'deadline', 'requirements', 'responsibilities']);
        });
    }
};
