<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') — PIGEA</title>
  <meta name="description" content="@yield('title') — PIGEA">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('css/plugins.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/color.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
  @livewireStyles
  @stack('styles')
</head>
<body class="dashboard-body">
  <div class="dashboard-wrap">
    <!-- SIDEBAR -->
    @yield('sidebar')
    <!-- MAIN -->
    <div class="dashboard-main">
      @include('components.dashboard-header')
      <div class="dashboard-breadcrumb">
        @yield('breadcrumb')
      </div>
      <main class="dashboard-content">
        @yield('content')
      </main>
    </div>
  </div>
  <!-- Sair: o protótipo aponta os links "Sair" para login.html (GET); aqui
       passam por POST + CSRF, pelo que existe um único formulário escondido. -->
  <form id="form-sair" action="{{ route('logout') }}" method="POST" hidden>
    @csrf
  </form>
  <script src="{{ asset('js/jquery.min.js') }}"></script>
  <script src="{{ asset('js/plugins.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  @livewireScripts
  @stack('scripts')
</body>
</html>
