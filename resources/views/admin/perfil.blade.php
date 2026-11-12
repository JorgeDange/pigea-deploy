@extends('layouts.dashboard')

@section('title', 'O Meu Perfil')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">O Meu Perfil</li></ol>
@endsection

@section('content')
<h1 class="sr-only">O Meu Perfil</h1>

<div class="card">
  <div class="card-head">
    <h2 class="card-title">O Meu Perfil</h2>
  </div>
  <div class="card-body">
    <form class="dashboard-form" action="{{ route('admin.perfil.update') }}" method="post" enctype="multipart/form-data">
      @csrf

      @if (session('estado'))
        <div class="flash-toast" data-flash-toast="success" hidden>{{ session('estado') }}</div>
      @endif

      <div class="dashboard-form-group">
        <label class="dashboard-form-label" for="name">Nome completo</label>
        <input class="dashboard-form-input" type="text" id="name" name="name" maxlength="100" value="{{ old('name', $utilizador->name) }}" required>
        @error('name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>

      @php
          $avatarAtual = $utilizador->avatar ?? null;
          $fonteFoto = old('avatar_fonte', ($avatarAtual && str_starts_with($avatarAtual, 'http')) ? 'link' : 'ficheiro');
          $avatarSrc = $avatarAtual
              ? (str_starts_with($avatarAtual, 'http') ? $avatarAtual : asset($avatarAtual))
              : asset('images/logo.png');
      @endphp

      <div class="dashboard-form-group">
        <span class="dashboard-form-label">Foto de perfil</span>

        {{-- Previa da foto atual --}}
        <div class="perfil-atual">
          <img class="perfil-atual-img" src="{{ $avatarSrc }}" alt="Foto atual de {{ $utilizador->name }}">
          <div class="perfil-atual-meta">
            <strong>Foto {{ $avatarAtual ? 'atual' : 'padrão (logo PIGEA)' }}</strong>
            <small class="help-text">Aparece ao lado do seu nome na assinatura dos artigos («Escrito por»).</small>
          </div>
        </div>

        {{-- Fonte da foto: ficheiro ou link (mesmo padrao da capa das noticias) --}}
        <div class="cover-source">
          <label class="dashboard-checkbox"><input type="radio" name="avatar_fonte" value="ficheiro" {{ $fonteFoto === 'ficheiro' ? 'checked' : '' }}> Carregar ficheiro</label>
          <label class="dashboard-checkbox"><input type="radio" name="avatar_fonte" value="link" {{ $fonteFoto === 'link' ? 'checked' : '' }}> Colar endereço (link)</label>
        </div>

        <div id="avatar-ficheiro" class="file-upload" data-accept="image" data-max-bytes="1048576"{{ $fonteFoto === 'link' ? ' hidden' : '' }}>
          <img class="file-upload-preview" hidden alt="Foto">
          <label class="file-upload-button" for="avatar"><i class="fa fa-folder-open" aria-hidden="true"></i> Escolher imagem</label>
          <div class="file-upload-meta">
            <span class="file-upload-name">Nenhum ficheiro selecionado</span>
            <small class="help-text">Recomendado: 150x150px. Formatos: JPG, PNG. Máx.: 1MB.</small>
          </div>
          <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp" aria-describedby="erro-avatar"{{ $fonteFoto === 'link' ? ' disabled' : '' }}>
          <button type="button" class="file-upload-remove">Remover</button>
          <p class="file-upload-erro" id="erro-avatar" role="alert"></p>
        </div>

        <div id="avatar-link" class="dashboard-form-group"{{ $fonteFoto === 'link' ? '' : ' hidden' }}>
          <label class="dashboard-form-label" for="avatar_link">Link da foto</label>
          <input class="dashboard-form-input" type="url" id="avatar_link" name="avatar_link" placeholder="https://…/foto.jpg" value="{{ old('avatar_link', ($avatarAtual && str_starts_with($avatarAtual, 'http')) ? $avatarAtual : '') }}" aria-describedby="erro-avatar"{{ $fonteFoto === 'link' ? '' : ' disabled' }}>
          <small class="help-text">Se a imagem já estiver publicada noutro site, cole o endereço — mostra-se tal como está, sem cópia.</small>
        </div>
        @error('avatar')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        @error('avatar_link')<p class="form-error" role="alert">{{ $message }}</p>@enderror
      </div>

      <div class="dashboard-form-actions">
        <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar perfil</button>
      </div>
    </form>
  </div>
</div>

@push('styles')
<style>
  .perfil-atual { display: flex; align-items: center; gap: 14px; padding: 12px; border: 1px dashed #D8D8D8; border-radius: 8px; background: #FCFCFC; margin-bottom: 12px; }
  .perfil-atual-img { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 1px solid #E0E0E0; }
  .perfil-atual-meta small { display: block; }
</style>
@endpush

@push('scripts')
<script>
  (function () {
    var radios = document.querySelectorAll('input[name="avatar_fonte"]');
    var ficheiro = document.getElementById('avatar-ficheiro');
    var link = document.getElementById('avatar-link');
    var inputFicheiro = document.getElementById('avatar');
    var inputLink = document.getElementById('avatar_link');
    if (!radios.length || !ficheiro || !link) return;

    function aplicar() {
      var valor = (document.querySelector('input[name="avatar_fonte"]:checked') || {}).value || 'ficheiro';
      ficheiro.hidden = valor !== 'ficheiro';
      link.hidden = valor !== 'link';
      if (inputFicheiro) inputFicheiro.disabled = valor !== 'ficheiro';
      if (inputLink) inputLink.disabled = valor !== 'link';
    }
    radios.forEach(function (r) { r.addEventListener('change', aplicar); });
    aplicar();
  })();
</script>
@endpush
@endsection
