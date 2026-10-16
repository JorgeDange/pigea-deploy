<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelas de conteúdo público — todas têm `title` + `excerpt` (mesmo
     * esquema), necessários ao MySQL Full-Text usado pelo Scout (fase.md §2.2).
     */
    private const TABELAS = [
        'news', 'projects', 'directory', 'technologies', 'suppliers',
        'events', 'interviews', 'research', 'vacancies', 'legislation',
        'training',
    ];

    public function up(): void
    {
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
