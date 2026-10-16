@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Dashboard</li></ul>
@endsection

@section('content')
<div class="stats-grid">
  @foreach ($estatisticas as $estatistica)
  <div class="stat-card">
    <div class="stat-card-icon"><i class="fa {{ $estatistica['icone'] }}"></i></div>
    <div class="stat-card-body">
      <div class="stat-card-number">{{ $estatistica['numero'] }}</div>
      <div class="stat-card-label">{{ $estatistica['rotulo'] }}</div>
      @if ($estatistica['rodape'] !== null)
      <div class="stat-card-footer">{{ $estatistica['rodape'] }}</div>
      @endif
    </div>
  </div>
  @endforeach
</div>
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Últimas atividades</h3>
  </div>
  <div class="card-body">
    <div class="notification-list">
      @forelse ($atividades as $indice => $atividade)
      <div class="notification-item{{ $indice < 2 ? ' unread' : '' }}">
        <div class="notification-icon"><i class="fa {{ $atividade['icone'] }}"></i></div>
        <div class="notification-content"><p><strong>{{ $atividade['titulo'] }}</strong> — {{ $atividade['texto'] }}</p></div>
        <div class="notification-time">{{ $atividade['tempo'] }}</div>
      </div>
      @empty
      <div class="notification-item">
        <div class="notification-content"><p>Sem atividade recente.</p></div>
      </div>
      @endforelse
    </div>
  </div>
</div>
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Conteúdo pendente</h3>
    <div class="card-actions"><a href="#" class="btn-ghost btn-sm">Ver tudo</a></div>
  </div>
  <div class="card-body">
    <div class="table-wrap">
      <table class="dashboard-table">
        <thead><tr><th>Conteúdo</th><th>Tipo</th><th>Autor</th><th>Submetido</th><th>Ações</th></tr></thead>
        <tbody>
          @forelse ($pendentes as $pendente)
          <tr><td>{{ $pendente['titulo'] }}</td><td>{{ $pendente['tipo'] }}</td><td>{{ $pendente['autor'] }}</td><td>{{ $pendente['submetido'] }}</td><td><div class="row-actions"><button class="icon-btn " title="Aprovar"><i class="fa fa-check"></i></button><button class="icon-btn danger" title="Rejeitar"><i class="fa fa-times"></i></button><button class="icon-btn " title="Ver"><i class="fa fa-eye"></i></button></div></td></tr>
          @empty
          <tr><td colspan="5">Sem conteúdo pendente.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Gráficos</h3>
  </div>
  <div class="card-body">
    <div class="charts-grid">
      {{-- Mesmas 3 caixas do `admin/index.html`; o rótulo "reservado para
           Chart.js" deixa de fazer sentido — passam a ser gráficos reais.
           O Chart.js vive em `public/js/` (não num CDN) porque a CSP é
           `script-src 'self'`: um CDN seria sinalizado em cada carregamento. --}}
      <div class="chart-box">
        <h4 class="chart-titulo">Conteúdos publicados por mês</h4>
        <canvas id="grafico-conteudos" aria-label="Conteúdos publicados por mês" role="img"></canvas>
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

@push('scripts')
<script src="{{ asset('js/chart.umd.js') }}"></script>
<script>
  // IIFE — os gráficos não poluem o escopo global (a página carrega jQuery +
  // dashboard.js e `rotulos`/`opcoes` são nomes genéricos demais para irem
  // parar a `window`).
  (() => {
    // O eixo x tem sempre os 12 meses — os meses sem registos vêm a 0 do
    // DashboardController, por isso o eixo nunca salta nem cria picos falsos.
    const rotulos = @json($graficos['rotulos']);
    const opcoes = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } }
    };

    new Chart(document.getElementById('grafico-conteudos'), {
      type: 'line',
      data: {
        labels: rotulos,
        datasets: [{
          label: 'Conteúdos publicados',
          data: @json($graficos['conteudos']),
          borderColor: '#C8102E',
          backgroundColor: 'rgba(200, 16, 46, .12)',
          fill: true,
          tension: .3
        }]
      },
      options: opcoes
    });

    new Chart(document.getElementById('grafico-utilizadores'), {
      type: 'bar',
      data: {
        labels: rotulos,
        datasets: [{
          label: 'Novos utilizadores',
          data: @json($graficos['utilizadores']),
          backgroundColor: '#C8102E'
        }]
      },
      options: opcoes
    });

    // O protótipo chama-lhe "pizza" — é `pie`, não `doughnut`. Sem províncias
    // registadas no conteúdo mostra uma nota em vez de um gráfico vazio (uma
    // pizza de zero ou de uma fatia não comunica nada).
    const provincias = @json($graficos['provincias']);

    if (provincias.valores.length) {
      new Chart(document.getElementById('grafico-provincias'), {
        type: 'pie',
        data: {
          labels: provincias.rotulos,
          datasets: [{
            data: provincias.valores,
            backgroundColor: ['#C8102E', '#2B2B2B', '#8A8A8A', '#E0A0A8', '#5C5C5C', '#C9C9C9', '#7A1524', '#B0B0B0']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } }
        }
      });
    } else {
      document.getElementById('grafico-provincias')
        .insertAdjacentHTML('afterend', '<p class="chart-vazio">Sem províncias registadas no conteúdo.</p>');
    }
  })();
</script>
@endpush
@endsection
