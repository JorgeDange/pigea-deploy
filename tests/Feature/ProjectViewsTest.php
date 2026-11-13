<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE E2 (anti-bot, estendido a projetos 2026-11-13): o contador por
 * projeto conta apenas leituras reais — bots e a mesma sessão (5 min)
 * não incrementam, como nas notícias.
 */
class ProjectViewsTest extends TestCase
{
    use RefreshDatabase;

    private function projeto(): Project
    {
        return Project::where('status', 'published')->orderBy('id')->firstOrFail();
    }

    private function vistas(Project $projeto): int
    {
        return (int) Project::findOrFail($projeto->id)->views_count;
    }

    public function test_visita_real_conta_uma_leitura_no_projeto(): void
    {
        $projeto = $this->projeto();
        $antes = $this->vistas($projeto);

        $this->get(route('projects.show', $projeto->slug))->assertOk();

        $this->assertSame($antes + 1, $this->vistas($projeto));
    }

    public function test_googlebot_nao_conta_leitura_no_projeto(): void
    {
        $projeto = $this->projeto();
        $antes = $this->vistas($projeto);

        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->get(route('projects.show', $projeto->slug))
            ->assertOk();

        $this->assertSame($antes, $this->vistas($projeto));
    }

    public function test_mesma_sessao_nao_conta_duas_vezes_no_mesmo_projeto(): void
    {
        $projeto = $this->projeto();
        $antes = $this->vistas($projeto);

        $this->get(route('projects.show', $projeto->slug))->assertOk();
        $this->get(route('projects.show', $projeto->slug))->assertOk();

        $this->assertSame($antes + 1, $this->vistas($projeto));
    }
}
