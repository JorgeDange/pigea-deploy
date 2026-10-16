<header class="main-header">
    <!-- top bar -->
    <div class="top-bar fl-wrap">
        <div class="container">
            <div class="date-holder">
                <span class="date_num"></span>
                <span class="date_mounth"></span>
                <span class="date_year"></span>
            </div>
            <div class="header_news-ticker-wrap">
                <div class="hnt_title">Últimas Notícias :</div>
                <div class="header_news-ticker fl-wrap">
                    <ul>
                        @foreach ($widgetNews->take(4) as $item)
                            <li><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="n_contr-wrap">
                    <div class="n_contr p_btn"><i class="fas fa-caret-left"></i></div>
                    <div class="n_contr n_btn"><i class="fas fa-caret-right"></i></div>
                </div>
            </div>
            <ul class="topbar-social">
                <li><a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-vk"></i></a></li>
                <li><a href="#" target="_blank"><i class="fab fa-pinterest-p"></i></a></li>
            </ul>
        </div>
    </div>
    <!-- top bar end -->
    <div class="header-inner fl-wrap">
        <div class="container">
            <!-- logo holder  -->
            <a href="{{ route('home') }}" class="logo-holder"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></a>
            <!-- logo holder end -->
            <div class="search_btn htact show_search-btn"><i class="far fa-search"></i> <span class="header-tooltip">Pesquisar</span></div>
            @auth
                @php
                    // Mesma lógica do RoleBasedHomeResponse: admin/editor → painel
                    // admin, empresa → /painel-empresa, resto → /painel.
                    // Os três ramos escrevem caminhos relativos — o do admin vem
                    // do ADMIN_PATH (.env), ver config/app.php.
                    $usuario = auth()->user();
                    $painelUrl = $usuario->hasAnyRole(['admin', 'editor']) ? '/'.config('app.admin_path')
                        : ($usuario->hasRole('company') ? '/painel-empresa' : '/painel');
                @endphp
                <a href="{{ $painelUrl }}" class="srf_btn htact"><i class="fal fa-user"></i> <span class="header-tooltip">O meu painel</span></a>
            @else
                <div class="srf_btn htact show-reg-form"><i class="fal fa-user"></i> <span class="header-tooltip">Entrar</span></div>
            @endauth
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
