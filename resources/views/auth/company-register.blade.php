@extends('layouts.auth')

@section('title', 'Registar empresa — PIGEA')
@section('description', 'Registo de empresa no diretório PIGEA — Plataforma de Impulsionamento das Geociências em Angola')

@section('content')
<div class="auth-wrap">
    <div class="auth-card auth-wide">
      <div class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="PIGEA"></div>
      <h1>Registar Empresa</h1>
      <p class="auth-subtitle">Preencha os dados da empresa para entrar no diretório do PIGEA e aceder a novas oportunidades de negócio.</p>
      <div class="auth-tabs">
        <button type="button" data-href="{{ url('/login') }}">Entrar</button>
        <button type="button" data-href="{{ url('/register') }}">Criar conta</button>
      </div>
      <div id="message"></div>
      <form class="auth-form" action="{{ route('company.register.store') }}" method="post" name="registoform" id="registoform">
        @csrf
        <div class="auth-grid">
          <div class="form-group">
            <label>Nome da empresa *</label>
            <input type="text" name="empresa" placeholder="Nome da empresa" value="{{ old('empresa') }}" required>
            @error('empresa')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div class="form-group">
            <label>NIF *</label>
            <input type="text" name="nif" placeholder="NIF" value="{{ old('nif') }}" required>
            @error('nif')<p class="form-error">{{ $message }}</p>@enderror
          </div>
        </div>
        <div class="auth-grid">
          <div class="form-group">
            <label>Setor de atividade *</label>
            <select name="setor" required>
              <option value="">Selecionar…</option>
            <option value="mineracao" @selected(old('setor') === 'mineracao')>Mineração</option>
            <option value="agua" @selected(old('setor') === 'agua')>Água</option>
            <option value="ambiente" @selected(old('setor') === 'ambiente')>Ambiente</option>
            <option value="energia" @selected(old('setor') === 'energia')>Energia</option>
            <option value="construcao" @selected(old('setor') === 'construcao')>Construção e Obras</option>
            <option value="transportes" @selected(old('setor') === 'transportes')>Transportes</option>
            <option value="servicos" @selected(old('setor') === 'servicos')>Serviços</option>
            <option value="outro" @selected(old('setor') === 'outro')>Outro</option>
          </select>
          @error('setor')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div class="form-group">
            <label>Tipo de empresa</label>
            <select name="tipo_empresa">
              <option value="">Selecionar…</option>
            <option value="suarl" @selected(old('tipo_empresa') === 'suarl')>Unipessoal por Quotas (Unipessoal Lda)</option>
            <option value="lda" @selected(old('tipo_empresa') === 'lda')>Sociedade por Quotas (Lda)</option>
            <option value="sa" @selected(old('tipo_empresa') === 'sa')>Sociedade Anónima (SA)</option>
            <option value="representacao" @selected(old('tipo_empresa') === 'representacao')>Representação</option>
            <option value="outro" @selected(old('tipo_empresa') === 'outro')>Outro</option>
          </select>
          @error('tipo_empresa')<p class="form-error">{{ $message }}</p>@enderror
          </div>
        </div>
        <div class="auth-grid">
          <div class="form-group">
            <label>Responsável pelo registo *</label>
            <input type="text" name="responsavel" placeholder="Nome do responsável" value="{{ old('responsavel') }}" required>
            @error('responsavel')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" placeholder="o.seu@email.com" value="{{ old('email') }}" required>
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
          </div>
        </div>
        <div class="auth-grid">
          <div class="form-group">
            <label>Telefone *</label>
            <input type="tel" name="telefone" placeholder="+244 000 000 000" value="{{ old('telefone') }}" required>
            @error('telefone')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div class="form-group">
            <label>Website</label>
            <input type="text" name="website" placeholder="https:// (opcional)" value="{{ old('website') }}">
            @error('website')<p class="form-error">{{ $message }}</p>@enderror
          </div>
        </div>
        <div class="form-group">
          <label>Morada / observações</label>
          <textarea name="morada" rows="3" placeholder="Morada, província e observações">{{ old('morada') }}</textarea>
          @error('morada')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="auth-form-row">
          <label><input type="checkbox" name="veracidade" required> Confirmo que os dados fornecidos são verdadeiros e autorizo a sua publicação no diretório PIGEA.</label>
        </div>
        @error('veracidade')<p class="form-error">{{ $message }}</p>@enderror
        <button type="submit" class="btn-primary btn-block">Registar Empresa</button>
      </form>
      <p class="auth-footer">Já tem conta? <a href="{{ url('/login') }}">Entre</a></p>
      <p class="auth-footer">É um particular? <a href="{{ url('/register') }}">Crie uma conta de utilizador</a></p>
      <div class="auth-links"><a href="{{ route('home') }}">← Voltar ao site</a></div>
    </div>
  </div>
@endsection
