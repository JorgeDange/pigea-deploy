<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Support\MenuCategorias;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE 5.1 — Mega Menu (menu.md §2-§5).
 *
 * Cobre: seeder das subcategorias (listas exatas do cliente), dados do
 * menu (App\Support\MenuCategorias), renderização do componente nos
 * públicos e ligação ao filtro de notícias (?cat=).
 */
class MegaMenuTest extends TestCase
{
    use RefreshDatabase;

    /** Contagens por grupo — listas exatas dadas pelo cliente (44 itens). */
    private const CONTAGENS = [
        'inicio' => 4,
        'noticias' => 4,
        'mineracao' => 5,
        'petroleo-e-gas' => 5,
        'geociencias' => 7,
        'academia' => 5,
        'oportunidades' => 5,
        'directorio' => 4,
        'sobre' => 5,
    ];

    // ------------------------------------------------------------------
    // Seeder (menu.md §4)
    // ------------------------------------------------------------------

    public function test_seeder_cria_as_subcategorias_exatas_nos_9_grupos(): void
    {
        $total = Category::whereNotNull('menu_group')->count();

        $this->assertSame(44, $total, 'O menu deve ter as 44 subcategorias do cliente.');

        foreach (self::CONTAGENS as $grupo => $esperado) {
            $this->assertSame(
                $esperado,
                Category::where('menu_group', $grupo)->count(),
                "Grupo {$grupo} devia ter {$esperado} subcategorias."
            );
        }
    }

    public function test_nomes_exatos_do_cliente(): void
    {
        foreach ([
            'Prospecção e pesquisa',
            'Avaliação de jazidas',
            'Geodesia e GNSS',
            'SIG e geoprocessamento',
            'Actualidade nacional e internacional',
            'Missão, visão e valores',
            'Equipamentos e serviços',
        ] as $nome) {
            $this->assertTrue(
                Category::where('menu_group', '!=', '')->where('name', $nome)->exists(),
                "Subcategoria exata em falta: {$nome}"
            );
        }
    }

    public function test_grupos_de_noticias_sao_type_news_e_penduram_no_pai(): void
    {
        foreach (['mineracao', 'petroleo-e-gas', 'geociencias'] as $grupo) {
            $pai = Category::where('type', 'news')->where('slug', $grupo)->firstOrFail();
            $subcategorias = Category::where('menu_group', $grupo)->get();

            $this->assertTrue(
                $subcategorias->every(fn (Category $c) => $c->type === 'news'),
                "Todas as subcategorias de {$grupo} deviam ser type=news (filtráveis)."
            );
            $this->assertTrue(
                $subcategorias->every(fn (Category $c) => $c->parent_id === $pai->id),
                "As subcategorias de {$grupo} deviam pendurar na categoria de notícias do grupo."
            );
        }
    }

    public function test_grupos_de_navegacao_sao_type_menu_sem_pai(): void
    {
        foreach (['inicio', 'noticias', 'directorio', 'sobre'] as $grupo) {
            $subcategorias = Category::where('menu_group', $grupo)->get();

            $this->assertTrue(
                $subcategorias->every(fn (Category $c) => $c->type === 'menu'),
                "As subcategorias de {$grupo} deviam ser type=menu (inertes nos selects)."
            );
            $this->assertTrue(
                $subcategorias->every(fn (Category $c) => $c->parent_id === null),
                "As subcategorias de navegação de {$grupo} não têm pai."
            );
        }
    }

    public function test_academia_oportunidades_sao_type_news_penduram_na_top_level(): void
    {
        foreach (['academia', 'oportunidades'] as $grupo) {
            // Top-level (news, sem menu_group para NÃO entrar no Mega Menu).
            $top = Category::where('type', 'news')->whereNull('parent_id')->where('slug', $grupo)->firstOrFail();
            $this->assertNull($top->menu_group, 'Top-level não devia ter menu_group (não é item do Mega Menu).');

            // Os 5 filhos do grupo são news e penduram da top-level.
            $filhos = Category::where('menu_group', $grupo)->get();
            $this->assertCount(5, $filhos, "Grupo {$grupo} devia ter 5 filhos.");
            $this->assertTrue(
                $filhos->every(fn (Category $c) => $c->type === 'news' && $c->parent_id === $top->id),
                "As subcategorias de {$grupo} deviam ser type=news penduradas na top-level."
            );
        }
    }

