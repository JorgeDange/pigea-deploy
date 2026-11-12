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
<link rel="stylesheet" href="{{ asset('css/article.css') }}">
  @livewireStyles
  @stack('styles')
</head>
<body class="dashboard-body">
  <a class="skip-link" href="#conteudo-principal">Saltar para o conteúdo</a>
  <div class="dashboard-wrap">
    <!-- SIDEBAR -->
    @yield('sidebar')
    <!-- MAIN -->
    <div class="dashboard-main">
      @include('components.dashboard-header')
      <div class="dashboard-breadcrumb">
        <nav class="dashboard-breadcrumb-nav" aria-label="Caminho">
          @yield('breadcrumb')
        </nav>
      </div>
      <main class="dashboard-content" id="conteudo-principal" tabindex="-1">
        @yield('content')
      </main>
    </div>
  </div>
  <!-- Sair: o protótipo aponta os links "Sair" para login.html (GET); aqui
       passam por POST + CSRF, pelo que existe um único formulário escondido. -->
  <form id="form-sair" action="{{ route('logout') }}" method="POST" hidden>
    @csrf
  </form>
  {{-- Modal de notificações (cms_v.md v11/11/2026) — o sino abre-o; mostra a mesma lista que o dashboard usa. --}}
  <div class="dashboard-modal" id="notificacoes-modal" role="dialog" aria-modal="true" aria-labelledby="notificacoes-modal-title">
    <div class="dashboard-modal-content">
      <div class="dashboard-modal-header">
        <h3 id="notificacoes-modal-title">Notificações</h3>
        <button type="button" class="dashboard-modal-close js-modal-close" aria-label="Fechar notificações"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
      <div class="dashboard-modal-body">
        @include('partials.notificacoes', ['itens' => $adminNotificacoes ?? []])
      </div>
      <div class="dashboard-modal-footer">
        <a href="{{ route('admin.index') }}" class="btn-ghost">Ir para o painel</a>
      </div>
    </div>
  </div>
  {{-- Toast global (cms_v.md §5) — um único padrão; substitui o flash simples. --}}
  <div class="toast-region" aria-live="polite" aria-atomic="true"></div>
  {{-- Modal de confirmação genérico para ações destrutivas (cms_v.md §5). --}}
  <div class="dashboard-modal" id="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
    <div class="dashboard-modal-content">
      <div class="dashboard-modal-header">
        <h3 id="confirm-title">Confirmar ação</h3>
        <button type="button" class="dashboard-modal-close js-modal-close" aria-label="Fechar"><i class="fa fa-times" aria-hidden="true"></i></button>
      </div>
      <div class="dashboard-modal-body">
        <p id="confirm-message">Tem a certeza?</p>
      </div>
      <div class="dashboard-modal-footer">
        <button type="button" class="btn-ghost js-modal-close">Cancelar</button>
        <button type="button" class="btn-danger" id="confirm-action">Confirmar</button>
      </div>
    </div>
  </div>
  <script src="{{ asset('js/jquery.min.js') }}"></script>
  <script src="{{ asset('js/plugins.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  {{-- Fase 16 — window.PIGEAConfirm + .js-confirm/livewire intercept (publicidade) --}}
  <script src="{{ asset('js/admin/banner.js') }}"></script>
  @livewireScripts
  @stack('scripts')
</body>
</html>
