<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Contador de visitas do portal (cms_pro.md §4.1 — cartão "Visitas no Site").
 *
 * Regras: só páginas públicas do frontend contam; /admin (prefixo
 * ADMIN_PATH), /livewire e redirects (≠ 200) nunca contam; bots não contam.
 */
class VisitsCounterTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_pagina_publica_conta_uma_visita(): void
    {
        PageView::query()->delete();

        $this->get(route('home'))->assertOk();

        $this->assertDatabaseHas('page_views', [
            'date' => now()->toDateString(),
            'visits' => 1,
        ]);
    }

    public function test_varias_visitas_acumulam_no_mesmo_dia(): void
    {
        PageView::query()->delete();

        $this->get(route('home'))->assertOk();
        $this->get(route('home'))->assertOk();

        $this->assertSame(
            2,
            (int) PageView::query()->where('date', now()->toDateString())->value('visits')
        );
    }

    public function test_admin_autenticado_nao_conta_visitas(): void
    {
        PageView::query()->delete();

        $this->actingAs($this->admin())->get(route('admin.index'))->assertOk();

        $this->assertSame(0, (int) PageView::query()->count());
    }

    public function test_admin_anonimo_redireciona_e_nao_conta(): void
    {
        PageView::query()->delete();

        $this->get(route('admin.index'))->assertRedirect();

        $this->assertSame(0, (int) PageView::query()->count());
    }

    public function test_redirect_publico_nao_conta(): void
    {
        PageView::query()->delete();

        $this->get('/diretorio')->assertStatus(301);

        $this->assertSame(0, (int) PageView::query()->count());
    }

    public function test_pagina_interna_de_noticia_publica_conta(): void
    {
        $noticia = News::where('status', 'published')->firstOrFail();

        PageView::query()->delete();

        $this->get(route('news.show', $noticia->slug))->assertOk();

        $this->assertSame(
            1,
            (int) PageView::query()->where('date', now()->toDateString())->sum('visits')
        );
    }
}
