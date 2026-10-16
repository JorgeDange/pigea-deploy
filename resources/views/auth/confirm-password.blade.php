@extends('layouts.auth')

@section('title', 'Confirmar password')
@section('description', 'Confirmar a password antes de continuar')

@section('content')
<div class="auth-wrap">
  <div class="auth-card">
    <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></div>
    <h1>Confirmar password</h1>
    <p class="auth-subtitle">Introduza a sua password para continuar</p>
    <form class="auth-form" method="POST" action="{{ route('password.confirm.store') }}">
      @csrf
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required autofocus>
      </div>
      <button type="submit" class="btn-primary btn-block">Confirmar</button>
    </form>
    <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
  </div>
</div>
@endsection
