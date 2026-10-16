@extends('layouts.auth')

@section('title', 'Recuperar password')
@section('description', 'Recuperar a password da conta PIGEA — Plataforma de Impulsionamento das Geociências em Angola')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></div>
    <h1>Recuperar password</h1>
    <p class="auth-subtitle">Receba um link de recuperação no seu email</p>
    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="o.seu@email.com" required>
      </div>
      <button type="submit" class="btn-primary btn-block">Enviar link</button>
    </form>
    <div class="auth-divider">ou</div>
    <p class="auth-footer"><a href="{{ url('/login') }}">Voltar a entrar</a></p>
    <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
  </div>
</div>
@endsection
