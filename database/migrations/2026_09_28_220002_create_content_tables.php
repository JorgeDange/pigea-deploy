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
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content')->nullable();
                $table->string('image')->nullable();
                $table->string('image_caption')->nullable();
                $table->string('status', 20)->default('published')->index();
                $table->date('published_at')->nullable()->index();
                $table->unsignedInteger('comments_count')->default(0);
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamps();
                $table->softDeletes();

                if ($name === 'news') {
                    $table->string('province', 120)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::dropIfExists($name);
        }
    }
};
