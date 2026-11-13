<!DOCTYPE HTML>
<html lang="pt">
    <head>
        <!--=============== basic  ===============-->
        <meta charset="UTF-8">
        <title>@yield('title', \App\Models\Setting::tituloPadrao())</title>
        <meta name="robots" content="index, follow" />
        <meta name="keywords" content="@yield('keywords', '')" />
        <meta name="description" content="@yield('description', \App\Models\Setting::descricaoPadrao())" />
        <meta property="og:title" content="@yield('og_title', \App\Models\Setting::tituloPadrao())" />
        <meta property="og:description" content="@yield('og_description', \App\Models\Setting::descricaoPadrao())" />
        <meta property="og:type" content="website">
        <meta property="og:locale" content="pt_AO">
        <meta property="og:site_name" content="{{ \App\Models\Setting::portal() }}">
        <!--=============== css  ===============-->
        <link type="text/css" rel="stylesheet" href="{{ asset('css/plugins.css') }}">
        <link type="text/css" rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link type="text/css" rel="stylesheet" href="{{ asset('css/color.css') }}">
        {{-- FASE 5.1 — Mega Menu (menu.md): depois do tema, para poder sobrepô-lo --}}
        <link type="text/css" rel="stylesheet" href="{{ asset('css/menu.css') }}">
        <link type="text/css" rel="stylesheet" href="{{ asset('css/topo.css') }}">
        <!--=============== favicons ===============-->
        {{-- Favicon configurável no admin (Geral); fallback images/favicon.png --}}
        @php $definicoes = \App\Models\Setting::todas(); @endphp
        <link rel="icon" type="image/png" href="{{ \App\Models\Setting::imagem($definicoes['favicon'] ?? null, 'images/favicon.png') }}">
        @stack('styles')
    </head>
    <body>
        <!-- main start  -->
        <div id="main">
            <!-- progress-bar  -->
            <div class="progress-bar-wrap">
                <div class="progress-bar color-bg"></div>
            </div>
            <!-- progress-bar end -->
            <!-- header -->
            @include('components.header')
            <!-- header end  -->
            @unless(request()->routeIs('home'))
                <!-- vh-preloader (todas as páginas do protótipo, excepto a home) -->
                <div class="vh-preloader"><div class="vh-pl-box"><img src="{{ asset('images/logo.png') }}" alt="PIGEA" class="vh-pl-logo"><span class="vh-pl-bar"></span></div></div>
            @endunless
            <!-- wrapper -->
            <div id="wrapper">
                <!-- content    -->
                <div class="content">
                    @yield('content')
                </div>
                <!-- content  end-->
                @yield('after_content')
                <!-- footer -->
                @include('components.footer')
                <!-- footer end-->
                @include('components.aside-panel')
            </div>
            <!-- wrapper end -->
            @if(request()->routeIs('home'))
                <!-- cookie-info-bar -->
                @include('components.cookie-bar')
                <!-- cookie-info-bar end -->
            @endif
        </div>
        <!-- Main end -->
        <!--=============== scripts  ===============-->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/plugins.js') }}"></script>
        <script src="{{ asset('js/scripts.js') }}"></script>
        {{-- FASE 5.1 — acordeão mobile do Mega Menu (desktop é 100% CSS) --}}
        <script src="{{ asset('js/menu.js') }}" defer></script>
        {{-- FASE 15 — rotação dos destaques publicitários (autoplay 4s) --}}
        <script src="{{ asset('js/destaque.js') }}" defer></script>
        {{-- FASE 18 — rotação dos banners verticais `lateral` (autoplay 5s) --}}
        <script src="{{ asset('js/banner-lateral.js') }}" defer></script>
        {{-- Newsletter — modal de subscrição (aparece em todas as páginas após ~3s) --}}
        <x-newsletter-modal />
        @stack('scripts')
    </body>
</html>
