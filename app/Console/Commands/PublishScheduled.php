<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;

/**
 * Fase 8.2 (cms_v.md) — publicar, na prática, as notícias agendadas.
 *
 * Uma notícia «agendada» é publicada com status=published e published_at no
 * futuro — invisível no frontend até à hora chegar (pelo scopePublished).
 * Este comando faz a transição formal: marca date_label quando chega a hora.
 * Idempotente (só altera registos em falta) e à prova de overlap.
 */
class PublishScheduled extends Command
{
    protected $signature = 'news:publish-scheduled {--dry-run : Apenas reporta, não altera nada}';

    protected $description = 'Marca as notícias agendadas cuja hora já chegou (date_label e published_at).';

    public function handle(): int
    {
        $agora = now();

        $query = News::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', $agora)
            ->whereNull('date_label');

        $total = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("Dry-run: {$total} notícia(s) seriam publicadas agora.");

            return self::SUCCESS;
        }

        $atualizadas = $query->update([
            'date_label' => $agora->locale('pt')->translatedFormat('d M Y'),
        ]);

        $this->info("Notícias agendadas publicadas: {$atualizadas} de {$total}.");

        return self::SUCCESS;
    }
}
