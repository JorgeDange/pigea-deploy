<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * editoria_cms.md — FASE 1 (2026-11-13): nova posição `largo` nos banners.
 *
 * Estende o enum `posicao` com `largo` — slot horizontal de largura total
 * que substitui o antigo `images/all/banner.jpg` do tema (faixas nas páginas
 * /noticias, /projetos, /pesquisa, …). O formulário do admin e a validação
 * já aceitavam «largo»; a coluna ficou por atualizar, o que gerava
 * `1265 Data truncated for column 'posicao'` ao gravar.
 *
 * ALTER nativo (MySQL): `change()` do Blueprint não reescreve enums de
 * forma fiável sem doctrine/dbal.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE banners MODIFY posicao ENUM('topo','sidebar','rodape','lateral','largo') NOT NULL");
    }

    public function down(): void
    {
        // Reversão exige que não existam banners `largo` (o MySQL recusa
        // truncar valores de enum). Removê-los antes, se necessário.
        DB::statement("ALTER TABLE banners MODIFY posicao ENUM('topo','sidebar','rodape','lateral') NOT NULL");
    }
};
