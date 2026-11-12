<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FASE 18 — banners verticais: nova posição `lateral`.
 *
 * Estende o enum `posicao` (topo|sidebar|rodape) com `lateral` — slot
 * vertical na sidebar (largura da coluna, enquadramento 1:1; ajustes do
 * cliente 2026-11-12) usado nas sidebars de: /noticias/{slug},
 * /projetos/{slug}, /eventos, /legislacao, /fornecedores, /pesquisa e
 * home. O slot `sidebar` mantém-se como está (banner partilhado em
 * /noticias, /noticias/{slug} e /directorio).
 *
 * ALTER nativo (MySQL): `change()` do Blueprint não reescreve enums de
 * forma fiável sem doctrine/dbal.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE banners MODIFY posicao ENUM('topo','sidebar','rodape','lateral') NOT NULL");
    }

    public function down(): void
    {
        // Reversão exige que não existam banners `lateral` (o MySQL recusa
        // truncar valores de enum). Removê-los antes, se necessário.
        DB::statement("ALTER TABLE banners MODIFY posicao ENUM('topo','sidebar','rodape') NOT NULL");
    }
};
