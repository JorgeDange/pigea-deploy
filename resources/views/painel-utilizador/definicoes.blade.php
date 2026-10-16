@extends('layouts.dashboard')

@section('title', 'Definições')

@section('sidebar')
<x-sidebar-user />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('user.dashboard') }}">Painel</a></li><li class="active">Definições</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Segurança</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" method="POST" action="{{ route('user.definicoes.seguranca') }}">
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
                <label class="dashboard-form-label">Autenticação em dois fatores (2FA)</label>
                            <ul class="list-check">
              <li><span>Receber código por SMS</span><label class="switch"><input type="checkbox"><span></span></label></li>
              <li><span>Aplicação autenticadora</span><label class="switch"><input type="checkbox" checked><span></span></label></li>
            </ul>
                <div class="dashboard-form-hint">Recomendamos ativar a 2FA para proteger a sua conta.</div>
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
              <li><span>Notificações por email</span><label class="switch"><input type="checkbox" checked><span></span></label></li>
              <li><span>Notificações no navegador (push)</span><label class="switch"><input type="checkbox" checked><span></span></label></li>
              <li><span>Resumo semanal (newsletter)</span><label class="switch"><input type="checkbox"><span></span></label></li>
            </ul>
          </div>
        </div>
        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Privacidade</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" method="POST" action="{{ route('user.definicoes.privacidade') }}">
              @csrf
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Visibilidade do perfil</label>
                <select class="dashboard-form-select" name="visibilidade">
                <option value="">Selecionar…</option>
                <option value="todos" @selected(old('visibilidade', $user->profile_visibility) === 'todos')>Visível para todos</option>
                <option value="empresas" @selected(old('visibilidade', $user->profile_visibility) === 'empresas')>Visível apenas para empresas</option>
                <option value="ninguem" @selected(old('visibilidade', $user->profile_visibility) === 'ninguem')>Perfil privado</option>
              </select>
                @error('visibilidade')<div class="form-error">{{ $message }}</div>@enderror
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar alterações</button>
                
              </div>
          </form>
          </div>
        </div>
        <div class="danger-zone">
          <h4>Apagar conta</h4>
          <p>Ao apagar a sua conta, todos os dados pessoais, candidaturas e vagas guardadas serão removidos de forma permanente.</p>
          <div class="dashboard-form-actions">
            <button type="button" class="btn-danger"><i class="fa fa-trash"></i> Apagar a minha conta</button>
          </div>
        </div>
@endsection