    public function test_ordem_e_sequencial_dentro_de_cada_grupo(): void
    {
        foreach (self::CONTAGENS as $grupo => $esperado) {
            $ordens = Category::where('menu_group', $grupo)
                ->orderBy('order')
                ->pluck('order')
                ->values();

            $this->assertSame(
                range(1, $esperado),
                $ordens->all(),
                "Ordem do grupo {$grupo} devia ser 1..{$esperado}."
            );
        }
    }

    public function test_seeder_e_idempotente(): void
    {
        $this->artisan('db:seed', ['--class' => \Database\Seeders\MenuSeeder::class, '--force' => true]);

        $this->assertSame(44, Category::whereNotNull('menu_group')->count());
    }

    // ------------------------------------------------------------------
    // Dados do menu (App\Support\MenuCategorias)
    // ------------------------------------------------------------------

    public function test_grupos_devolve_as_9_seccoes_com_links_de_topo(): void
    {
        $grupos = MenuCategorias::grupos();

        $this->assertSame(array_keys(self::CONTAGENS), array_keys($grupos));

        $this->assertSame('Início', $grupos['inicio']['rotulo']);
        $this->assertSame(route('home'), $grupos['inicio']['link']);
        $this->assertSame(route('news.index', ['cat' => 'mineracao']), $grupos['mineracao']['link']);
        // Academia e Oportunidades foram promovidas a type=news — topo aponta para o filtro.
        $this->assertSame(route('news.index', ['cat' => 'academia']), $grupos['academia']['link']);
        $this->assertSame(route('news.index', ['cat' => 'oportunidades']), $grupos['oportunidades']['link']);
        $this->assertSame(route('directory.index'), $grupos['directorio']['link']);
        $this->assertSame(route('about'), $grupos['sobre']['link']);
    }

    public function test_subcategorias_de_noticias_filtram_por_cat(): void
    {
        $grupos = MenuCategorias::grupos();

        $urls = $grupos['mineracao']['sublinks']->pluck('url', 'slug');
        $this->assertSame(route('news.index', ['cat' => 'prospeccao-e-pesquisa']), $urls['prospeccao-e-pesquisa']);

        $urls = $grupos['geociencias']['sublinks']->pluck('url', 'slug');
        $this->assertSame(route('news.index', ['cat' => 'geodesia-e-gnss']), $urls['geodesia-e-gnss']);
    }

    public function test_destinos_das_subcategorias_de_navegacao(): void
    {
        $grupos = MenuCategorias::grupos();

        $urls = $grupos['noticias']['sublinks']->pluck('url', 'slug');
        $this->assertSame(route('technologies.index'), $urls['inovacao-e-tecnologia']);
        $this->assertSame(route('events.index'), $urls['eventos']);

        $urls = $grupos['academia']['sublinks']->pluck('url', 'slug');
        $this->assertSame(route('news.index', ['cat' => 'cursos-e-formacao']), $urls['cursos-e-formacao']);
        $this->assertSame(route('news.index', ['cat' => 'artigos-cientificos']), $urls['artigos-cientificos']);

        $urls = $grupos['directorio']['sublinks']->pluck('url', 'slug');
        $this->assertSame(route('directory.index', ['aba' => 'empresas']), $urls['empresas']);
        $this->assertSame(route('directory.index', ['aba' => 'profissionais']), $urls['profissionais']);
        $this->assertSame(route('suppliers.index'), $urls['equipamentos-e-servicos']);

        $urls = $grupos['sobre']['sublinks']->pluck('url', 'slug');
        $this->assertSame(route('contacts'), $urls['contactos']);

        // Oportunidades promovidas a news — cada sublink filtra a sua categoria.
        $urls = $grupos['oportunidades']['sublinks']->pluck('url', 'slug');
        foreach (['emprego', 'estagios', 'bolsas-de-estudo', 'concursos', 'consultorias'] as $slug) {
            $this->assertSame(route('news.index', ['cat' => $slug]), $urls[$slug]);
        }
    }

