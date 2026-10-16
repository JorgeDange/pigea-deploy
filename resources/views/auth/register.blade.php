@extends('layouts.auth')

@section('title', 'Registo de utilizador — PIGEA')
@section('description', 'Criar conta na plataforma PIGEA — Plataforma de Impulsionamento das Geociências em Angola')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
      <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></div>
      <h1>Criar conta</h1>
      <p class="auth-subtitle">Junte-se à comunidade das geociências em Angola</p>
      <div class="auth-tabs">
        <button type="button" data-href="{{ url('/login') }}">Entrar</button>
        <button type="button" class="active" data-href="{{ url('/register') }}">Registar</button>
      </div>
      <form class="auth-form" method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-group">
          <label>Nome completo</label>
          <input type="text" name="name" value="{{ old('name') }}" placeholder="O seu nome" required>
          @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="{{ old('email') }}" placeholder="o.seu@email.com" required>
          @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="auth-grid">
          <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div class="form-group">
            <label>Confirmar password</label>
            <input type="password" name="password_confirmation" placeholder="••••••••" required>
          </div>
        </div>
        <div class="auth-form-row">
          <label><input type="checkbox" required> Aceito os Termos e Condições</label>
        </div>
        <button type="submit" class="btn-primary btn-block">Registar</button>
      </form>
      <p class="auth-footer">Já tem conta? <a href="{{ url('/login') }}">Entre</a></p>
      <p class="auth-footer">É uma empresa? <a href="{{ route('company.register') }}">Registe a sua empresa</a></p>
      <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
    </div>
  </div>
@endsection
