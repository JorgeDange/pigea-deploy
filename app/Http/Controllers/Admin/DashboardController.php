<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Directory;
use App\Models\Event;
use App\Models\Interview;
use App\Models\Job;
use App\Models\Legislation;
use App\Models\News;
use App\Models\Project;
use App\Models\Research;
use App\Models\Training;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Dashboard do painel admin (protótipo admin/index.html).
 *
 * Os números do protótipo eram fixos (142, 312, 1.480…); aqui passam a vir
 * da base de dados, mantendo o mesmo markup.
 */
class DashboardController extends Controller
{
    /** Modelos com estado de publicação (para "Conteúdo pendente"). */
    protected array $modelosConteudo = [
        News::class, Project::class, Event::class, Job::class,
        Legislation::class, Research::class, Interview::class,
        Training::class,
    ];

    public function index(): View
    {
        $agora = now();
        $pendentes = $this->pendentes();

        $estatisticas = [
            [
                'icone' => 'fa-newspaper',
                'numero' => News::published()->count(),
                'rotulo' => 'Notícias publicadas',
                'rodape' => '+'.News::published()->where('published_at', '>=', $agora->copy()->startOfMonth())->count().' este mês',
            ],
            [
                'icone' => 'fa-map-marked-alt',
                'numero' => Project::published()->count(),
                'rotulo' => 'Projetos ativos',
                'rodape' => '+'.Project::published()->where('published_at', '>=', $agora->copy()->startOfMonth())->count().' este mês',
            ],
            [
                'icone' => 'fa-building',
                'numero' => Directory::count(),
                'rotulo' => 'Empresas registadas',
                'rodape' => '+'.Directory::where('created_at', '>=', $agora->copy()->startOfMonth())->count().' este mês',
            ],
            [
                'icone' => 'fa-briefcase',
                'numero' => Job::published()->count(),
                'rotulo' => 'Vagas ativas',
                'rodape' => null,
            ],
            [
                'icone' => 'fa-users',
                'numero' => User::count(),
                'rotulo' => 'Utilizadores',
                'rodape' => '+'.User::where('created_at', '>=', $agora->copy()->startOfMonth())->count().' este mês',
            ],
            [
                'icone' => 'fa-hourglass-half',
                'numero' => count($pendentes),
                'rotulo' => 'Conteúdo pendente',
                'rodape' => 'requer aprovação',
            ],
        ];

        return view('admin.index', [
            'estatisticas' => $estatisticas,
            'atividades' => $this->atividades(),
            'pendentes' => $pendentes,
            'graficos' => $this->graficos(),
        ]);
    }

    /**
     * Últimos registos por tipo — mesma lista de 5 itens do protótipo.
     *
     * @return list<array{icone: string, titulo: string, texto: string, tempo: string}>
     */
    protected function atividades(): array
    {
        $itens = [];

        if ($empresa = Directory::latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-building',
                'titulo' => 'Nova empresa registada',
                'texto' => $empresa->title.' aguarda aprovação no diretório.',
                'tempo' => $empresa->created_at?->diffForHumans(),
            ];
        }

