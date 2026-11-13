@php
    // Sino de notificações — módulo real (opção B, 2026-11-13): conta as
    // notificações por ler deste utilizador (queries em cada carga, sem
    // cache persistente para nunca desfasar o badge).
    $naoLidas = \App\Support\NotificacoesPainel::naoLidas();
@endphp
<header class="dashboard-header">
  <div class="header-left">
    <button class="sidebar-toggle" aria-label="Menu"><i class="fa fa-bars"></i></button>
  </div>
  <div class="header-right">
    {{-- CMS Pro §2: botão "Ver Site" — abre o frontend noutro separador. --}}
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn-ghost btn-sm header-ver-site"><i class="fa fa-external-link-alt"></i> Ver site</a>
    <button type="button" class="header-icon js-modal-open" data-modal="notificacoes-modal" aria-label="Abrir notificações" title="Notificações"><i class="fa fa-bell" aria-hidden="true"></i>@if (($naoLidas ?? 0) > 0)<span class="badge">{{ $naoLidas }}</span>@endif</button>
    <div class="header-user">
      <img src="{{ \App\Support\Autor::avatarUrl(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
      <span>{{ auth()->user()->name }}<small class="header-user-role">{{ auth()->user()->hasRole('admin') ? 'Admin' : (auth()->user()->hasRole('editor') ? 'Editor' : 'Colaborador') }}</small></span>
    </div>
    <a href="#" onclick="event.preventDefault(); document.getElementById('form-sair').submit();" class="header-logout" aria-label="Sair"><i class="fa fa-sign-out"></i></a>
  </div>
</header>
