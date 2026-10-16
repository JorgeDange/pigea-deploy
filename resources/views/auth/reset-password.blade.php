@extends('layouts.auth')

@section('title', 'Repor password')
@section('description', 'Definir uma nova password para a conta PIGEA')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></div>
    <h1>Nova password</h1>
    <p class="auth-subtitle">Escolha uma password nova para a sua conta</p>
    <form class="auth-form" method="POST" action="{{ route('password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ request('token') }}">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" placeholder="o.seu@email.com" required>
      </div>
      <div class="auth-grid">
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Confirmar password</label>
          <input type="password" name="password_confirmation" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn-primary btn-block">Repor password</button>
    </form>
    <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
  </div>
</div>
@endsection
