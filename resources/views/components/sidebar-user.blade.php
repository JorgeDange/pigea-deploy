@php
    // Painel do utilizador — 6 itens do protótipo (painel-utilizador*.html).
    $rotaAtual = request()->route()?->getName() ?? '';
    $ativa     = str_starts_with($rotaAtual, 'user.') ? substr($rotaAtual, 5) : '';

    $itens = [
        ['chave' => 'dashboard',     'nome' => 'Dashboard',       'icone' => 'fa-tachometer-alt', 'rota' => 'user.dashboard'],
        ['chave' => 'perfil',        'nome' => 'Perfil',          'icone' => 'fa-user',           'rota' => 'user.perfil'],
        ['chave' => 'candidaturas',  'nome' => 'Candidaturas',    'icone' => 'fa-paper-plane',    'rota' => 'user.candidaturas'],
        ['chave' => 'guardadas',     'nome' => 'Vagas guardadas', 'icone' => 'fa-bookmark',       'rota' => 'user.guardadas'],
        ['chave' => 'notificacoes',  'nome' => 'Notificações',    'icone' => 'fa-bell',           'rota' => 'user.notificacoes', 'badge' => auth()->user()->notifications()->whereNull('read_at')->count()],
        ['chave' => 'definicoes',    'nome' => 'Definições',      'icone' => 'fa-cog',            'rota' => 'user.definicoes'],
    ];
@endphp
<aside class="dashboard-sidebar">
  <div class="sidebar-logo">
    <a href="{{ route('home') }}"><img src="{{ asset('images/logo2.png') }}" alt="PIGEA"></a>
  </div>
  <nav class="sidebar-nav">
    <ul>
      @foreach ($itens as $item)
      <li class="sidebar-item{{ $item['chave'] === $ativa ? ' active' : '' }}"><a href="{{ Route::has($item['rota']) ? route($item['rota']) : '#' }}"><i class="fa {{ $item['icone'] }}"></i> {{ $item['nome'] }}@if (isset($item['badge']) && $item['badge'] > 0)<span class="badge">{{ $item['badge'] }}</span>@endif</a></li>
      @endforeach
      <li class="sidebar-item sidebar-logout"><a href="#" onclick="event.preventDefault(); document.getElementById('form-sair').submit();"><i class="fa fa-sign-out"></i> Sair</a></li>
    </ul>
  </nav>
</aside>
