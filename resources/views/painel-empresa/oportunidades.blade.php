@extends('layouts.dashboard')

@section('title', 'Oportunidades de negócio')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li class="active">Oportunidades</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Oportunidades de negócio</h3>
            <div class="card-actions"><button type="button" class="btn-primary btn-sm js-modal-open" data-modal="modal-oport"><i class="fa fa-plus"></i> Publicar nova oportunidade</button></div>
          </div>
          <div class="card-body">
            <div class="table-wrap">
              <table class="dashboard-table">
                <thead><tr><th>Título</th><th>Tipo</th><th>Prazo</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                  @forelse ($oportunidades as $oportunidade)
                  <tr>
                    <td>{{ $oportunidade->title }}</td>
                    <td>{{ $oportunidade->type ?? '—' }}</td>
                    <td>{{ $oportunidade->deadline?->format('d/m/Y') ?? '—' }}</td>
                    <td><span class="status-badge {{ \App\Models\Opportunity::BADGES[$oportunidade->status][0] ?? 'pending' }}">{{ \App\Models\Opportunity::BADGES[$oportunidade->status][1] ?? $oportunidade->status }}</span></td>
                    <td><div class="row-actions"><button class="icon-btn " title="Editar"><i class="fa fa-pen"></i></button><form method="POST" action="{{ route('company.oportunidades.eliminar', $oportunidade) }}" onsubmit="return confirm('Eliminar este registo? Esta ação não pode ser desfeita.')">@csrf<button type="submit" class="icon-btn danger" title="Eliminar"><i class="fa fa-trash"></i></button></form></div></td>
                  </tr>
                  @empty
                  <tr><td colspan="5">Nenhuma oportunidade encontrada.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="dashboard-modal" id="modal-oport">
          <div class="dashboard-modal-content">
            <div class="dashboard-modal-header">
              <h3>Nova oportunidade</h3>
              <button type="button" class="dashboard-modal-close js-modal-close"><i class="fa fa-times"></i></button>
            </div>
            <div class="dashboard-modal-body">
              <form class="dashboard-form">
                              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Título</label>
                <input class="dashboard-form-input" type="text" name="titulo" placeholder="Título da oportunidade" required>
                
              </div>
                              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Tipo</label>
                <select class="dashboard-form-select" name="tipo">
                <option value="">Selecionar tipo…</option>
                <option value="parceria">Parceria</option>
                <option value="fornecimento">Fornecimento</option>
                <option value="joint-venture">Joint venture</option>
                <option value="licensing">Licensing</option>
              </select>
                
              </div>
                              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Prazo</label>
                <input class="dashboard-form-input" type="date" name="prazo">
                
              </div>
              </form>
            </div>
            <div class="dashboard-modal-footer">
              <button type="button" class="btn-ghost js-modal-close">Cancelar</button>
              <button type="button" class="btn-primary">Publicar</button>
            </div>
          </div>
        </div>
@endsection
