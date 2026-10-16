@extends('layouts.dashboard')

@section('title', 'Painel da empresa')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li class="active">Dashboard</li></ul>
@endsection

@section('content')

        <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-briefcase"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $vagas }}</div>
            <div class="stat-card-label">Vagas publicadas</div>
            <div class="stat-card-footer">1 expira em breve</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-users"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $candidaturas }}</div>
            <div class="stat-card-label">Candidaturas recebidas</div>
            <div class="stat-card-footer">+9 esta semana</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-eye"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">1.240</div>
            <div class="stat-card-label">Visualizações do perfil</div>
            <div class="stat-card-footer">+12% este mês</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-handshake"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $oportunidades }}</div>
            <div class="stat-card-label">Oportunidades ativas</div>
            
          </div>
        </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Ações rápidas</h3>
            
          </div>
          <div class="card-body">
<div class="dashboard-form-actions" style="margin-top:0">
              <a href="{{ route('company.vaga-nova') }}" class="btn-primary"><i class="fa fa-plus"></i> Publicar nova vaga</a>
              <a href="{{ route('company.perfil') }}" class="btn-ghost"><i class="fa fa-pen"></i> Editar perfil da empresa</a>
              <a href="{{ route('company.estatisticas') }}" class="btn-ghost"><i class="fa fa-chart-bar"></i> Ver estatísticas</a>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Vagas recentes</h3>
            <div class="card-actions"><a href="{{ route('company.vagas') }}" class="btn-ghost btn-sm">Gerir vagas</a></div>
          </div>
          <div class="card-body">
            <div class="table-wrap">
              <table class="dashboard-table">
                <thead><tr><th>Título</th><th>Data</th><th>Candidaturas</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                  @php $ESTADOS_VAGA = ['published' => ['active', 'Ativa'], 'draft' => ['pending', 'Em análise'], 'rejected' => ['rejected', 'Expirada']]; @endphp
                  @forelse ($vagasRecentes as $vaga)
                  <tr>
                    <td>{{ $vaga->title }}</td>
                    <td>{{ $vaga->published_at?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $vaga->applications_count }}</td>
                    <td><span class="status-badge {{ $ESTADOS_VAGA[$vaga->status][0] ?? 'pending' }}">{{ $ESTADOS_VAGA[$vaga->status][1] ?? $vaga->status }}</span></td>
                    <td><div class="row-actions"><button class="icon-btn " title="Editar"><i class="fa fa-pen"></i></button><button class="icon-btn " title="Ver candidaturas" onclick="location.href='{{ route('company.candidaturas') }}'"><i class="fa fa-users"></i></button><form method="POST" action="{{ route('company.vagas.eliminar', $vaga) }}" onsubmit="return confirm('Eliminar este registo? Esta ação não pode ser desfeita.')">@csrf<button type="submit" class="icon-btn danger" title="Eliminar"><i class="fa fa-trash"></i></button></form></div></td>
                  </tr>
                  @empty
                  <tr><td colspan="5">Nenhuma vaga encontrada.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Candidaturas recentes</h3>
            <div class="card-actions"><a href="{{ route('company.candidaturas') }}" class="btn-ghost btn-sm">Ver todas</a></div>
          </div>
          <div class="card-body">
            <div class="table-wrap">
              <table class="dashboard-table">
                <thead><tr><th>Candidato</th><th>Vaga</th><th>Data</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                  @forelse ($candidaturasRecentes as $candidatura)
                  <tr>
                    <td><div class="table-user"><img src="{{ asset('images/avatar/'.(($candidatura->id % 5) + 2).'.jpg') }}" alt=""><span>{{ $candidatura->name }}<small>{{ $candidatura->email }}</small></span></div></td>
                    <td>{{ $candidatura->job?->title ?? '—' }}</td>
                    <td>{{ $candidatura->created_at->format('d/m/Y') }}</td>
                    <td><span class="status-badge {{ \App\Models\Application::BADGES[$candidatura->status][0] ?? 'pending' }}">{{ \App\Models\Application::BADGES[$candidatura->status][1] ?? $candidatura->status }}</span></td>
                    <td><div class="row-actions"><button class="icon-btn " title="Ver CV" @if ($candidatura->cv_url) onclick="location.href='{{ $candidatura->cv_url }}'" @endif><i class="fa fa-eye"></i></button></div></td>
                  </tr>
                  @empty
                  <tr><td colspan="5">Nenhuma candidatura encontrada.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
@endsection
