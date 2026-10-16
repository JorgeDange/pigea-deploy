@extends('layouts.dashboard')

@section('title', 'Gerir Utilizadores')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active">Utilizadores</li></ul>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">Utilizadores</h3>
    <div class="card-actions"><button type="button" class="btn-primary btn-sm js-modal-open" data-modal="modal-user"><i class="fa fa-user-plus"></i> Adicionar utilizador</button></div>
  </div>
  <livewire:admin.users-table />
</div>

{{-- Modal do protótipo (abre/fecha pelo dashboard.js original). No HTML estático
     o `<form>` não tinha `action` nem botão de submissão — era só visual. Aqui
     submete ao UserFormController, por isso:
       - acrescentam-se os campos de password (o modal original não tinha;
         sem eles a conta nascia sem password válida e não dava para entrar);
       - o modal reabre com a classe `open` quando há erros, senão ficariam
         escondidos dentro de um modal fechado. --}}
<div class="dashboard-modal{{ $errors->any() ? ' open' : '' }}" id="modal-user">
  <div class="dashboard-modal-content">
    <div class="dashboard-modal-header">
      <h3>Adicionar utilizador</h3>
      <button type="button" class="dashboard-modal-close js-modal-close"><i class="fa fa-times"></i></button>
    </div>
    <div class="dashboard-modal-body">
      <form class="dashboard-form" id="modal-user-form" action="{{ route('admin.utilizador.novo') }}" method="post">
        @csrf
        <div class="dashboard-form-group">
          <label class="dashboard-form-label">Nome</label>
          <input class="dashboard-form-input" type="text" name="nome" placeholder="Nome completo" value="{{ old('nome') }}" required>
          @error('nome')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="dashboard-form-group">
          <label class="dashboard-form-label">Email</label>
          <input class="dashboard-form-input" type="email" name="email" placeholder="o.seu@email.com" value="{{ old('email') }}" required>
          @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="dashboard-form-group">
          <label class="dashboard-form-label">Perfil</label>
          <select class="dashboard-form-select" name="perfil" required>
            <option value="">Selecionar perfil…</option>
            <option value="user" @selected(old('perfil') === 'user')>Utilizador</option>
            <option value="company" @selected(old('perfil') === 'company')>Empresa</option>
            <option value="editor" @selected(old('perfil') === 'editor')>Editor</option>
            <option value="admin" @selected(old('perfil') === 'admin')>Admin</option>
          </select>
          @error('perfil')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="dashboard-form-group">
          <label class="dashboard-form-label">Palavra-passe</label>
          <input class="dashboard-form-input" type="password" name="password" placeholder="mínimo 8 caracteres" required>
          @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="dashboard-form-group">
          <label class="dashboard-form-label">Confirmar palavra-passe</label>
          <input class="dashboard-form-input" type="password" name="password_confirmation" placeholder="repita a palavra-passe" required>
          @error('password_confirmation')<p class="form-error">{{ $message }}</p>@enderror
        </div>
      </form>
    </div>
    <div class="dashboard-modal-footer">
      <button type="button" class="btn-ghost js-modal-close">Cancelar</button>
      {{-- Fica onde o protótipo o pôs (rodapé, fora do `<form>`) e liga-se ao
           form pelo atributo HTML5 `form` — altera-se o mínimo de markup. --}}
      <button type="submit" form="modal-user-form" class="btn-primary">Adicionar</button>
    </div>
  </div>
</div>
@endsection
