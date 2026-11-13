<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soft deletes nas tabelas da Fase 4 (reestrutura.md §5.5-5.6).
 *
 * As tabelas de conteúdo do painel (news, projects, …) usam SoftDeletes —
 * "Eliminar: soft delete (dá para reverter com restore())". Os documentos do
 * diretório e os banners passam a seguir a mesma regra: eliminar no /admin
 * é reversível.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('directory_documents', function (Blueprint $table) {
            $table->softDeletes()->after('areas_atuacao');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->softDeletes()->after('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('directory_documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
