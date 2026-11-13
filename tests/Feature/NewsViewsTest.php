<?php

namespace Tests\Feature;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE E2 (2026-11-13): as "views" por notícia contam apenas visitas reais.
 *
 *  - bots/crawlers conhecidos (Google, Bing, Facebook, WhatsApp, …) não contam;
 *  - a mesma sessão conta cada notícia UMA vez por janela de 5 minutos;
 *  - sem user-agent → não conta (clients vazios são tipicamente bots).
 */
class NewsViewsTest extends TestCase
{
    use RefreshDatabase;

    private function noticia(): News
    {
        return News::where('status', 'published')->orderBy('id')->firstOrFail();
    }

    private function vistas(News $noticia): int
    {
        return (int) News::findOrFail($noticia->id)->views_count;
    }

    public function test_visita_real_conta_uma_leitura(): void
    {
        $noticia = $this->noticia();
        $antes = $this->vistas($noticia);

        $this->get(route('news.show', $noticia->slug))->assertOk();

        $this->assertSame($antes + 1, $this->vistas($noticia));
    }

    public function test_googlebot_nao_conta_leitura(): void
    {
        $noticia = $this->noticia();
        $antes = $this->vistas($noticia);

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->get(route('news.show', $noticia->slug))
            ->assertOk();

        $this->assertSame($antes, $this->vistas($noticia));
    }

    public function test_facebook_external_hit_nao_conta_leitura(): void
    {
        $noticia = $this->noticia();
        $antes = $this->vistas($noticia);

        $this->withHeaders(['User-Agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)'])
            ->get(route('news.show', $noticia->slug))
            ->assertOk();

        $this->assertSame($antes, $this->vistas($noticia));
    }

    public function test_mesma_sessao_nao_conta_duas_vezes_em_cinco_minutos(): void
    {
        $noticia = $this->noticia();
        $antes = $this->vistas($noticia);

        $this->get(route('news.show', $noticia->slug))->assertOk();
        $this->get(route('news.show', $noticia->slug))->assertOk();

        $this->assertSame($antes + 1, $this->vistas($noticia));
    }

    public function test_user_agent_vazio_nao_conta(): void
    {
        $noticia = $this->noticia();
        $antes = $this->vistas($noticia);

        $this->withHeaders(['User-Agent' => ''])
            ->get(route('news.show', $noticia->slug))
            ->assertOk();

        $this->assertSame($antes, $this->vistas($noticia));
    }
}
