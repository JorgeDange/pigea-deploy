@extends('layouts.dashboard')

@section('title', 'Gerir — '.$utilizador->name)

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.utilizadores') }}">Utilizadores</a></li><li class="active" aria-current="page">{{ $utilizador->name }}</li></ol>
@endsection

@section('content')
<h1 class="sr-only">Gerir utilizador</h1>

<div class="card">
  <div class="card-head">
    <h2 class="card-title">Gerir — {{ $utilizador->name }}</h2>
    <div class="card-actions"><a class="btn-ghost btn-sm" href="{{ route('admin.utilizadores') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar à lista</a></div>
  </div>
  <div class="card-body">
    @if (session('estado'))
      <div class="flash-toast" data-flash-toast="success" hidden>{{ session('estado') }}</div>
    @endif

    <dl class="conta-info" style="margin-bottom: 18px;">
      <div class="conta-info-item"><dt>Papel atual</dt><dd>{{ $utilizador->getRoleNames()->first() ?? '—' }}</dd></div>
      <div class="conta-info-item"><dt>Estado</dt><dd>{{ match ($utilizador->status ?? 'active') { 'blocked' => 'Desativado', 'pending' => 'Pendente', default => 'Ativo' } }}</dd></div>
      <div class="conta-info-item"><dt>Conta criada</dt><dd>{{ $utilizador->created_at?->format('d/m/Y H:i') }}</dd></div>
      <div class="conta-info-item"><dt>Último acesso</dt><dd>{{ $utilizador->last_login_at?->format('d/m/Y H:i') ?? '—' }}</dd></div>
      <div class="conta-info-item"><dt>Troca de senha pendente</dt><dd>{{ $utilizador->must_change_password ? 'Sim' : 'Não' }}</dd></div>
    </dl>

    <form class="dashboard-form" action="{{ route('admin.utilizador.atualizar', $utilizador) }}" method="post" autocomplete="off">
      @csrf
      @method('PUT')

      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="nome">Nome completo</label>
        <input class="dashboard-form-input" type="text" id="nome" name="nome" maxlength="190" value="{{ old('nome', $utilizador->name) }}" required>
        @error('nome')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>

      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="email">Email</label>
        <input class="dashboard-form-input" type="email" id="email" name="email" maxlength="190" value="{{ old('email', $utilizador->email) }}" required>
        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>

      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="perfil">Papel</label>
        <select class="dashboard-form-select" id="perfil" name="perfil" @if ($utilizador->is(auth()->user())) aria-describedby="perfil-hint" @endif required>
          @foreach ($perfisPermitidos as $p)
            <option value="{{ $p }}" @selected(old('perfil', $utilizador->getRoleNames()->first()) === $p)>{{ ucfirst($p) }}</option>
          @endforeach
        </select>
        @if ($utilizador->is(auth()->user()))
          <small class="help-text" id="perfil-hint">Não pode mudar o seu próprio papel.</small>
        @endif
        @error('perfil')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>

      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="telefone">Telefone <span class="help-text">(opcional)</span></label>
        <input class="dashboard-form-input" type="text" id="telefone" name="telefone" maxlength="40" value="{{ old('telefone', $utilizador->phone) }}" placeholder="+244 …">
        @error('telefone')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>

      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="provincia">Província <span class="help-text">(opcional)</span></label>
        <input class="dashboard-form-input" type="text" id="provincia" name="provincia" maxlength="64" value="{{ old('provincia', $utilizador->province) }}" placeholder="Ex.: Luanda">
        @error('provincia')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>

      <div class="dashboard-form-actions">
        <button type="submit" class="btn-primary"><i class="fa fa-save" aria-hidden="true"></i> Guardar dados</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <h2 class="card-title">Repor senha</h2>
  </div>
  <div class="card-body">
    <p class="help-text" style="margin-top:0;">Define uma senha provisória para <strong>{{ $utilizador->name }}</strong>. No próximo acesso, o utilizador é <strong>obrigado a trocar</strong> a senha (decisão do cliente) e todas as sessões abertas da conta são <strong>encerradas na hora</strong>.</p>

    <form class="dashboard-form" action="{{ route('admin.utilizador.senha', $utilizador) }}" method="post" autocomplete="off">
      @csrf
      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="password">Nova senha provisória</label>
        <input class="dashboard-form-input" type="password" id="password" name="password" autocomplete="new-password" required>
        <small class="help-text">Mínimo de 8 caracteres, com letras e números (regra do painel).</small>
        @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>
      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="password_confirmation">Repetir a senha provisória</label>
        <input class="dashboard-form-input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
      </div>
      <div class="dashboard-form-actions">
        <button type="submit" class="btn-primary"><i class="fa fa-key" aria-hidden="true"></i> Repor senha</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
  .conta-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin: 0; }
  .conta-info-item { border: 1px solid #ECECEC; border-radius: 8px; padding: 12px 14px; background: #FAFAFA; }
  .conta-info-item dt { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
  .conta-info-item dd { margin: 0; font-size: 15px; color: #1A1A1A; font-weight: 600; }
</style>
@endpush
