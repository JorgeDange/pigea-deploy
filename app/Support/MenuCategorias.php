<?php

namespace App\Support;

use App\Models\Category;

/**
 * FASE 5.1 — dados do Mega Menu (menu.md §2-§4).
 *
 * Devolve as 9 secções de topo (rótulo + link) com as respetivas
 * subcategorias vindas da tabela `categories` (menu_group + order).
 *
 * Destinos:
 *  - subcategoria `type='news'`  → /noticias?cat=<slug>  (filtro real do
 *    NewsFilter — menu.md §5.4);
 *  - subcategoria `type='menu'`  → página existente, conforme o mapa
 *    DESTINOS abaixo (documentado como AJUSTÁVEL — as rotas alvo são as
 *    disponíveis pós-reestruturação; ver modificar.md §15);
 *  - sem entrada no mapa          → link da própria secção de topo.
 *
 * Uma só query por request (44 linhas indexadas por menu_group).
 */
final class MenuCategorias
{
    /**
     * Secções de topo na ordem do cliente (menu.md §2). `params` é o
     * parâmetro de rota quando o item de topo é um filtro de notícias.
     *
     * @var array<string, array{rotulo: string, rota: string, params?: array}>
     */
    public const GRUPOS = [
        'inicio' => ['rotulo' => 'Início', 'rota' => 'home'],
        'noticias' => ['rotulo' => 'Notícias', 'rota' => 'news.index'],
        'mineracao' => ['rotulo' => 'Mineração', 'rota' => 'news.index', 'params' => ['cat' => 'mineracao']],
        'petroleo-e-gas' => ['rotulo' => 'Petróleo e Gás', 'rota' => 'news.index', 'params' => ['cat' => 'petroleo-e-gas']],
        'geociencias' => ['rotulo' => 'Geociências', 'rota' => 'news.index', 'params' => ['cat' => 'geociencias']],
        'academia' => ['rotulo' => 'Academia', 'rota' => 'news.index', 'params' => ['cat' => 'academia']],
        'oportunidades' => ['rotulo' => 'Oportunidades', 'rota' => 'news.index', 'params' => ['cat' => 'oportunidades']],
        'directorio' => ['rotulo' => 'Directório', 'rota' => 'directory.index'],
        'sobre' => ['rotulo' => 'Sobre Nós', 'rota' => 'about'],
    ];

    /**
     * Destino das subcategorias de navegação (`type='menu'`): slug →
     * [rota, params]. AJUSTÁVEL — restrutura.md §6 define abas no
     * diretório (Empresas/Profissionais/Artigos).
     *
     * @var array<string, array<string, array{0: string, 1?: array}>>
     */
    private const DESTINOS = [
        'inicio' => [
            'destaques' => ['home'],
            'noticias-recentes' => ['news.index'],
            'empresas-em-destaque' => ['directory.index', ['aba' => 'empresas']],
            'oportunidades' => ['projects.index'],
        ],
        'noticias' => [
            'actualidade-nacional-e-internacional' => ['news.index'],
            'inovacao-e-tecnologia' => ['technologies.index'],
            'projectos-e-iniciativas' => ['projects.index'],
            'eventos' => ['events.index'],
        ],
        'academia' => [
            // Promoted to type=news in Fase 14 — fall back to /noticias?cat=<slug>
            'cursos-e-formacao' => ['news.index', ['cat' => 'cursos-e-formacao']],
            'workshops-e-webinars' => ['news.index', ['cat' => 'workshops-e-webinars']],
            'artigos-cientificos' => ['news.index', ['cat' => 'artigos-cientificos']],
            'livros-e-publicacoes' => ['news.index', ['cat' => 'livros-e-publicacoes']],
            'investigacao-academia' => ['news.index', ['cat' => 'investigacao-academia']],
        ],
        'oportunidades' => [
            // Promoted to type=news in Fase 14 — fall back to /noticias?cat=<slug>
            'emprego' => ['news.index', ['cat' => 'emprego']],
            'estagios' => ['news.index', ['cat' => 'estagios']],
            'bolsas-de-estudo' => ['news.index', ['cat' => 'bolsas-de-estudo']],
            'concursos' => ['news.index', ['cat' => 'concursos']],
            'consultorias' => ['news.index', ['cat' => 'consultorias']],
        ],
        'directorio' => [
            'empresas' => ['directory.index', ['aba' => 'empresas']],
            'profissionais' => ['directory.index', ['aba' => 'profissionais']],
            'dados-geoespaciais' => ['directory.index', ['aba' => 'documentos']],
            'equipamentos-e-servicos' => ['suppliers.index'],
        ],
        'sobre' => [
            'quem-somos' => ['about'],
            'missao-visao-e-valores' => ['about'],
            'equipa' => ['about'],
            'parceiros' => ['about'],
            'contactos' => ['contacts'],
        ],
    ];

