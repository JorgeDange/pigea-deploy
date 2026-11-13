<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 8.2 (cms_v.md) — permitir agendamento (data E hora) de publicação.
 *
 * published_at: date → datetime via SQL direto (sem dependência do doctrine/dbal).
 * Os valores existentes são preservados em meia-noite. Down() devolve a DATE
 * (perde apenas a hora, não as datas).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `news` MODIFY `published_at` DATETIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `news` MODIFY `published_at` DATE NULL');
    }
};
