@extends('layouts.dashboard')

@section('title', $titulo)

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.documentos') }}">Documentos</a></li><li class="active" aria-current="page">{{ $documento === null ? 'Novo' : 'Editar' }}</li></ol>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">{{ $titulo }}</h3>
          </div>
          <div class="card-body">
@php
    $action = $documento === null
        ? route('admin.documentos.novo.store')
        : route('admin.documentos.atualizar', ['id' => $documento->id]);
@endphp
<form class="dashboard-form" action="{{ $action }}" method="post" enctype="multipart/form-data">
              @csrf
              @if ($documento !== null)@method('PUT')@endif
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Título</label>
                    <input class="dashboard-form-input" type="text" name="titulo" placeholder="Título" value="{{ old('titulo', $documento?->titulo) }}" required>
                    @error('titulo')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Descrição</label>
                    <textarea class="dashboard-form-textarea" name="descricao" rows="3" placeholder="Descrição curta (máx. 500 caracteres)…">{{ old('descricao', $documento?->descricao) }}</textarea>
                    @error('descricao')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Tipo</label>
                    <select class="dashboard-form-select" name="tipo">
                      <option value="">Selecionar…</option>
                      @foreach (['artigo' => 'Artigo', 'documento' => 'Documento', 'lei' => 'Lei'] as $valor => $rotulo)
                      <option value="{{ $valor }}" @selected((string) old('tipo', $documento?->tipo) === (string) $valor)>{{ $rotulo }}</option>
                      @endforeach
                    </select>
                    @error('tipo')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Ficheiro para descarregar</label>
                    <input class="dashboard-form-input" type="url" name="link_download" placeholder="https://… (PDF/artigo)" value="{{ old('link_download', $documento?->link_download) }}" required>
                    @error('link_download')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                </div>
                <div class="dashboard-form-col">
                  <div class="dashboard-form-group">
                    <label class="dashboard-form-label">Áreas de Atuação</label>
                    @foreach ($areasLista as $area)
                    <label style="display: block; margin: 4px 0;">
                      <input type="checkbox" name="areas_atuacao[]" value="{{ $area }}" @checked(in_array($area, (array) old('areas_atuacao', $areasSelecionadas)))> {{ $area }}
                    </label>
                    @endforeach
                    @error('areas_atuacao')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                  </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                <a href="{{ route('admin.documentos') }}" class="btn-secondary">Cancelar</a>
              </div>
          </form>
          </div>
        </div>
@endsection
