<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Definições do portal de `/admin/configuracoes` (6 cartões do protótipo).
     * Guardadas em pares `key`/`value` para não criar 15 colunas rígidas: um
     * registo por campo (`portal`, `descricao`, `logo`, `email`, `facebook`,
     * `meta_title`, `template`, ...).
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