    public function test_subcategoria_nova_sem_destino_cai_no_link_da_seccao(): void
    {
        Category::create([
            'name' => 'Subcategoria Futura',
            'slug' => 'subcategoria-futura',
            'type' => 'menu',
            'menu_group' => 'sobre',
            'order' => 6,
        ]);

        $grupos = MenuCategorias::grupos();

        $urls = $grupos['sobre']['sublinks']->pluck('url', 'slug');
        $this->assertSame(route('about'), $urls['subcategoria-futura']);
    }

    // ------------------------------------------------------------------
    // Renderização (header em todas as páginas públicas)
    // ------------------------------------------------------------------

    public function test_home_renderiza_o_mega_menu_completo(): void
    {
        $resposta = $this->get('/');
        $resposta->assertOk();

        $html = $resposta->getContent();

        // 9 itens de topo com chevron.
        $this->assertSame(9, substr_count($html, 'mm-topo'));

        // Painéis: pura grelha de links, sem cabeçalho descritivo
        // (mm-panel-cab removido por decisão do cliente — 11/11/2026).
        $this->assertSame(0, substr_count($html, 'mm-panel-cab'));
        $this->assertStringNotContainsString('Portal de Geociências', $html);

        // Grelha com os 44 links de subcategorias.
        $this->assertSame(44, substr_count($html, 'class="mm-link"'));

        // Início ativo na home.
        $this->assertStringContainsString('mm-grupo-inicio mm-item-ativo', $html);

        // Exemplos dos destinos mapeados.
        $this->assertStringContainsString('/noticias?cat=prospeccao-e-pesquisa', $html);
        $this->assertStringContainsString('/directorio?aba=empresas', $html);
        $this->assertStringContainsString('/fornecedores', $html);
    }

    public function test_subcategoria_de_noticias_acende_o_grupo_respetivo(): void
    {
        $html = $this->get('/noticias?cat=geologia')->getContent();

        $this->assertStringContainsString('mm-grupo-geociencias mm-item-ativo', $html);
        $this->assertStringNotContainsString('mm-grupo-inicio mm-item-ativo', $html);
    }

    public function test_filtro_de_subcategoria_de_grupo_promovido_acende_o_grupo(): void
    {
        $academia = $this->get('/noticias?cat=cursos-e-formacao')->getContent();
        $this->assertStringContainsString('mm-grupo-academia mm-item-ativo', $academia);

        $oportunidades = $this->get('/noticias?cat=emprego')->getContent();
        $this->assertStringContainsString('mm-grupo-oportunidades mm-item-ativo', $oportunidades);
    }

    public function test_filtro_de_grupo_promovido_acende_o_item_de_topo(): void
    {
        $academia = $this->get('/noticias?cat=academia')->getContent();
        $this->assertStringContainsString('mm-grupo-academia mm-item-ativo', $academia);

        $oportunidades = $this->get('/noticias?cat=oportunidades')->getContent();
        $this->assertStringContainsString('mm-grupo-oportunidades mm-item-ativo', $oportunidades);
    }

    public function test_filtro_de_grupo_de_topo_continua_a_acender_o_item(): void
    {
        $html = $this->get('/noticias?cat=petroleo-e-gas')->getContent();

        $this->assertStringContainsString('mm-grupo-petroleo-e-gas mm-item-ativo', $html);
    }

    public function test_menu_esta_em_todas_as_paginas_publicas(): void
    {
        foreach (['/noticias', '/formacao', '/directorio', '/sobre', '/contactos'] as $url) {
            $html = $this->get($url)->getContent();

            $this->assertStringContainsString('mm-bar', $html, "Menu em falta em {$url}.");
        }
    }

    // ------------------------------------------------------------------
    // Ligação ao admin (menu.md §4: associar notícias às categorias)
    // ------------------------------------------------------------------

    public function test_subcategorias_de_noticias_estao_no_formulario_do_admin(): void
    {
        $admin = User::where('email', 'test@example.com')->firstOrFail();

        $html = $this->actingAs($admin)->get(route('admin.noticia-nova'))->getContent();

        // type=news → o admin pode associar notícias às subcategorias do menu.
        $this->assertStringContainsString('Prospecção e pesquisa', $html);
        $this->assertStringContainsString('Geodesia e GNSS', $html);

        // type=menu → inerte nos selects de conteúdo.
        $this->assertStringNotContainsString('Missão, visão e valores', $html);
    }
}
