@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('sidebar')
<x-sidebar-user />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('user.dashboard') }}">Painel</a></li><li class="active">Dashboard</li></ul>
@endsection

@section('content')

        <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-paper-plane"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $enviadas }}</div>
            <div class="stat-card-label">Candidaturas enviadas</div>
            <div class="stat-card-footer">+{{ $enviadasSemana }} esta semana</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-clock"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $emAnalise }}</div>
            <div class="stat-card-label">Em análise</div>
            
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-bookmark"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $guardadas }}</div>
            <div class="stat-card-label">Vagas guardadas</div>
            
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-bell"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $naoLidas }}</div>
            <div class="stat-card-label">Notificações não lidas</div>
            
          </div>
        </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">As minhas candidaturas recentes</h3>
            <div class="card-actions"><a href="{{ route('user.candidaturas') }}" class="btn-ghost btn-sm">Ver todas</a></div>
          </div>
          <div class="card-body">
            <div class="table-wrap">
              <table class="dashboard-table">
                <thead><tr><th>Vaga</th><th>Empresa</th><th>Data</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                  @forelse ($candidaturas as $candidatura)
                  <tr>
                    <td>{{ $candidatura->job?->title ?? '—' }}</td>
                    <td>{{ $candidatura->job?->author?->name ?? '—' }}</td>
                    <td>{{ $candidatura->created_at->format('d/m/Y') }}</td>
                    <td><span class="status-badge {{ \App\Models\Application::BADGES[$candidatura->status][0] ?? 'pending' }}">{{ \App\Models\Application::BADGES[$candidatura->status][1] ?? $candidatura->status }}</span></td>
                    <td><div class="row-actions"><button class="icon-btn " title="Ver" onclick="location.href='{{ $candidatura->job ? route('jobs.show', $candidatura->job->slug) : '#' }}'"><i class="fa fa-eye"></i></button></div></td>
                  </tr>
                  @empty
                  <tr><td colspan="5">Nenhuma candidatura encontrada.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Vagas recomendadas</h3>
            <div class="card-actions"><a href="{{ route('user.guardadas') }}" class="btn-ghost btn-sm">Ver todas as vagas</a></div>
          </div>
          <div class="card-body">
<div class="jobs-grid">
          @php $PROVINCIAS = ['luanda' => 'Luanda', 'benguela' => 'Benguela', 'huila' => 'Huíla', 'malanje' => 'Malanje', 'cuando-cubango' => 'Cuando Cubango', 'zaire' => 'Zaire']; @endphp
          @forelse ($recomendadas as $vaga)
          <div class="job-card">
            <h4><a href="{{ route('jobs.show', $vaga->slug) }}">{{ $vaga->title }}</a></h4>
            <div class="job-meta">{{ $vaga->author?->name ?? '—' }} · {{ $PROVINCIAS[$vaga->province] ?? '—' }}</div>
            <div class="job-tags"><span>{{ $vaga->category?->name ?? '—' }}</span><span>{{ $vaga->contract_type ?? '—' }}</span></div>
            <form method="POST" action="{{ route('user.guardar', $vaga) }}">
              @csrf
              {{-- Sem classe `js-remove-item`: o dashboard.js do protótipo faria
                   `return false` e bloqueava o submit real. --}}
              <button type="submit" class="btn-ghost btn-sm"><i class="fa fa-bookmark"></i> Guardar</button>
            </form>
          </div>
          @empty
          <div class="job-card"><h4>Nenhuma vaga encontrada.</h4></div>
          @endforelse
        </div>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Notificações recentes</h3>
            <div class="card-actions"><a href="{{ route('user.notificacoes') }}" class="btn-ghost btn-sm">Ver todas</a></div>
          </div>
          <div class="card-body">
            <div class="notification-list">
              @forelse ($notificacoes as $notificacao)
              @php
                $dias = (int) $notificacao->created_at->diffInDays(now());
                $tempo = $dias < 1 ? 'hoje' : ($dias === 1 ? 'há 1 dia' : 'há '.$dias.' dias');
              @endphp
              <div class="notification-item{{ $notificacao->read_at === null ? ' unread' : '' }}">
                <div class="notification-icon"><i class="fa {{ $notificacao->icon }}"></i></div>
                <div class="notification-content"><p><strong>{{ $notificacao->title }}</strong> — {{ $notificacao->text }}</p></div>
                <div class="notification-time">{{ $tempo }}</div>
              </div>
              @empty
              <div class="notification-item"><div class="notification-content"><p>Sem notificações.</p></div></div>
              @endforelse
            </div>
          </div>
        </div>
@endsection
