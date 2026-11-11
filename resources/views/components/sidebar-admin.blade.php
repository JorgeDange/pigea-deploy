@php
    // cms_pro.md §2/§3 — sidebar em GRUPOS pequenos, com cabeçalhos.
    // O grupo SISTEMA (Utilizadores, Configurações) só aparece para `admin`
    // (restrutura.md: editor não gira utilizadores nem configurações).
    $rotaAtual = request()->route()?->getName() ?? '';
    $ativa     = str_starts_with($rotaAtual, 'admin.') ? substr($rotaAtual, 6) : '';
    $ativa     = [
        'noticia-nova'     => 'noticias',
        'noticia-editar'   => 'noticias',
        'documentos.novo'  => 'documentos',
        'documentos.editar' => 'documentos',
        'banners.novo'     => 'banners',
        'banners.editar'   => 'banners',
        'conteudo.editar'  => $rotaAtual === 'admin.conteudo.editar' && request('tipo') === 'noticias' ? 'noticias' : '',
    ][$ativa] ?? $ativa;

    $ehAdmin = auth()->user()?->hasRole('admin') ?? false;

    // Mesmos 9 itens do cms_pro.md, agrupados nos 5 blocos do spec.
    $grupos = [
        ['rotulo' => 'Principal', 'itens' => [
            ['chave' => 'index', 'nome' => 'Dashboard', 'icone' => 'fa-tachometer-alt', 'rota' => 'admin.index', 'can' => null],
        ]],
        ['rotulo' => 'Produção', 'itens' => [
            ['chave' => 'noticias', 'nome' => 'Notícias', 'icone' => 'fa-newspaper', 'rota' => 'admin.noticias', 'can' => 'news.manage'],
        ]],
        ['rotulo' => 'Diretório', 'itens' => [
            ['chave' => 'empresas', 'nome' => 'Empresas', 'icone' => 'fa-building', 'rota' => 'admin.empresas', 'can' => 'company.moderate'],
            ['chave' => 'profissionais', 'nome' => 'Profissionais', 'icone' => 'fa-user-tie', 'rota' => 'admin.profissionais', 'can' => 'company.moderate'],
            ['chave' => 'documentos', 'nome' => 'Documentos', 'icone' => 'fa-file-alt', 'rota' => 'admin.documentos', 'can' => 'document.manage'],
        ]],
        ['rotulo' => 'Comercial', 'itens' => [
            ['chave' => 'banners', 'nome' => 'Publicidade', 'icone' => 'fa-bullhorn', 'rota' => 'admin.banners', 'can' => 'banner.manage'],
        ]],
    ];

    if ($ehAdmin) {
        $grupos[] = ['rotulo' => 'Sistema', 'itens' => [
            ['chave' => 'utilizadores', 'nome' => 'Utilizadores', 'icone' => 'fa-users', 'rota' => 'admin.utilizadores', 'can' => 'user.manage'],
            ['chave' => 'configuracoes', 'nome' => 'Configurações', 'icone' => 'fa-cog', 'rota' => 'admin.configuracoes', 'can' => 'settings.manage'],
        ]];
    }
@endphp
<aside class="dashboard-sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('home') }}"><img src="{{ asset('images/logo2.png') }}" alt="PIGEA"></a>
    </div>
    <nav class="sidebar-nav">
        <ul>
            @foreach ($grupos as $grupo)
                <li class="sidebar-group" aria-hidden="true">{{ $grupo['rotulo'] }}</li>
                @foreach ($grupo['itens'] as $item)
                    @php
                        // Itens visíveis só para quem tem a permissão (ocultar
                        // menu é cosmético — a proteção real está no middleware).
                        $paoPermitido = $item['can'] === null || (auth()->user()?->can($item['can']) ?? false);
                    @endphp
                    @if ($paoPermitido)
                        <li class="sidebar-item{{ $item['chave'] === $ativa ? ' active' : '' }}"><a href="{{ route($item['rota']) }}"><i class="fa {{ $item['icone'] }}"></i> {{ $item['nome'] }}</a></li>
                    @endif
                @endforeach
            @endforeach
        </ul>
    </nav>
</aside>
