<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'news',
        'projects',
        'vacancies',
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
            Schema::table($name, function (Blueprint $table) {
                $table->string('date_label', 40)->nullable()->after('published_at');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('date_label');
            });
        }
    }
};
