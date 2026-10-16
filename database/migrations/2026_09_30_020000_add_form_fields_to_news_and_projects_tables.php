<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colunas para os formulários `admin/noticia-nova.html` e `admin/projeto-novo.html`
 * (decisão do utilizador, 30/09/2026): campos que o protótipo mostra e que não
 * tinham onde ser guardados.
 *
 * `news`: `tags`, `meta_title`, `meta_description`.
 * `projects`: `mineral`, `province` (já via news), `phase`, `operator`,
 * `area` (km², decimal), `documents` e `images` (JSON com os caminhos, guardados
 * sem exibição — o protótipo não tem galeria nem lista de documentos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('tags')->nullable()->after('image_caption');
            $table->string('meta_title')->nullable()->after('tags');
            $table->string('meta_description')->nullable()->after('meta_title');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('province', 64)->nullable()->after('image_caption');
            $table->string('mineral', 64)->nullable()->after('province');
            $table->string('phase', 64)->nullable()->after('mineral');
            $table->string('operator')->nullable()->after('phase');
            $table->decimal('area', 12, 2)->nullable()->after('operator');
            $table->json('documents')->nullable()->after('area');
            $table->json('images')->nullable()->after('documents');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['tags', 'meta_title', 'meta_description']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['province', 'mineral', 'phase', 'operator', 'area', 'documents', 'images']);
        });
    }
};
