<header class="main-header">
    <!-- top bar -->
    <div class="top-bar fl-wrap">
        <div class="container">
            {{-- Redes sociais (esquerda). Links ainda placeholder '#'; os URLs
                 de gestão ficam nas Configurações do admin (facebook/instagram/
                 linkedin/youtube) — ligação a implementar mais tarde. --}}
            <ul class="topbar-social">
                <li><a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-linkedin-in"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-youtube"></i></a></li>
            </ul>

            {{-- Área de publicidade CENTRAL — banner horizontal (pedido do
                 cliente, 11/11/2026). O componente renderiza o banner exibível
                 mais recente; sem banner vigente não gera HTML e o centro fica
                 livre. Posicionamento em css/topo.css. --}}
            <div class="top-banner-area">
                <x-banner-publicitario posicao="topo" />
            </div>

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
            <!-- logo holder  -->
            <a href="{{ route('home') }}" class="logo-holder"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></a>
            <!-- logo holder end -->
            <!-- header-search-wrap -->
            <div class="header-search-wrap novis_sarch">
                <div class="widget-inner">
                    <form action="{{ route('search.index') }}">
                        <input name="se" id="se" type="text" class="search" placeholder="Pesquisar notícias, projetos, empresas..." value="" />
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
