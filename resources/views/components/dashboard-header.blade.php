<header class="dashboard-header">
  <div class="header-left">
    <button class="sidebar-toggle" aria-label="Menu"><i class="fa fa-bars"></i></button>
  </div>
  <div class="header-right">
    <button class="header-icon" aria-label="Notificações"><i class="fa fa-bell"></i><span class="badge">3</span></button>
    <div class="header-user">
      <img src="{{ asset(auth()->user()->avatar ?: 'images/avatar/1.jpg') }}" alt="{{ auth()->user()->name }}">
      <span>{{ auth()->user()->name }}</span>
    </div>
    <a href="#" onclick="event.preventDefault(); document.getElementById('form-sair').submit();" class="header-logout" aria-label="Sair"><i class="fa fa-sign-out"></i></a>
  </div>
</header>
