@php
    // Sino com a contagem de moderações em espera (cms_pro.md — sino de
    // notificações). Sem módulo de notificações: o badge é um atalho para a
    // lista de moderação de diretório (empresas + profissionais pendentes).
    $pendentesModeracao = (int) \App\Models\Company::pendente()->count()
        + (int) \App\Models\Professional::pendente()->count();
@endphp
<header class="dashboard-header">
  <div class="header-left">
    <button class="sidebar-toggle" aria-label="Menu"><i class="fa fa-bars"></i></button>
  </div>
  <div class="header-right">
    {{-- CMS Pro §2: botão "Ver Site" — abre o frontend noutro separador. --}}
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn-ghost btn-sm header-ver-site"><i class="fa fa-external-link-alt"></i> Ver site</a>
    <a href="{{ route('admin.empresas') }}" class="header-icon" aria-label="Fila de moderação do diretório" title="{{ $pendentesModeracao }} pendentes para moderar — abrir a fila"><i class="fa fa-bell"></i>@if ($pendentesModeracao > 0)<span class="badge">{{ $pendentesModeracao }}</span>@endif</a>
    <div class="header-user">
      <img src="{{ asset(auth()->user()->avatar ?: 'images/avatar/1.jpg') }}" alt="{{ auth()->user()->name }}">
      <span>{{ auth()->user()->name }}<small class="header-user-role">{{ auth()->user()->hasRole('admin') ? 'Admin' : (auth()->user()->hasRole('editor') ? 'Editor' : 'Colaborador') }}</small></span>
    </div>
    <a href="#" onclick="event.preventDefault(); document.getElementById('form-sair').submit();" class="header-logout" aria-label="Sair"><i class="fa fa-sign-out"></i></a>
  </div>
</header>
