<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelas de conteúdo público — todas têm `title` + `excerpt` (mesmo
     * esquema), necessários ao MySQL Full-Text usado pelo Scout (fase.md §2.2).
     */
    private const TABELAS = [
        'news', 'projects', 'directory', 'technologies', 'suppliers',
        'events', 'interviews', 'research', 'legislation',
        'training',
    ];

    public function up(): void
    {
        // SQLite (desenvolvimento local) não suporta índices FULLTEXT —
        // pular sem falhar; o MySQL (produção) cria normalmente.
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function ($table) {
                $table->fullText(['title', 'excerpt']);
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABELAS as $tabela) {
            Schema::table($tabela, function ($table) {
                $table->dropFullText(['title', 'excerpt']);
            });
        }
    }
};
