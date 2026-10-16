@extends('layouts.dashboard')

@section('title', 'Estatísticas')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li class="active">Estatísticas</li></ul>
@endsection

@section('content')

        <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-card-icon"><i class="fa fa-eye"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">1.240</div>
            <div class="stat-card-label">Visualizações do perfil</div>
            <div class="stat-card-footer">+12% este mês</div>
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
          <div class="stat-card-icon"><i class="fa fa-handshake"></i></div>
          <div class="stat-card-body">
            <div class="stat-card-number">{{ $oportunidadesAtivas }}</div>
            <div class="stat-card-label">Interações com oportunidades</div>
            <div class="stat-card-footer">+1 esta semana</div>
          </div>
        </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Gráficos</h3>
            <div class="card-actions"><span class="status-badge pending">Chart.js — Fase 3</span></div>
          </div>
          <div class="card-body">
<div class="charts-grid">
              <div class="chart-box">Visualizações do perfil<br>(gráfico de linha — reservado para Chart.js)</div>
              <div class="chart-box">Candidaturas por vaga<br>(gráfico de barras — reservado para Chart.js)</div>
              <div class="chart-box">Interações com oportunidades<br>(gráfico de pizza — reservado para Chart.js)</div>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Resumo por vaga</h3>
            
          </div>
          <div class="card-body">
            <div class="table-wrap">
              <table class="dashboard-table">
                <thead><tr><th>Vaga</th><th>Visualizações</th><th>Candidaturas</th><th>Taxa de conversão</th></tr></thead>
                <tbody>
                  {{-- "Visualizações" e "Taxa de conversão" não têm fonte de
                       dados (→ "—" como nas tabelas do admin). --}}
                  @forelse ($vagasResumo as $vaga)
                  <tr>
                    <td>{{ $vaga->title }}</td>
                    <td>—</td>
                    <td>{{ $vaga->applications_count }}</td>
                    <td>—</td>
                  </tr>
                  @empty
                  <tr><td colspan="4">Nenhuma vaga encontrada.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
@endsection
