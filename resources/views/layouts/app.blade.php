<!DOCTYPE HTML>
<html lang="pt">
    <head>
        <!--=============== basic  ===============-->
        <meta charset="UTF-8">
        <title>@yield('title', 'PIGEA — Plataforma de Impulsionamento das Geociências em Angola')</title>
        <meta name="robots" content="index, follow" />
        <meta name="keywords" content="@yield('keywords', '')" />
        <meta name="description" content="@yield('description', 'PIGEA reúne notícias, projetos mineiros, empregos, formação e investigação das geociências em Angola.')">
        <meta property="og:title" content="@yield('og_title', 'PIGEA — Plataforma de Impulsionamento das Geociências em Angola')">
        <meta property="og:description" content="@yield('og_description', 'PIGEA reúne notícias, projetos mineiros, empregos, formação e investigação das geociências em Angola.')">
        <meta property="og:type" content="website">
        <meta property="og:locale" content="pt_AO">
        <meta property="og:site_name" content="PIGEA">
        <!--=============== css  ===============-->
        <link type="text/css" rel="stylesheet" href="{{ asset('css/plugins.css') }}">
        <link type="text/css" rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link type="text/css" rel="stylesheet" href="{{ asset('css/color.css') }}">
        <!--=============== favicons ===============-->
        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
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
            <!--register form -->
            @include('components.auth-modal')
            <!--register form end -->
        </div>
        <!-- Main end -->
        <!--=============== scripts  ===============-->
        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/plugins.js') }}"></script>
        <script src="{{ asset('js/scripts.js') }}"></script>
        @stack('scripts')
    </body>
</html>
