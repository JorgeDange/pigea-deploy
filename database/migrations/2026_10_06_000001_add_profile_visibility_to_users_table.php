<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `painel-utilizador-definicoes.html` — cartão "Privacidade" (select
 * `visibilidade`): column para guardar a escolha (o form do protótipo não
 * tinha `action`; §2.2 pede formulários funcionais).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_visibility', 20)->default('todos')->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_visibility');
        });
    }
};
