<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 11 (cms_v.md) — campo genérico «Link (URL)» para notícias.
 *
 * Além do vídeo (`video_link`) e do documento (`documento_link`), a notícia
 * pode agora apontar para um destino externo arbitrário (site oficial,
 * artigo completo, fonte original, conta oficial, etc.).
 * Aditivo — sem perda de dados, SQL direto (sem doctrine/dbal).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `news` ADD COLUMN `link` VARCHAR(255) NULL AFTER `documento_link`");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `news` DROP COLUMN `link`");
    }
};
