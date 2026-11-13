<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FASE 8 (11/11/2026): chave ÚNICA em companies.nif a nível de BD.
 *
 * A validação `unique:companies,nif` do formulário público não impede dois
 * POST concorrentes (check-then-insert) — a BD passa a ser a última linha de
 * defesa. Corrida => uma inserção passa, a outra recebe 23000 (o
 * RegistoEmpresa apanha e responde com a mensagem pt do formulário, nunca
 * 500). Verificado antes: 5 registos dev, 0 duplicados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unique('nif');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['nif']);
        });
    }
};
