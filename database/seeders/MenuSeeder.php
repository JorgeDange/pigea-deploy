<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * FASE 5.1 — subcategorias do Mega Menu (menu.md §4; listas exatas dadas
 * pelo cliente em 11/11/2026 — 44 itens nas 9 secções).
 *
 * Tipos:
 *  - `type='news'`: subcategorias FILTRÁVEIS — o painel liga a
 *    /noticias?cat=<slug> e o admin pode associar notícias a elas
 *    (NewsFormController lista `type='news'`). Grupos: Mineração,
 *    Petróleo e Gás, Geociências. `parent_id` aponta para a categoria de
 *    notícias do próprio grupo (já criada pelo ContentSeeder).
 *  - `type='menu'`: links de NAVEGAÇÃO para páginas existentes
 *    (formação, eventos, diretório…) — inertes para os selects de
 *    conteúdo do admin. `parent_id` NULL.
 *
 * Idempotente (updateOrCreate por menu_group+slug) — pode correr de novo
 * sem duplicar; `order` é a posição na grelha (1..n por grupo).
 */
class MenuSeeder extends Seeder
{
    /**
     * Grupo → tipo + subcategorias, na ordem exata definida pelo cliente.
     *
     * @var array<string, array{tipo: string, itens: list<string>}>
     */
    private const GRUPOS = [
        'inicio' => [
            'tipo' => 'menu',
            'itens' => [
                'Destaques',
                'Notícias recentes',
                'Empresas em destaque',
                'Oportunidades',
            ],
        ],
        'noticias' => [
            'tipo' => 'menu',
            'itens' => [
                'Actualidade nacional e internacional',
                'Inovação e tecnologia',
                'Projectos e iniciativas',
                'Eventos',
            ],
        ],
        'mineracao' => [
            'tipo' => 'news',
            'itens' => [
                'Prospecção e pesquisa',
                'Exploração mineira',
                'Avaliação de jazidas',
                'Processamento mineral',
                'Ambiente mineiro',
            ],
        ],
        'petroleo-e-gas' => [
            'tipo' => 'news',
            'itens' => [
                'Exploração e produção',
                'Geologia e geofísica',
                'Perfuração',
                'Engenharia de reservatórios',
                'Energia',
            ],
        ],
        'geociencias' => [
            'tipo' => 'news',
            'itens' => [
                'Geologia',
                'Geofísica',
                'Geodesia e GNSS',
                'Topografia e cartografia',
                'SIG e geoprocessamento',
                'Hidrogeologia',
                'Geotecnia e ambiente',
            ],
        ],
        'academia' => [
            'tipo' => 'news', // Fase 14: promovada a notícias
            'slugs' => ['Investigação' => 'investigacao-academia'],
            'itens' => [
                'Cursos e formação',
                'Workshops e webinars',
                'Artigos científicos',
                'Livros e publicações',
                'Investigação',
            ],
        ],
        'oportunidades' => [
            'tipo' => 'news', // Fase 14: promovada a notícias
            'itens' => [
                'Emprego',
                'Estágios',
                'Bolsas de estudo',
                'Concursos',
                'Consultorias',
            ],
        ],
        'directorio' => [
            'tipo' => 'menu',
            'itens' => [
                'Empresas',
                'Profissionais',
                'Dados geoespaciais',
                'Equipamentos e serviços',
            ],
        ],
        'sobre' => [
            'tipo' => 'menu',
            'itens' => [
                'Quem somos',
                'Missão, visão e valores',
                'Equipa',
                'Parceiros',
                'Contactos',
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::GRUPOS as $grupo => $conf) {
            // As subcategorias de notícias penduram na top-level news do
            // próprio grupo (ContentSeeder para mineracao/petroleo/geociencias;
            // migração Fase 14 para academia/oportunidades).
            $pai = $conf['tipo'] === 'news'
                ? Category::query()->where('type', 'news')->where('slug', $grupo)->first()
                : null;

            foreach ($conf['itens'] as $i => $nome) {
                $slug = $conf['slugs'][$nome] ?? Str::slug($nome);

                Category::updateOrCreate(
                    ['menu_group' => $grupo, 'slug' => $slug],
                    [
                        'name' => $nome,
                        'type' => $conf['tipo'],
                        'parent_id' => $pai?->id,
                        'order' => $i + 1,
                    ]
                );
            }
        }
    }
}
