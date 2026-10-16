@extends('layouts.dashboard')

@section('title', 'Notificações')

@section('sidebar')
<x-sidebar-user />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('user.dashboard') }}">Painel</a></li><li class="active">Notificações</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Notificações</h3>
            <div class="card-actions">
              {{-- Sem classe `js-mark-all-read`: o dashboard.js do protótipo faria
                   `return false` e bloqueava o submit real (BD). --}}
              <form method="POST" action="{{ route('user.notificacoes.ler-todas') }}">
                @csrf
                <button type="submit" class="btn-ghost btn-sm"><i class="fa fa-check-double"></i> Marcar todas como lidas</button>
              </form>
            </div>
          </div>
          <div class="card-body">
            <div class="notification-list">
              @forelse ($notificacoes as $notificacao)
              <div class="notification-item{{ $notificacao->read_at === null ? ' unread' : '' }}">
                <div class="notification-icon"><i class="fa {{ $notificacao->icon }}"></i></div>
                <div class="notification-content"><p><strong>{{ $notificacao->title }}</strong> — {{ $notificacao->text }}</p></div>
                <div class="notification-time">{{ $notificacao->created_at->format('d/m/Y · H:i') }}</div>
              </div>
              @empty
              <div class="notification-item"><div class="notification-content"><p>Sem notificações.</p></div></div>
              @endforelse
            </div>
          </div>
        </div>
@endsection
