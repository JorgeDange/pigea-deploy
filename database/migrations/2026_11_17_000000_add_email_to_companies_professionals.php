<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Campo `email` (contacto) para o Diretório — permite que a equipa avise o
 * candidato sobre a decisão (em processamento / aprovado / não aprovado)
 * por email.
 *
 * Nullable: os registos antigos não têm email e continuam válidos; só os
 * novos (submetidos pelos formulários públicos atualizados) são emailados.
 * O NIF continua a ser a chave única — o email não recebe unique() porque
 * o mesmo contacto/empresa pode resubmeter.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('email', 190)->nullable()->after('contactos');
        });

        Schema::table('professionals', function (Blueprint $table) {
            $table->string('email', 190)->nullable()->after('contactos');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('professionals', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
