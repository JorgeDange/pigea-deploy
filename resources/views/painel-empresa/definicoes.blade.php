@extends('layouts.dashboard')

@section('title', 'Definições')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li class="active">Definições</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Segurança</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" method="POST" action="{{ route('company.definicoes.seguranca') }}">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Password atual</label>
                <input class="dashboard-form-input" type="password" name="pw-atual" placeholder="••••••••" required>
                @error('pw-atual')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Nova password</label>
                <input class="dashboard-form-input" type="password" name="pw-nova" placeholder="••••••••" required>
                @error('pw-nova')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Confirmar nova password</label>
                <input class="dashboard-form-input" type="password" name="pw-conf" placeholder="••••••••" required>
                @error('pw-conf')<div class="form-error">{{ $message }}</div>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Sessão atual</label>
                            <ul class="list-check">
              <li><span>Windows · Luanda · 27/09/2026 09:12</span><span class="status-badge active">Ativa</span></li>
              <li><span>Android · Luanda · 25/09/2026 18:40</span><span class="status-badge pending">Terminada</span></li>
            </ul>
                
              </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar alterações</button>
                
              </div>
          </form>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Notificações</h3>
            
          </div>
          <div class="card-body">
            <ul class="list-check">
              <li><span>Novas candidaturas por email</span><label class="switch"><input type="checkbox" checked><span></span></label></li>
              <li><span>Novas candidaturas no navegador (push)</span><label class="switch"><input type="checkbox" checked><span></span></label></li>
              <li><span>Resumo semanal de desempenho</span><label class="switch"><input type="checkbox"><span></span></label></li>
            </ul>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Equipa</h3>
            <div class="card-actions"><button type="button" class="btn-ghost btn-sm js-modal-open" data-modal="modal-equipa"><i class="fa fa-user-plus"></i> Adicionar utilizador</button></div>
          </div>
          <div class="card-body">
            <div class="table-wrap">
              <table class="dashboard-table">
                <thead><tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Estado</th><th>Ações</th></tr></thead>
                <tbody>
                  <tr><td>Ana Manuel</td><td>ana.manuel@minex.ao</td><td>Gestor de vagas</td><td><span class="status-badge active">Ativa</span></td><td><div class="row-actions"><button class="icon-btn " title="Editar"><i class="fa fa-pen"></i></button><button class="icon-btn danger" title="Remover"><i class="fa fa-trash"></i></button></div></td></tr>
                  <tr><td>Bruno Silva</td><td>bruno.silva@minex.ao</td><td>Recrutador</td><td><span class="status-badge active">Ativa</span></td><td><div class="row-actions"><button class="icon-btn " title="Editar"><i class="fa fa-pen"></i></button><button class="icon-btn danger" title="Remover"><i class="fa fa-trash"></i></button></div></td></tr>
                  <tr><td>Carla Dias</td><td>carla.dias@minex.ao</td><td>Leitor</td><td><span class="status-badge pending">Pendente</span></td><td><div class="row-actions"><button class="icon-btn " title="Editar"><i class="fa fa-pen"></i></button><button class="icon-btn danger" title="Remover"><i class="fa fa-trash"></i></button></div></td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="danger-zone">
          <h4>Apagar conta</h4>
          <p>Ao apagar a conta da empresa, as vagas, candidaturas e oportunidades serão removidas de forma permanente do diretório PIGEA.</p>
          <div class="dashboard-form-actions">
            <button type="button" class="btn-danger"><i class="fa fa-trash"></i> Apagar conta da empresa</button>
          </div>
        </div>
        <div class="dashboard-modal" id="modal-equipa">
          <div class="dashboard-modal-content">
            <div class="dashboard-modal-header">
              <h3>Adicionar utilizador à equipa</h3>
              <button type="button" class="dashboard-modal-close js-modal-close"><i class="fa fa-times"></i></button>
            </div>
            <div class="dashboard-modal-body">
              <form class="dashboard-form">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Nome</label>
                <input class="dashboard-form-input" type="text" name="nome" placeholder="Nome completo" required>
                
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Email</label>
                <input class="dashboard-form-input" type="email" name="email" placeholder="o.seu@email.com" required>
                
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Perfil</label>
                <select class="dashboard-form-select" name="perfil">
                <option value="">Selecionar perfil…</option>
                <option value="gestor">Gestor de vagas</option>
                <option value="recrutador">Recrutador</option>
                <option value="leitor">Leitor</option>
              </select>
                
              </div>
              </form>
            </div>
            <div class="dashboard-modal-footer">
              <button type="button" class="btn-ghost js-modal-close">Cancelar</button>
              <button type="button" class="btn-primary">Adicionar</button>
            </div>
          </div>
        </div>
@endsection
