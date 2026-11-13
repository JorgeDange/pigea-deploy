<?php

namespace Tests\Feature;

use App\Models\Destaque;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE 15 — widget «Escolha da Equipa» com banners rotativos (pub.md).
 *
 * Garante: fallback editorial (sem publicidade), destaque ativo substitui
 * o editorial no widget (home e /projetos/{slug}), escopo de colocação
 * (site/inicio/sidebar), validação do admin e rotas do painel.
 */
class DestaqueTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    private function criar(array $extra = []): Destaque
    {
        return Destaque::create(array_merge([
            'titulo' => 'TEST Banner',
            'imagem_url' => 'images/all/banner.jpg',
            'escopo' => 'site',
            'ativo' => true,
        ], $extra));
    }

    private function paginaProjeto(): string
    {
        $projeto = Project::published()->with('category')->firstOrFail();

        return route('projects.show', $projeto->slug);
    }

    // ------------------------------------------------------------------
    // Frontend público — widget «Escolha da Equipa»
    // ------------------------------------------------------------------

    public function test_home_sem_destaque_mostra_editorial(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('destaque-equipa-slide', $html);
        $this->assertStringContainsString('Escolha da Equipa', $html);
        // editorial: marcador de categoria de notícia
        $this->assertStringContainsString('post-category-marker', $html);
    }

    public function test_destaque_site_substitui_o_editorial_no_widget(): void
    {
        $this->criar([
            'titulo' => 'TEST Banner Site',
            'link_destino' => 'https://anunciante.destaques.test.ao',
        ]);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('destaque-equipa-slide', $html);
        $this->assertStringContainsString('TEST Banner Site', $html);
        $this->assertStringContainsString('Publicidade', $html);
        $this->assertStringContainsString('https://anunciante.destaques.test.ao', $html);
        $this->assertStringContainsString('rel="noopener sponsored"', $html);
    }

    public function test_escopo_inicio_nao_aparece_na_pagina_de_projeto(): void
    {
        $this->criar(['titulo' => 'TEST So Home', 'escopo' => 'inicio']);

        $this->assertStringContainsString('TEST So Home', $this->get('/')->getContent());

        $projeto = $this->get($this->paginaProjeto())->getContent();
        $this->assertStringNotContainsString('TEST So Home', $projeto);
    }

    public function test_escopo_sidebar_nao_aparece_na_home(): void
    {
        $this->criar(['titulo' => 'TEST So Interior', 'escopo' => 'sidebar']);

        $home = $this->get('/')->getContent();
        $this->assertStringNotContainsString('TEST So Interior', $home);

        $projeto = $this->get($this->paginaProjeto())->getContent();
        $this->assertStringContainsString('TEST So Interior', $projeto);
    }

    public function test_escopo_site_aparece_na_home_e_na_pagina_de_projeto(): void
    {
        $this->criar(['titulo' => 'TEST Todo Site', 'escopo' => 'site']);

        $this->assertStringContainsString('TEST Todo Site', $this->get('/')->getContent());
        $this->assertStringContainsString('TEST Todo Site', $this->get($this->paginaProjeto())->getContent());
    }

    public function test_destaque_expirado_nao_renderiza(): void
    {
        $this->criar([
            'titulo' => 'TEST Expirado',
            'data_inicio' => now()->subMonths(2)->toDateString(),
            'data_fim' => now()->subMonth()->toDateString(),
        ]);

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('TEST Expirado', $html);
        $this->assertStringContainsString('post-category-marker', $html); // editorial mantém-se
    }

    public function test_destaque_inativo_nao_renderiza(): void
    {
        $this->criar(['titulo' => 'TEST Inativo', 'ativo' => false]);

        $this->assertStringNotContainsString('TEST Inativo', $this->get('/')->getContent());
    }

    public function test_destaque_sem_link_nao_e_clicavel(): void
    {
        $this->criar(['titulo' => 'TEST Sem Link', 'link_destino' => null]);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('destaque-equipa-slide', $html);
        $this->assertStringNotContainsString('destaque-link', $html);
    }

    // ------------------------------------------------------------------
    // Admin
    // ------------------------------------------------------------------

    public function test_admin_pode_aceder_a_gestao_de_destaques(): void
    {
        $this->actingAs($this->admin())->get(route('admin.destaques'))->assertOk();
        $this->actingAs($this->admin())->get(route('admin.destaques.novo'))->assertOk();
    }

    public function test_guest_redirect_para_login(): void
    {
        $this->get(route('admin.destaques'))->assertRedirect(route('login'));
    }

    public function test_criar_destaque_com_imagem_externa(): void
    {
        $this->actingAs($this->admin())->post(route('admin.destaques.novo.store'), [
            'titulo' => 'TEST Criar Destaque',
            'imagem_link' => 'https://cdn.destaques.test.ao/img.jpg',
            'escopo' => 'site',
            'ativo' => true,
        ])->assertRedirect(route('admin.destaques'));

        $destaque = Destaque::where('titulo', 'TEST Criar Destaque')->firstOrFail();

        $this->assertSame('https://cdn.destaques.test.ao/img.jpg', $destaque->imagem_url);
        $this->assertSame('site', $destaque->escopo);
        $this->assertTrue($destaque->ativo);
    }

    public function test_criar_destaque_sem_imagem_da_erro(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.destaques.novo'))
            ->post(route('admin.destaques.novo.store'), [
                'titulo' => 'TEST Sem Imagem',
                'escopo' => 'site',
            ])->assertSessionHasErrors();

        $this->assertDatabaseMissing('destaques', ['titulo' => 'TEST Sem Imagem']);
    }

    public function test_escopo_invalido_da_erro(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.destaques.novo'))
            ->post(route('admin.destaques.novo.store'), [
                'titulo' => 'TEST Escopo Mau',
                'imagem_link' => 'https://cdn.destaques.test.ao/img.jpg',
                'escopo' => 'lugar-nenhum',
            ])->assertSessionHasErrors('escopo');

        $this->assertDatabaseMissing('destaques', ['titulo' => 'TEST Escopo Mau']);
    }

    public function test_toggle_ativa_e_desativa(): void
    {
        $destaque = $this->criar(['titulo' => 'TEST Toggle']);

        $this->actingAs($this->admin())->patch(route('admin.destaques.toggle', $destaque->id))
            ->assertRedirect(route('admin.destaques'));
        $this->assertFalse($destaque->fresh()->ativo);

        $this->actingAs($this->admin())->patch(route('admin.destaques.toggle', $destaque->id));
        $this->assertTrue($destaque->fresh()->ativo);
    }

    public function test_destroy_remove_o_registo(): void
    {
        $destaque = $this->criar(['titulo' => 'TEST Apagar']);

        $this->actingAs($this->admin())->delete(route('admin.destaques.destroy', $destaque->id));

        $this->assertSoftDeleted('destaques', ['id' => $destaque->id]);
    }
}
