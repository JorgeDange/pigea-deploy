<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\News;
use App\Models\PageView;
use App\Models\Setting;
use App\Support\Provincias;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Página «Acompanhamento» — a sala de controlo das funcionalidades novas
 * (opções E/S 1..N). Mostra, num só ecrã:
 *   - Redes sociais configuradas (E3);
 *   - Banners na posição «Largo» (editoria_cms.md FASE 1);
 *   - Províncias — cobertura nos artigos publicados (E1);
 *   - Destaque editorial — quantos artigos pinados (editoria_cms.md FASE 2);
 *   - Qualidade dos artigos — sem imagem/etiquetas (sinais de alerta);
 *   - Vistas reais & Top 5 mais vistas (E2 — anti-bot + sessão).
 *
 * Só leitura: cada cartão aponta o botão «Corrigir…» para a página certa do
 * painel. Tudo real, sem gráficos falsos nem cache expirada (os contadores
 * são consultas leves num ecrã de pouca frequência).
 */
class AcompanhamentoController extends Controller
{
    public function index(): View
    {
        $agora = now();

        // ---- Redes sociais (E3) — 5 redes, mostram com link ou nome público
        $definicoes = Setting::todas();
        $redes = [
            'facebook' => ['nome' => 'Facebook', 'icone' => 'fab fa-facebook-f'],
            'instagram' => ['nome' => 'Instagram', 'icone' => 'fab fa-instagram'],
            'linkedin' => ['nome' => 'LinkedIn', 'icone' => 'fab fa-linkedin-in'],
            'youtube' => ['nome' => 'YouTube', 'icone' => 'fab fa-youtube'],
            'twitter' => ['nome' => 'X (Twitter)', 'icone' => 'fab fa-twitter'],
        ];

        $redesEstado = [];
        foreach ($redes as $chave => $dados) {
            $url = trim((string) ($definicoes[$chave] ?? ''));
            $handle = trim((string) ($definicoes[$chave . '_nome'] ?? ''));
            $redesEstado[$chave] = $dados + [
                'url' => $url,
                'handle' => $handle,
                'configurada' => $url !== '' || $handle !== '',
            ];
        }
        $redesConfiguradas = collect($redesEstado)->where('configurada', true)->count();

        // ---- Banners na posição «Largo» (editoria_cms.md FASE 1)
        $largo = [
            'ativos' => Banner::ativos()->where('posicao', 'largo')->count(),
            'total' => Banner::where('posicao', 'largo')->count(),
        ];

        // ---- Províncias (E1)
        $publicadas = News::published();
        $totalPub = $publicadas->count();
        $comProvincia = (clone $publicadas)->whereNotNull('province')->where('province', '<>', '')->count();
        $semProvincia = $totalPub - $comProvincia;
        $livres = (clone $publicadas)->whereNotNull('province')->where('province', '<>', '')
            ->whereNotIn('province', array_keys(Provincias::LISTA))
            ->count();
        $topProvincias = DB::table('news')
            ->where('status', 'published')
            ->whereNotNull('province')
            ->where('province', '<>', '')
            ->selectRaw('province, count(*) as n')
            ->groupBy('province')
            ->orderByDesc('n')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['rotulo' => Provincias::rotulo($row->province), 'n' => (int) $row->n]);

        // ---- Destaque editorial (editoria_cms.md FASE 2)
        $pins = (clone $publicadas)->where('destaque_editorial', true)->count();

        // ---- Qualidade (sinais de alerta)
        $semImagem = (clone $publicadas)->where(fn ($q) => $q->whereNull('image')->orWhere('image', ''))->count();
        $semTags = (clone $publicadas)->where(fn ($q) => $q->whereNull('tags')->orWhere('tags', ''))->count();

        // ---- Views (E2) — total e Top 5
        $totalVistas = (int) (clone $publicadas)->sum('views_count');
        $maisLidas = (clone $publicadas)
            ->orderByDesc('views_count')
            ->orderByDesc('published_at')
            ->take(5)
            ->get(['title', 'slug', 'views_count']);

        // ---- Visitas hoje (mesma métrica do Dashboard)
        $visitasHoje = (int) PageView::query()->where('date', $agora->toDateString())->value('visits');

        // ---- Resumo de atenção: quantos pontos precisam de ação
        $alertas = collect([
            $redesConfiguradas < 5,
            $largo['ativos'] === 0,
            $semProvincia > 0,
            $pins === 0,
            $semImagem > 0,
            $semTags > 0,
        ])->filter()->count();

        return view('admin.acompanhamento', [
            'redesEstado' => $redesEstado,
            'redesConfiguradas' => $redesConfiguradas,
            'largo' => $largo,
            'totalPub' => $totalPub,
            'comProvincia' => $comProvincia,
            'semProvincia' => $semProvincia,
            'livres' => $livres,
            'topProvincias' => $topProvincias,
            'pins' => $pins,
            'semImagem' => $semImagem,
            'semTags' => $semTags,
            'totalVistas' => $totalVistas,
            'maisLidas' => $maisLidas,
            'visitasHoje' => $visitasHoje,
            'alertas' => $alertas,
        ]);
    }
}