    /**
     * Estrutura completa do menu: grupo → {rotulo, link, sublinks[]}.
     *
     * @return array<string, array{rotulo: string, link: string, sublinks: \Illuminate\Support\Collection<int, array{name: string, slug: string, url: string}>}>
     */
    public static function grupos(): array
    {
        $sublinks = Category::query()
            ->whereNotNull('menu_group')
            ->orderBy('menu_group')
            ->orderBy('order')
            ->get();

        $grupos = [];

        foreach (self::GRUPOS as $chave => $conf) {
            $link = route($conf['rota'], $conf['params'] ?? []);

            $grupos[$chave] = [
                'rotulo' => $conf['rotulo'],
                'link' => $link,
                'sublinks' => $sublinks
                    ->where('menu_group', $chave)
                    ->map(fn (Category $categoria) => [
                        'name' => $categoria->name,
                        'slug' => $categoria->slug,
                        'url' => self::url($categoria, $conf, $link),
                    ])
                    ->values(),
            ];
        }

        return $grupos;
    }

    /**
     * Grupo ativo a partir da rota/query atual — acende também o grupo
     * quando a listagem de notícias está filtrada por uma SUBcategoria
     * dele (ex.: /noticias?cat=prospeccao-e-pesquisa → Mineração).
     */
    public static function grupoAtivo(): string
    {
        $rota = request()->route()?->getName() ?? '';
        $cat = (string) request()->query('cat');

        $ativo = match (true) {
            $rota === 'home' => 'inicio',
            $rota === 'news.index' && $cat === 'mineracao' => 'mineracao',
            $rota === 'news.index' && $cat === 'petroleo-e-gas' => 'petroleo-e-gas',
            $rota === 'news.index' && $cat === 'geociencias' => 'geociencias',
            str_starts_with($rota, 'news.') => 'noticias',
            str_starts_with($rota, 'technologies.') => 'noticias',
            // Eventos vivem no painel "Notícias" (sublink "Eventos").
            str_starts_with($rota, 'events.') => 'noticias',
            str_starts_with($rota, 'training.') => 'academia',
            str_starts_with($rota, 'research.') => 'academia',
            str_starts_with($rota, 'projects.') => 'noticias',
            str_starts_with($rota, 'directory.') => 'directorio',
            str_starts_with($rota, 'suppliers.') => 'directorio',
            $rota === 'about' => 'sobre',
            $rota === 'contacts' => 'sobre',
            default => '',
        };

        // Subcategoria de notícias (ex.: ?cat=prospeccao-e-pesquisa) acende
        // o grupo a que pertence — as categorias de notícias do grupo de
        // topo já foram tratadas acima.
        if ($cat !== '' && $ativo === 'noticias') {
            $grupo = Category::query()
                ->where('type', 'news')
                ->where('slug', $cat)
                ->value('menu_group');

            // Top-levels Academia/Oportunidades (promovidas a news) não têm
            // menu_group (não são itens do Mega Menu) — mapear pelo slug.
            if (! is_string($grupo) || $grupo === '') {
                $grupo = isset(self::GRUPOS[$cat]) ? $cat : null;
            }

            if (is_string($grupo) && isset(self::GRUPOS[$grupo])) {
                $ativo = $grupo;
            }
        }

        return $ativo;
    }

    /**
     * URL de uma subcategoria.
     *
     * Regra: DESTINOS ganha SEMPRE (as categorias de menu_group
     * com destino conhecido são páginas do site; a mudança para
     * type=news não as faz ficar a apontar para /noticias?cat=…).
     *
     * @param  array{rotulo: string, rota: string, params?: array}  $confGrupo
     * @param  string  $linkGrupo  link da própria secção (fallback)
     */
    private static function url(Category $categoria, array $confGrupo, string $linkGrupo): string
    {
        $destino = self::DESTINOS[$categoria->menu_group][$categoria->slug] ?? null;

        if ($destino !== null) {
            return route($destino[0], $destino[1] ?? []);
        }

        // Sem destino conhecido: se for type=news, vai para o filtro.
        if ($categoria->type === 'news') {
            return route('news.index', ['cat' => $categoria->slug]);
        }

        // Sem destino conhecido e sem type=news: fallback para a página da secção.
        return $linkGrupo;
    }
}
