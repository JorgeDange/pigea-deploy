@php
    // Painel da empresa — 7 itens do protótipo (painel-empresa*.html).
    $rotaAtual = request()->route()?->getName() ?? '';
    $ativa     = str_starts_with($rotaAtual, 'company.') ? substr($rotaAtual, 8) : '';
    $ativa     = ['vaga-nova' => 'vagas'][$ativa] ?? $ativa;

    $itens = [
        ['chave' => 'dashboard',      'nome' => 'Dashboard',          'icone' => 'fa-tachometer-alt', 'rota' => 'company.dashboard'],
        ['chave' => 'perfil',         'nome' => 'Perfil da empresa',  'icone' => 'fa-building',       'rota' => 'company.perfil'],
        ['chave' => 'vagas',          'nome' => 'Vagas',              'icone' => 'fa-briefcase',      'rota' => 'company.vagas'],
        ['chave' => 'candidaturas',   'nome' => 'Candidaturas',       'icone' => 'fa-users',          'rota' => 'company.candidaturas'],
        ['chave' => 'oportunidades',  'nome' => 'Oportunidades',      'icone' => 'fa-handshake',      'rota' => 'company.oportunidades'],
        ['chave' => 'estatisticas',   'nome' => 'Estatísticas',       'icone' => 'fa-chart-bar',      'rota' => 'company.estatisticas'],
        ['chave' => 'definicoes',     'nome' => 'Definições',         'icone' => 'fa-cog',            'rota' => 'company.definicoes'],
    ];
@endphp
<aside class="dashboard-sidebar">
  <div class="sidebar-logo">
    <a href="{{ route('home') }}"><img src="{{ asset('images/logo2.png') }}" alt="PIGEA"></a>
  </div>
  <nav class="sidebar-nav">
    <ul>
      @foreach ($itens as $item)
      <li class="sidebar-item{{ $item['chave'] === $ativa ? ' active' : '' }}"><a href="{{ Route::has($item['rota']) ? route($item['rota']) : '#' }}"><i class="fa {{ $item['icone'] }}"></i> {{ $item['nome'] }}</a></li>
      @endforeach
      <li class="sidebar-item sidebar-logout"><a href="#" onclick="event.preventDefault(); document.getElementById('form-sair').submit();"><i class="fa fa-sign-out"></i> Sair</a></li>
    </ul>
  </nav>
</aside>
