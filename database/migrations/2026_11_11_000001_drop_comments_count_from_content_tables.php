<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remoção completa do sistema de comentários (comentarios.md, 11/11/2026).
 *
 * Não existia tabela `comments`, nem Model `Comment`, nem componente Livewire
 * relacionado — apenas o contador `comments_count` embutido (e a respetiva
 * apresentação). A coluna é removida das 10 tabelas de conteúdo; o guard
 * `hasColumn` torna a migração segura em bases antigas (dev/teste) e no-op em
 * instalações novas (onde já não é criada).
 */
return new class extends Migration
{
    private const TABLES = [
        'news',
        'projects',
        'suppliers',
        'events',
        'legislation',
        'research',
        'interviews',
        'technologies',
        'directory',
        'training',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            if (Schema::hasColumn($name, 'comments_count')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropColumn('comments_count');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            if (! Schema::hasColumn($name, 'comments_count')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->unsignedInteger('comments_count')->default(0);
                });
            }
        }
    }
};
