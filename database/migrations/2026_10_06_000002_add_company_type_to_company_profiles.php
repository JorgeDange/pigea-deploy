<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `tipo_empresa` do formulário /registo-empresa (`registo-empresa.html`).
 *
 * Gravada sem exibição — o painel da empresa (`painel-empresa-perfil.html`)
 * não tem este campo (mesmo precedente de `documents`/`images` da Fase 8).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('company_type', 64)->nullable()->after('sector');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn('company_type');
        });
    }
};
