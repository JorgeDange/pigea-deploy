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
     * FASE 18 — posição `lateral` (vertical): renderiza com wrapper box-widget,
     * classe própria e lazy (abaixo da dobra). Com 1 só banner é estático
     * (sem slider).
     */
    /** Apaga todos os banners — os testes assumem que nenhum está ativo por defeito. */
    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\Banner::query()->delete();
    }

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
        // 1 banner → estático, sem slider de rotação
        $this->assertStringNotContainsString('banner-lateral-slider', $html);
    }

    /**
     * FASE 18 — rotação: com 2+ banners `lateral` ativos renderiza slider
     * com data-autoplay (5s) e todos os slides; imagens `eager` (pré-load
     * para a troca não fazer flash).
     */
    public function test_posicao_lateral_rotativa_com_dois_banners(): void
    {
        Banner::create([
            'titulo' => 'TEST Lateral A',
            'imagem_url' => 'https://cdn.teste.ao/vertical-a.jpg',
            'link_destino' => 'https://a.teste.ao',
            'posicao' => 'lateral',
            'ativo' => true,
        ]);
        Banner::create([
            'titulo' => 'TEST Lateral B',
            'imagem_url' => 'https://cdn.teste.ao/vertical-b.jpg',
            'posicao' => 'lateral',
            'ativo' => true,
        ]);

        $html = $this->render('lateral', true);

        $this->assertStringContainsString('banner-lateral-slider', $html);
        $this->assertStringContainsString('data-autoplay="5000"', $html);
        $this->assertStringContainsString('swiper-wrapper', $html);
        $this->assertSame(2, substr_count($html, 'swiper-slide'), 'devia ter 2 slides');
        $this->assertStringContainsString('https://cdn.teste.ao/vertical-a.jpg', $html);
        $this->assertStringContainsString('https://cdn.teste.ao/vertical-b.jpg', $html);
        $this->assertStringContainsString('loading="eager"', $html);
        // o banner B (sem link) não gera <a>; o A sim
        $this->assertStringContainsString('https://a.teste.ao', $html);
    }

    /** FASE 18 — rotação limitada a 4 slides (os 4 mais recentes). */
    public function test_posicao_lateral_rotacao_maximo_quatro(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Banner::create([
                'titulo' => 'TEST Lateral ' . $i,
                'imagem_url' => 'https://cdn.teste.ao/v' . $i . '.jpg',
                'posicao' => 'lateral',
                'ativo' => true,
            ]);
        }

        $html = $this->render('lateral');

        $this->assertStringContainsString('banner-lateral-slider', $html);
        $this->assertSame(4, substr_count($html, 'swiper-slide'), 'devia limitar a 4 slides');
        // os 4 mais recentes (ids 5..2); o mais antigo (1) fica de fora
        $this->assertStringNotContainsString('v1.jpg', $html);
        $this->assertStringContainsString('v5.jpg', $html);
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
