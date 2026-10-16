@extends('layouts.auth')

@section('title', 'Verificação em duas etapas')
@section('description', 'Confirmar a identidade com o código de dois fatores')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></div>
    <h1>Código de segurança</h1>
    <p class="auth-subtitle">Introduza o código gerado pela sua aplicação de autenticação</p>
    <form class="auth-form" method="POST" action="{{ route('two-factor.login.store') }}">
      @csrf
      <div class="form-group">
        <label>Código</label>
        <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" required autofocus>
      </div>
      <button type="submit" class="btn-primary btn-block">Confirmar</button>
    </form>
    <div class="auth-divider">ou</div>
    <form class="auth-form" method="POST" action="{{ route('two-factor.login.store') }}">
      @csrf
      <div class="form-group">
        <label>Código de recuperação</label>
        <input type="text" name="recovery_code" autocomplete="off" placeholder="XXXX-XXXX" required>
      </div>
      <button type="submit" class="btn-primary btn-block">Usar código de recuperação</button>
    </form>
    <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
  </div>
</div>
@endsection
