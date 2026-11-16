@extends('layouts.auth')

@section('title', 'Entrar — PIGEA')
@section('description', 'Entrar na conta PIGEA — Plataforma de Impulsionamento das Geociências em Angola')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-logo"><img src="{{ asset('images/logo.webp') }}" alt="PIGEA" width="240" height="100"></div>
      <h1>Entrar no PIGEA</h1>
      <p class="auth-subtitle">Acesse a sua conta para continuar</p>
      @if (session('estado'))
        <p class="auth-alerta" role="status" style="background:#FFF4E5; border:1px solid #F2A900; color:#7A4A00; padding:10px 14px; border-radius:8px; font-size:14px; margin:0 0 14px;">{{ session('estado') }}</p>
      @endif
      <form class="auth-form" method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="o.seu@email.com" required autofocus>
          @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
          @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div class="auth-form-row">
          <label><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Lembrar-me</label>
          <a href="{{ route('password.request') }}">Esqueci-me da password</a>
        </div>
        <button type="submit" class="btn-primary btn-block">Entrar</button>
      </form>
      <p class="auth-footer">Acesso reservado à equipa interna do PIGEA.</p>
      <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
    </div>
  </div>
@endsection
