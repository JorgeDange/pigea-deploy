@php
    // FASE 5.1 — Mega Menu (menu.md §2-§3). Os dados (9 secções + 44
    // subcategorias, na ordem exata do cliente) vêm da tabela `categories`
    // via MenuSeeder / App\Support\MenuCategorias — o admin pode acrescentar
    // subcategorias novas que o painel acompanha (menu.md §4).
    //
    // Item ativo: página atual (rota) ou filtro de subcategoria
    // (?cat=prospeccao-e-pesquisa acende "Mineração", etc.).
    $gruposMenu = \App\Support\MenuCategorias::grupos();
    $grupoAtivo = \App\Support\MenuCategorias::grupoAtivo();
@endphp
<div class="nav-holder main-menu mega-menu">
    <nav class="mm-nav" aria-label="Menu principal">
        <ul class="mm-bar">
            @foreach ($gruposMenu as $chave => $grupo)
                <li class="mm-item mm-grupo-{{ $chave }} {{ $grupoAtivo === $chave ? 'mm-item-ativo' : '' }}">
                    <a href="{{ $grupo['link'] }}"
                       class="mm-topo {{ $grupoAtivo === $chave ? 'act-link' : '' }}"
                       aria-haspopup="true">
                        {{ $grupo['rotulo'] }}
                        <i class="fa fa-chevron-down" aria-hidden="true"></i>
                    </a>

                    @if ($grupo['sublinks']->isNotEmpty())
                        <div class="mm-panel">
                            <div class="mm-panel-inner">
                                <ul class="mm-grelha">
                                    @foreach ($grupo['sublinks'] as $sublink)
                                        <li class="mm-grelha-item">
                                            <a class="mm-link" href="{{ $sublink['url'] }}">
                                                <i class="fa fa-angle-right" aria-hidden="true"></i>
                                                {{ $sublink['name'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
</div>
