<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Company;
use App\Models\News;
use App\Models\PageView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
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
    /** Admin (cms_pro.md §4.1): cartões de topo do dashboard. */
    /** @return array{estatisticas: list<array{icone: string, numero: int, rotulo: string, rodape: string|null, href?: string}>, atividades: list<array{icone: string, titulo: string, texto: string, tempo: string|null, href: string}>, graficos: array{rotulos: list<string>, conteudos: list<int>, utilizadores: list<int>, provincias: array{rotulos: list<string>, valores: list<int>}, visitas7: list<int>, rotulos7: list<string>}} */
    public function index(): View
    {
        $agora = now();

        // visitas hoje muda a cada visita — não vai para cache; o total é
        // agregado sobre uma tabela de 1 linha/dia, mas guarda 5 min de graça.
        $visitasHoje = (int) PageView::query()->where('date', $agora->toDateString())->value('visits');
        $visitasTotal = Cache::remember(
            'dashboard.visitas_total',
            now()->addMinutes(5),
            fn () => (int) PageView::query()->sum('visits')
        );

        $estatisticas = [
            [
                'icone' => 'fa-newspaper',
                'numero' => News::published()->count(),
                'rotulo' => 'Notícias publicadas',
                'rodape' => '+'.News::published()->where('published_at', '>=', $agora->copy()->startOfMonth())->count().' este mês',
            ],
            [
                'icone' => 'fa-building',
                'numero' => Company::pendente()->count(),
                'rotulo' => 'Empresas pendentes',
                'rodape' => 'aprovação no diretório →',
                'rodape_class' => 'pending',
                'href' => route('admin.empresas'),
            ],
            [
                'icone' => 'fa-eye',
                'numero' => $visitasTotal,
                'rotulo' => 'Visitas no Site',
                'rodape' => $visitasHoje.' hoje',
            ],
            [
                'icone' => 'fa-bullhorn',
                'numero' => Banner::ativos()->count(),
                'rotulo' => 'Banners ativos',
                'rodape' => 'visíveis no frontend',
            ],
        ];

        return view('admin.index', [
            'estatisticas' => $estatisticas,
            'atividades' => $this->atividades(),
            'graficos' => $this->graficos(),
        ]);
    }

    /** @return list<array{icone: string, titulo: string, texto: string, tempo: string|null, href: string}> */
    protected function atividades(): array
    {
        return \App\Support\NotificacoesPainel::obter();
    }

    /**
     * Séries dos gráficos (cms_pro.md §4.1 — gráfico central de 7 dias de
     * visitas + gráficos auxiliares já existentes no painel).
     *
     * @return array{rotulos7: list<string>, visitas7: list<int>, rotulos: list<string>, utilizadores: list<int>, provincias: array{rotulos: list<string>, valores: list<int>}}
     */
    protected function graficos(): array
    {
        $meses = $this->ultimosDozeMeses();
        $utilizadores = $this->utilizadoresPorMes();

        return [
            'rotulos7' => $this->ultimosSeteDias()['rotulos'],
            'visitas7' => $this->ultimosSeteDias()['valores'],
            'rotulos' => $meses['rotulos'],
            'utilizadores' => array_map(fn ($mes) => $utilizadores[$mes] ?? 0, $meses['chaves']),
            'provincias' => $this->provinciasDoConteudo(),
        ];
    }

    /**
     * Últimos 7 dias: chaves `Y-m-d` (para cruzar com `page_views.date`),
     * rótulos dd/MM e a série correspondente — um dia sem registos conta 0,
     * nunca falta nem salta no eixo.
     *
     * @return array{rotulos: list<string>, valores: list<int>}
     */
    protected function ultimosSeteDias(): array
    {
        $fim = now();

        $chaves = [];
        for ($i = 6; $i >= 0; $i--) {
            $chaves[] = $fim->copy()->subDays($i)->toDateString();
        }

        $mapa = PageView::query()
            ->whereIn('date', $chaves)
            ->pluck('visits', 'date');

        return [
            'rotulos' => array_map(fn ($chave) => (new \DateTime($chave))->format('d/m'), $chaves),
            'valores' => array_map(fn ($chave) => (int) ($mapa[$chave] ?? 0), $chaves),
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
     * Expressão SQL "ano-mês" da coluna, por motor: MySQL usa
     * DATE_FORMAT, SQLite usa strftime.
     */
    protected function expressaoMes(string $coluna): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$coluna})"
            : "DATE_FORMAT({$coluna}, '%Y-%m')";
    }

    /** Novos utilizadores por mês (conta por `created_at` de criação de conta). */
    protected function utilizadoresPorMes(): array
    {
        return DB::table('users')
            ->where('created_at', '>=', now()->subMonthsNoOverflow(11)->startOfMonth())
            ->selectRaw($this->expressaoMes('created_at')." as mes, count(*) as n")
            ->groupBy('mes')
            ->pluck('n', 'mes')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * Distribuição por província: apenas conteúdo PUBLICADO com província
     * preenchida (rascunhos e despublicados não contam para o gráfico).
     *
     * Das tabelas todas, só `news` e `projects` têm a coluna `province`.
     * Ordenado da mais para a menos representada.
     */
    protected function provinciasDoConteudo(): array
    {
        $contagem = [];

        foreach (['news', 'projects'] as $tabela) {
            DB::table($tabela)
                ->whereNotNull('province')
                ->where('status', 'published')
                ->selectRaw('province, count(*) as n')
                ->groupBy('province')
                ->pluck('n', 'province')
                ->each(function ($n, $provincia) use (&$contagem) {
                    $contagem[$provincia] = ($contagem[$provincia] ?? 0) + (int) $n;
                });
        }

        arsort($contagem);

        $rotulos = [
            'luanda' => 'Luanda',
            'benguela' => 'Benguela',
            'huila' => 'Huíla',
            'namibe' => 'Namibe',
            'nacional' => 'Nacional',
        ];

        return [
            'rotulos' => array_map(fn (string $p) => $rotulos[$p] ?? Str::headline($p), array_keys($contagem)),
            'valores' => array_values($contagem),
        ];
    }
}
