<?php

namespace Tests\Feature;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE 7 — banner publicitário no frontend (restrutura.md §3.5, FASE 6):
 * período ativo/expirado, posição, sem link → não clicável e
 * desempate pelo mais recente (pub.md §6.2).
 *
 * O componente é um Blade anónimo — renderiza-se a vista diretamente.
 * O seeder insere 3 banners de demonstração; os testes usam URLs/títulos
 * únicos "TEST…" para não colidir.
 */
class BannersFrontendTest extends TestCase
{
    use RefreshDatabase;

    private function render(string $posicao, bool $wrapper = false): string
    {
        return view('components.banner-publicitario', [
            'posicao' => $posicao,
            'wrapper' => $wrapper,
        ])->render();
    }

    public function test_banner_vigente_no_topo_com_link(): void
    {
        Banner::create([
            'titulo' => 'TEST Topo Vigente',
            'imagem_url' => 'images/all/banner.jpg',
            'link_destino' => 'https://anunciante.teste.ao',
            'posicao' => 'topo',
            'data_inicio' => now()->subDay()->toDateString(),
            'data_fim' => now()->addMonth()->toDateString(),
            'ativo' => true,
        ]);

        $html = $this->render('topo');

        $this->assertStringContainsString('https://anunciante.teste.ao', $html);
        $this->assertStringContainsString('Publicidade', $html);
        $this->assertStringContainsString('alt="TEST Topo Vigente"', $html);
        // 2026-11-12: o banner do TOPO passou a `loading="eager"` - é above-the-fold
        // na barra superior e, neste ambiente (Chromium), imagens `loading="lazy"`
        // NUNCA chegam a carregar (verificado: pedido nunca emitido; página de
        // teste isolada confirma que qualquer <img loading="lazy"> fica a 0x0).
        // Sidebar/rodapé mantêm `lazy` (abaixo da dobra).
        $this->assertStringContainsString('loading="eager"', $html);
    }

    public function test_banner_expirado_nao_renderiza(): void
    {
        Banner::create([
            'titulo' => 'TEST Expirado',
            'imagem_url' => 'images/all/banner.jpg',
            'posicao' => 'topo',
            'data_inicio' => now()->subMonths(2)->toDateString(),
            'data_fim' => now()->subMonth()->toDateString(),
            'ativo' => true,
        ]);

        $this->assertStringNotContainsString('TEST Expirado', $this->render('topo'));
    }

    public function test_banner_inativo_nao_renderiza(): void
    {
        Banner::create([
            'titulo' => 'TEST Inativo',
            'imagem_url' => 'images/all/banner.jpg',
            'posicao' => 'topo',
            'ativo' => false,
        ]);

        $this->assertStringNotContainsString('TEST Inativo', $this->render('topo'));
    }

    public function test_sem_link_o_banner_nao_e_clicavel(): void
    {
        Banner::create([
            'titulo' => 'TEST Sem Link',
            'imagem_url' => 'images/all/banner.jpg',
            'link_destino' => null,
            'posicao' => 'topo',
            'ativo' => true,
        ]);

        $html = $this->render('topo');

        $this->assertStringContainsString('TEST Sem Link', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }

    public function test_posicao_sem_banner_nao_gera_html(): void
    {
        $this->assertSame('', $this->render('rodape'));
    }

    public function test_desempate_mostra_o_mais_recente(): void
    {
        Banner::create([
            'titulo' => 'TEST Antigo',
            'imagem_url' => 'images/all/banner.jpg',
            'link_destino' => 'https://antigo.teste.ao',
            'posicao' => 'topo',
            'ativo' => true,
        ]);

        $recente = Banner::create([
            'titulo' => 'TEST Recente',
            'imagem_url' => 'images/all/banner.jpg',
            'link_destino' => 'https://recente.teste.ao',
            'posicao' => 'topo',
            'ativo' => true,
        ]);

        $html = $this->render('topo');

        $this->assertStringContainsString('https://recente.teste.ao', $html);
        $this->assertStringNotContainsString('https://antigo.teste.ao', $html);
        $this->assertGreaterThan(0, $recente->id);
    }

    public function test_posicao_sidebar_com_wrapper_e_imagem_externa(): void
    {
        Banner::create([
            'titulo' => 'TEST Sidebar',
            'imagem_url' => 'https://cdn.teste.ao/side.jpg',
            'posicao' => 'sidebar',
            'ativo' => true,
        ]);

        $html = $this->render('sidebar', true);

        $this->assertStringContainsString('https://cdn.teste.ao/side.jpg', $html);
        $this->assertStringContainsString('box-widget', $html);
    }

    /**
     * FASE 18 — posição `lateral` (vertical ~300×600): renderiza com
     * wrapper box-widget, classe própria e lazy (abaixo da dobra).
     */
    public function test_posicao_lateral_com_wrapper(): void
    {
        Banner::create([
            'titulo' => 'TEST Lateral',
            'imagem_url' => 'https://cdn.teste.ao/vertical.jpg',
            'link_destino' => 'https://anunciante.teste.ao',
            'posicao' => 'lateral',
            'ativo' => true,
        ]);

        $html = $this->render('lateral', true);

        $this->assertStringContainsString('https://cdn.teste.ao/vertical.jpg', $html);
        $this->assertStringContainsString('banner-publicitario--lateral', $html);
        $this->assertStringContainsString('box-widget', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
    }

    /** FASE 18 — sem banner `lateral` ativo, o slot vertical não gera HTML. */
    public function test_posicao_lateral_sem_banner_nao_gera_html(): void
    {
        $this->assertSame('', $this->render('lateral'));
    }

    /** FASE 18 — banner `lateral` sem link fica só imagem (não clicável). */
    public function test_posicao_lateral_sem_link_nao_e_clicavel(): void
    {
        Banner::create([
            'titulo' => 'TEST Lateral Sem Link',
            'imagem_url' => 'images/banners/lateral-demo.jpg',
            'posicao' => 'lateral',
            'ativo' => true,
        ]);

        $html = $this->render('lateral');

        $this->assertStringContainsString('TEST Lateral Sem Link', $html);
        $this->assertStringNotContainsString('<a ', $html);
    }
}
