<?php

namespace Tests\Feature;

use App\Console\Commands\PublishScheduled;
use App\Models\News;
use Tests\TestCase;

/**
 * Fase 8.2 (cms_v.md) — comando `news:publish-scheduled`.
 *
 * Garante que notícias marcadas como publicadas com `published_at` já
 * chegadas e `date_label` em falta recebem a data certa.
 */
class PublishScheduledTest extends TestCase
{
    public function test_marca_published_cujo_horario_chegou(): void
    {
        $noticia = News::query()->where('status', 'published')->firstOrFail();
        $noticia->update([
            'published_at' => now()->subDay(),
            'date_label' => null,
        ]);

        $this->artisan('news:publish-scheduled')
            ->assertSuccessful();

        $this->assertDatabaseHas('news', [
            'id' => $noticia->id,
            'date_label' => now()->locale('pt')->translatedFormat('d M Y'),
        ]);
    }

    public function test_nao_marca_published_no_futuro(): void
    {
        $noticia = News::query()->where('status', 'published')->firstOrFail();
        $noticia->update([
            'published_at' => now()->addDay(),
            'date_label' => null,
        ]);

        $this->artisan('news:publish-scheduled')
            ->assertSuccessful();

        $this->assertDatabaseHas('news', [
            'id' => $noticia->id,
            'date_label' => null,
        ]);
    }
}
