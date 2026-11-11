@extends('layouts.dashboard')

@section('title', $titulo)

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.banners') }}">Publicidade</a></li><li class="active" aria-current="page">{{ $banner === null ? 'Novo' : 'Editar' }}</li></ol>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">{{ $titulo }}</h3>
          </div>
          <div class="card-body">
@php
    $action = $banner === null
        ? route('admin.banners.novo.store')
        : route('admin.banners.atualizar', ['id' => $banner->id]);
@endphp
<form class="dashboard-form" action="{{ $action }}" method="post" enctype="multipart/form-data">
              @csrf
              @if ($banner !== null)@method('PUT')@endif
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Título</label>
                    <input class="dashboard-form-input" type="text" name="titulo" placeholder="Título do banner" value="{{ old('titulo', $banner?->titulo) }}" required>
                    @error('titulo')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Imagem — upload</label>
                    <div class="upload-box">PNG ou JPG até 4MB
                      @if ($banner?->imagem_url && ! str_starts_with($banner->imagem_url, 'http'))<br><small>Imagem atual: {{ $banner->imagem_url }}</small>@endif
                      <br><input type="file" name="imagem_upload"></div>
                    <small class="help-text">Dimensão exata: 970x250px (Leaderboard). Formatos: JPG, PNG. Máx: 4MB.</small>
                    @error('imagem_upload')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Imagem — ou link</label>
                    <input class="dashboard-form-input" type="url" name="imagem_link" placeholder="https://… (usado se não houver upload)" value="{{ old('imagem_link', $banner?->imagem_url) }}">
                    @error('imagem_link')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                </div>
                <div class="dashboard-form-col">
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Link de Destino (opcional)</label>
                    <input class="dashboard-form-input" type="url" name="link_destino" placeholder="https://… (para onde o banner leva)" value="{{ old('link_destino', $banner?->link_destino) }}">
                    @error('link_destino')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Posição</label>
                    <select class="dashboard-form-select" name="posicao">
                      <option value="">Selecionar…</option>
                      @foreach (['topo' => 'Topo', 'sidebar' => 'Sidebar', 'rodape' => 'Rodapé'] as $valor => $rotulo)
                      <option value="{{ $valor }}" @selected((string) old('posicao', $banner?->posicao) === (string) $valor)>{{ $rotulo }}</option>
                      @endforeach
                    </select>
                    @error('posicao')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Data de Início (opcional)</label>
                    <input class="dashboard-form-input" type="date" name="data_inicio" value="{{ old('data_inicio', $banner?->data_inicio?->format('Y-m-d')) }}">
                    @error('data_inicio')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Data de Fim (opcional)</label>
                    <input class="dashboard-form-input" type="date" name="data_fim" value="{{ old('data_fim', $banner?->data_fim?->format('Y-m-d')) }}">
                    @error('data_fim')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">
                      <input type="checkbox" name="ativo" value="1" @checked((bool) old('ativo', $banner?->ativo ?? true))> Ativo
                    </label>
                  </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                <a href="{{ route('admin.banners') }}" class="btn-secondary">Cancelar</a>
              </div>
          </form>
          </div>
        </div>
@endsection
