<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `status`: coluna que faltava em `users` para a coluna "Estado" de
     * `/admin/utilizadores` (protótipo: Ativo / Pendente / Bloqueado).
     * Valores: `active`, `pending`, `blocked` — por omissão `active`, pelo que
     * as contas já existentes ficam Ativas.
     *
     * `last_login_at`: alimenta a coluna "Último acesso" (o registo é feito no
     * evento `Login`, ver `FortifyServiceProvider`).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'last_login_at']);
        });
    }
};
