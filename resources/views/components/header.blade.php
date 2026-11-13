<header class="main-header">
    <!-- top bar -->
    <div class="top-bar fl-wrap">
        <div class="container">
            {{-- Redes sociais (esquerda) — dinâmicas a partir das Configurações
                 do admin (facebook/instagram/linkedin/youtube/twitter, link ou nome).
                 Sem nenhuma configurada, a barra fica sem ícones (nada de links mortos). --}}
            <ul class="topbar-social">
                @include('components.social-icons')
            </ul>

            {{-- O banner da barra superior (posicao="topo") foi removido a pedido do cliente: coexistia com a data e a pesquisa e não agradava. Caso se pretenda reativar, repor o <x-banner-publicitario posicao="topo" />. --}}

            {{-- Direita: data/hora + pesquisa. O ícone de pesquisa saiu do
                 header-inner, onde ficava sob a barra full-width do mega menu
                 (não visível/clicável) — pedido do cliente, 11/11/2026. --}}
            <div class="topbar-direita">
                <div class="date-holder">
                    <span class="date_num"></span>
                    <span class="date_mounth"></span>
                    <span class="date_year"></span>
                </div>
                <div class="search_btn htact show_search-btn"><i class="far fa-search"></i></div>
            </div>
        </div>
    </div>
    <!-- top bar end -->
    <div class="header-inner fl-wrap">
        <div class="container">
            {{-- Logótipo configurável no admin; fallback images/logo.png do tema --}}
            @php $definicoes = \App\Models\Setting::todas(); @endphp
            <a href="{{ route('home') }}" class="logo-holder"><img src="{{ \App\Models\Setting::imagem($definicoes['logo'] ?? null, 'images/logo.png') }}" alt="{{ \App\Models\Setting::portal() }}"></a>
            <!-- logo holder end -->
            <!-- header-search-wrap -->
            <div class="header-search-wrap novis_sarch">
                <div class="widget-inner">
                    <form action="{{ route('search.index') }}">
                        <input name="se" id="se" type="text" class="search" placeholder="Pesquisar notícias, empresas, legislação…" value="" />
                        <button class="search-submit" id="submit_btn"><i class="fa fa-search transition"></i> </button>
                    </form>
                </div>
            </div>
            <!-- header-search-wrap end -->
            <!-- nav-button-wrap-->
            <div class="nav-button-wrap">
                <div class="nav-button">
                    <span></span><span></span><span></span>
                </div>
            </div>
            <!-- nav-button-wrap end-->
            <!--  navigation -->
            @include('components.main-menu')
            <!-- navigation  end -->
        </div>
    </div>
</header>
