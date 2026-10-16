@extends('layouts.auth')

@section('title', 'Entrar — PIGEA')
@section('description', 'Entrar na conta PIGEA — Plataforma de Impulsionamento das Geociências em Angola')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></div>
      <h1>Entrar no PIGEA</h1>
      <p class="auth-subtitle">Acesse a sua conta para continuar</p>
      <div class="auth-tabs">
        <button type="button" class="active" data-href="{{ url('/login') }}">Entrar</button>
        <button type="button" data-href="{{ url('/register') }}">Registar</button>
      </div>
      <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="o.seu@email.com" required autofocus>
          @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
          @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="auth-form-row">
          <label><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Lembrar-me</label>
          <a href="{{ route('password.request') }}">Esqueci-me da password</a>
        </div>
        <button type="submit" class="btn-primary btn-block">Entrar</button>
      </form>
      <div class="auth-divider">ou</div>
      <button type="button" class="btn-google"><i class="fab fa-google"></i> Entrar com Google</button>
      <p class="auth-footer">Não tem conta? <a href="{{ url('/register') }}">Registe-se</a></p>
      <p class="auth-footer">É uma empresa? <a href="{{ route('company.register') }}">Registe a sua empresa</a></p>
      <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
    </div>
  </div>
@endsection
