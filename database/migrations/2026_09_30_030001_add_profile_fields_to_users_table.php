<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos do formulário "Editar perfil" do painel do utilizador
 * (`painel-utilizador-perfil.html`): telefone, província e bio.
 * O protótipo não tinha onde o guardar — decisão do utilizador (30/09/2026).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 40)->nullable()->after('avatar');
            $table->string('province', 64)->nullable()->after('phone');
            $table->text('bio')->nullable()->after('province');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'province', 'bio']);
        });
    }
};