        if ($noticia = News::latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-newspaper',
                'titulo' => 'Nova notícia submetida',
                'texto' => '“'.$noticia->title.'” enviada para revisão.',
                'tempo' => $noticia->created_at?->diffForHumans(),
            ];
        }

        if ($utilizador = User::latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-user-plus',
                'titulo' => 'Novo utilizador',
                'texto' => $utilizador->name.' criou conta.',
                'tempo' => $utilizador->created_at?->diffForHumans(),
            ];
        }

        if ($vaga = Job::latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-briefcase',
                'titulo' => 'Nova vaga publicada',
                'texto' => '“'.$vaga->title.'” entrou no emprego científico.',
                'tempo' => $vaga->created_at?->diffForHumans(),
            ];
        }

        if ($projeto = Project::latest('created_at')->first()) {
            $itens[] = [
                'icone' => 'fa-handshake',
                'titulo' => 'Nova oportunidade',
                'texto' => '“'.$projeto->title.'” publicado em projetos.',
                'tempo' => $projeto->created_at?->diffForHumans(),
            ];
        }

        return $itens;
    }

    /**
     * Registos ainda não publicados (tabela "Conteúdo pendente").
     *
     * @return list<array{titulo: string, tipo: string, autor: string, submetido: string, modelo: string, id: int|string}>
     */
    protected function pendentes(): array
    {
        $rotulos = [
            News::class => 'Notícia',
            Project::class => 'Projeto',
            Event::class => 'Evento',
            Job::class => 'Vaga',
            Legislation::class => 'Legislação',
            Research::class => 'Investigação',
            Interview::class => 'Entrevista',
            Training::class => 'Formação',
        ];

        $pendentes = [];

        foreach ($this->modelosConteudo as $modelo) {
            foreach ($modelo::where('status', '!=', 'published')->latest('created_at')->limit(5)->get() as $registo) {
                $pendentes[] = [
                    'titulo' => $registo->title,
                    'tipo' => $rotulos[$modelo] ?? 'Conteúdo',
                    'autor' => $registo->author?->name ?? 'Registo público',
                    'submetido' => $registo->created_at?->format('d/m/Y') ?? '—',
                    'modelo' => $modelo,
                    'id' => $registo->id,
                    'ordenacao' => $registo->created_at?->getTimestamp() ?? 0,
                ];
            }
        }

        usort($pendentes, fn ($a, $b) => $b['ordenacao'] <=> $a['ordenacao']);

        return array_slice($pendentes, 0, 5);
    }

    /**
     * Séries dos 3 gráficos do protótipo — no `admin/index.html` todos vinham
     * marcados "reservado para Chart.js"; aqui deixam de o estar.
     *
     * Os eixos x (linha e barras) têm **sempre** os 12 meses, com 0 nos meses
     * sem registos: um eixo com meses em falta salta e faz parecer que há
     * picos onde não há.
     *
     * As contagens usam `DATE_FORMAT` (MySQL/MariaDB) — é o único motor
     * suportado pela aplicação (o `phpunit.xml` força `mysql`).
     *
     * @return array{rotulos: list<string>, conteudos: list<int>, utilizadores: list<int>, provincias: array{rotulos: list<string>, valores: list<int>}}
     */
    protected function graficos(): array
    {
        $meses = $this->ultimosDozeMeses();

        $publicados = $this->conteudoPublicadoPorMes();
        $utilizadores = $this->utilizadoresPorMes();
        $provincias = $this->provinciasDoConteudo();

        return [
            'rotulos' => $meses['rotulos'],
            'conteudos' => array_map(fn ($mes) => $publicados[$mes] ?? 0, $meses['chaves']),
            'utilizadores' => array_map(fn ($mes) => $utilizadores[$mes] ?? 0, $meses['chaves']),
            'provincias' => $provincias,
        ];
    }

    /**
     * As 12 chaves de mês (`Y-m`, para casar com o `DATE_FORMAT`) e os
     * rótulos legíveis ("set 26"), do mais antigo para o mais recente.
     *
     * @return array{chaves: list<string>, rotulos: list<string>}
     */
    protected function ultimosDozeMeses(): array
    {
        $datas = collect(range(11, 0))
            ->map(fn (int $mesesAtras) => now()->subMonthsNoOverflow($mesesAtras));

        return [
            'chaves' => $datas->map(fn ($data) => $data->format('Y-m'))->all(),
            'rotulos' => $datas->map(fn ($data) => $data->locale('pt')->translatedFormat('M y'))->all(),
        ];
    }

    /**
     * Conteúdos publicados por mês, somados sobre **todas** as tabelas com
     * estado de publicação (a mesma lista dos cartões e da tabela pendente).
     *
     * @return array<string, int> chave `Y-m` → contagem
     */
    protected function conteudoPublicadoPorMes(): array
    {
        $inicio = now()->subMonthsNoOverflow(11)->startOfMonth();
        $contagem = [];

        foreach ($this->modelosConteudo as $modelo) {
            $tabela = $modelo::query()->getModel()->getTable();

            DB::table($tabela)
                ->whereNotNull('published_at')
                ->where('published_at', '>=', $inicio)
                ->selectRaw("DATE_FORMAT(published_at, '%Y-%m') as mes, count(*) as n")
                ->groupBy('mes')
                ->pluck('n', 'mes')
                ->each(function ($n, $mes) use (&$contagem) {
                    $contagem[$mes] = ($contagem[$mes] ?? 0) + (int) $n;
                });
        }

        return $contagem;
    }

    /** Novos utilizadores por mês (conta por `created_at` de criação de conta). */
    protected function utilizadoresPorMes(): array
    {
        return DB::table('users')
            ->where('created_at', '>=', now()->subMonthsNoOverflow(11)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as mes, count(*) as n")
            ->groupBy('mes')
            ->pluck('n', 'mes')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * Distribuição por província: onde está o conteúdo que tem província.
     *
     * Das tabelas todas, só `news`, `projects` e `vacancies` têm a coluna
     * `province`. Ordenado da mais para a menos representada.
     */
    protected function provinciasDoConteudo(): array
    {
        $contagem = [];

        foreach (['news', 'projects', 'vacancies'] as $tabela) {
            DB::table($tabela)
                ->whereNotNull('province')
                ->selectRaw('province, count(*) as n')
                ->groupBy('province')
                ->pluck('n', 'province')
                ->each(function ($n, $provincia) use (&$contagem) {
                    $contagem[$provincia] = ($contagem[$provincia] ?? 0) + (int) $n;
                });
        }

        arsort($contagem);

        // Os valores são slugs ("cuando-cubango"); os rótulos seguem a mesma
        // convenção dos filtros do painel — maiúsculas por palavra.
        return [
            'rotulos' => array_map(fn (string $p) => Str::headline($p), array_keys($contagem)),
            'valores' => array_values($contagem),
        ];
    }
}
