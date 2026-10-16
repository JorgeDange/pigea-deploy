@php
    // Ativo pela rota atual (admin.noticias -> "noticias"); as páginas de
    // criação acendem o item da respetiva lista (como no protótipo).
    $rotaAtual = request()->route()?->getName() ?? '';
    $ativa     = str_starts_with($rotaAtual, 'admin.') ? substr($rotaAtual, 6) : '';
    $ativa     = ['noticia-nova' => 'noticias', 'projeto-novo' => 'projetos'][$ativa] ?? $ativa;

    // Mesmos 11 itens do protótipo (painel admin) — todos com rota já; o
    // `Route::has` fica como salvaguarda para páginas ainda por migrar.
    $itens = [
        ['chave' => 'index',         'nome' => 'Dashboard',      'icone' => 'fa-tachometer-alt', 'rota' => 'admin.index'],
        ['chave' => 'noticias',      'nome' => 'Notícias',       'icone' => 'fa-newspaper',      'rota' => 'admin.noticias'],
        ['chave' => 'projetos',      'nome' => 'Projetos',       'icone' => 'fa-map-marked-alt', 'rota' => 'admin.projetos'],
        ['chave' => 'empresas',      'nome' => 'Empresas',       'icone' => 'fa-building',       'rota' => 'admin.empresas'],
        ['chave' => 'vagas',         'nome' => 'Vagas',          'icone' => 'fa-briefcase',      'rota' => 'admin.vagas'],
        ['chave' => 'eventos',       'nome' => 'Eventos',        'icone' => 'fa-calendar-alt',   'rota' => 'admin.eventos'],
        ['chave' => 'legislacao',    'nome' => 'Legislação',     'icone' => 'fa-gavel',          'rota' => 'admin.legislacao'],
        ['chave' => 'investigacao',  'nome' => 'Investigação',   'icone' => 'fa-flask',          'rota' => 'admin.investigacao'],
        ['chave' => 'entrevistas',   'nome' => 'Entrevistas',    'icone' => 'fa-microphone-alt', 'rota' => 'admin.entrevistas'],
        ['chave' => 'utilizadores',  'nome' => 'Utilizadores',   'icone' => 'fa-users',          'rota' => 'admin.utilizadores'],
        ['chave' => 'configuracoes', 'nome' => 'Configurações', 'icone' => 'fa-cog',            'rota' => 'admin.configuracoes'],
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
