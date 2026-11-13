@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Dashboard</li></ol>
@endsection

@section('content')

{{-- Ação rápida: o botão grande "+ Nova Notícia" (cms_pro.md §4.1). --}}
<div class="quick-action card">
    <div class="quick-action-texto">
        <h2>Tens uma peça para publicar?</h2>
        <p>Escreve a próxima grande peça editorial — título, destaque, corpo com blocos especiais, capa, vídeo e documento.</p>
    </div>
    <a href="{{ route('admin.noticia-nova') }}" class="btn-primary btn-quick"><i class="fa fa-plus"></i> Nova Notícia</a>
</div>

{{-- Cartões de topo (cms_pro.md §4.1): Notícias Publicadas · Empresas
     Pendentes · Visitas no Site · Banners Ativos. O cartão de pendentes é
     um link direto para a página de aprovação. --}}
<div class="stats-grid">
    @foreach ($estatisticas as $estatistica)
        @php $href = $estatistica['href'] ?? null; @endphp
        @if ($href)
            <a class="stat-card stat-card-link" href="{{ $href }}">
        @else
            <div class="stat-card">
        @endif
            <div class="stat-card-icon"><i class="fa {{ $estatistica['icone'] }}"></i></div>
            <div class="stat-card-body">
                <div class="stat-card-number">{{ $estatistica['numero'] }}</div>
                <div class="stat-card-label">{{ $estatistica['rotulo'] }}</div>
                @if ($estatistica['rodape'] !== null)
                    <div class="stat-card-footer {{ $estatistica['rodape_class'] ?? '' }}">{{ $estatistica['rodape'] }}</div>
                @endif
            </div>
        @if ($href)</a>@else</div>@endif
    @endforeach
</div>

{{-- Gráficos. O central é as visitas dos últimos 7 dias (cms_pro.md §4.1).
     Chart.js vive em `public/js` (CSP script-src 'self'; nunca CDN). --}}
<div class="card">
    <div class="card-head">
        <h3 class="card-title">Gráficos</h3>
    </div>
    <div class="card-body">
        <div class="charts-grid">
            <div class="chart-box chart-box-principal">
                <h4 class="chart-titulo">Visitas do site — últimos 7 dias</h4>
                <canvas id="grafico-visitas7" aria-label="Visitas dos últimos 7 dias" role="img"></canvas>
            </div>
            <div class="chart-box">
                <h4 class="chart-titulo">Novos utilizadores por mês</h4>
                <canvas id="grafico-utilizadores" aria-label="Novos utilizadores por mês" role="img"></canvas>
            </div>
            <div class="chart-box">
                <h4 class="chart-titulo">Distribuição por província</h4>
                <canvas id="grafico-provincias" aria-label="Distribuição por província" role="img"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h3 class="card-title"><i class="fa fa-fire" aria-hidden="true" style="color:#C8102E;"></i> Mais lidas — Top 5</h3>
    </div>
    <div class="card-body">
        @if (count($maisLidas) === 0 || $maisLidas->every(fn ($n) => (int) $n->views_count === 0))
            <p class="chart-vazio">Ainda não há visitas registadas em notícias publicadas — as leituras começam a contar daqui para a frente (sem bots, mesma sessão a contar uma vez em 5 minutos).</p>
        @else
            <ol class="top5-list">
                @foreach ($maisLidas as $i => $n)
                <li class="top5-item">
                    <span class="top5-pos">{{ $i + 1 }}</span>
                    <a class="top5-title" href="{{ route('news.show', $n->slug) }}" target="_blank" rel="noopener">{{ $n->title }}</a>
                    <span class="top5-views"><i class="far fa-eye" aria-hidden="true"></i> {{ number_format((int) $n->views_count, 0, ',', '.') }}</span>
                </li>
                @endforeach
            </ol>
            <p class="helper-text" style="margin-top:10px">Visitas reais por leitura: bots/crawlers não contam; a mesma sessão conta uma vez a cada 5 minutos.</p>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h3 class="card-title">Atividade recente</h3>
        <div class="card-actions"><button type="button" class="btn-secondary btn-sm js-modal-open" data-modal="notificacoes-modal"><i class="fa fa-bell"></i> Ver todas</button></div>
    </div>
    <div class="card-body">
        @include('partials.notificacoes', ['itens' => $atividades])
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/chart.umd.js') }}" defer></script>
<script>
    // IIFE — os gráficos não poluem o escopo global. Chart.js carregado de
    // `public/js` (CSP script-src 'self' — nunca CDN) em defer; este código
    // corre no DOMContentLoaded (depois do Chart.js defer) — ordem garantida.
    document.addEventListener('DOMContentLoaded', () => {
        const opcoes = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        };

        // Visitas dos últimos 7 dias — se TUDO for 0, mostra nota vazia
        // em vez de uma linha plana em 0 (paridade com o gráfico de províncias).
        const visitas7 = @json($graficos['visitas7']);

        if (visitas7.every((v) => Number(v) === 0)) {
            const nota = document.createElement('p');
            nota.className = 'chart-vazio';
            nota.textContent = 'Sem visitas registadas ainda.';
            document.getElementById('grafico-visitas7').replaceWith(nota);
        } else {
            new Chart(document.getElementById('grafico-visitas7'), {
                type: 'line',
                data: {
                    labels: @json($graficos['rotulos7']),
                    datasets: [{
                        label: 'Visitas',
                        data: visitas7,
                        borderColor: '#C8102E',
                        backgroundColor: 'rgba(200, 16, 46, .12)',
                        fill: true,
                        tension: .35
                    }]
                },
                options: opcoes
            });
        }

        const meses = @json($graficos['rotulos']);

        new Chart(document.getElementById('grafico-utilizadores'), {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [{
                    label: 'Novos utilizadores',
                    data: @json($graficos['utilizadores']),
                    backgroundColor: '#C8102E'
                }]
            },
            options: opcoes
        });

        // Pie callado quando não há províncias com conteúdo — em vez de can­vas
        // branco/removido por confuso, mostra só a nota.
        const provincias = @json($graficos['provincias']);

        if (provincias.valores.length) {
            new Chart(document.getElementById('grafico-provincias'), {
                type: 'pie',
                data: {
                    labels: provincias.rotulos,
                    datasets: [{
                        data: provincias.valores,
                        backgroundColor: ['#C8102E', '#1A1A1A', '#666666', '#999999', '#B0B0B0', '#E0E0E0']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
                }
            });
        } else {
            const nota = document.createElement('p');
            nota.className = 'chart-vazio';
            nota.textContent = 'Sem províncias registadas no conteúdo publicado.';
            document.getElementById('grafico-provincias').replaceWith(nota);
        }
    });
</script>
@endpush
