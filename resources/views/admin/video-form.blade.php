@extends('layouts.dashboard')

@section('title', $titulo)

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.videos') }}">Vídeos</a></li><li class="active" aria-current="page">{{ $video === null ? 'Novo' : 'Editar' }}</li></ol>
@endsection

@section('content')
<div class="card">
  <div class="card-head">
    <h3 class="card-title">{{ $titulo }}</h3>
  </div>
  <div class="card-body">
    @php
      $action = $video === null
        ? route('admin.videos.novo.store')
        : route('admin.videos.atualizar', ['id' => $video->id]);
    @endphp
    <form class="dashboard-form" action="{{ $action }}" method="post" enctype="multipart/form-data">
      @csrf
      @if ($video !== null)@method('PUT')@endif
      <div class="dashboard-form-row">
        <div class="dashboard-form-col">
          <div class="dashboard-form-group">
            <label class="dashboard-form-label">Título</label>
            <input class="dashboard-form-input" type="text" name="titulo" placeholder="Título do vídeo" value="{{ old('titulo', $video?->titulo) }}" required>
            @error('titulo')<p class="form-error" role="alert">{{ $message }}</p>@enderror
          </div>
          <div class="dashboard-form-group">
            <label class="dashboard-form-label">Link do vídeo (YouTube/Vimeo)</label>
            <input class="dashboard-form-input" type="url" name="url" placeholder="https://www.youtube.com/watch?v=…" value="{{ old('url', $video?->url) }}" required>
            <small class="help-text">Colamos o link do vídeo (YouTube, Vimeo, Facebook, mp4...) — sem upload de ficheiros. O frontend mostra a reprodução incorporada com o link usado.</small>
            @error('url')<p class="form-error" role="alert">{{ $message }}</p>@enderror
          </div>
          <div class="dashboard-form-group">
            <label class="dashboard-form-label">Resumo (opcional)</label>
            <textarea class="dashboard-form-textarea" name="resumo" rows="3" placeholder="Linha curta que resume o vídeo — aparece com as datas nas secções de vídeo">{{ old('resumo', $video?->resumo) }}</textarea>
            @error('resumo')<p class="form-error" role="alert">{{ $message }}</p>@enderror
          </div>
        </div>
        <div class="dashboard-form-col">
          <div class="dashboard-form-group">
            <label class="dashboard-form-label">Miniatura (opcional)</label>
            <input class="dashboard-form-input" type="url" name="imagem" placeholder="https://…/imagem.jpg (ou deixa vazio para a thumb automática do YouTube)" value="{{ old('imagem', $video?->imagem) }}">
            <small class="help-text">Se estiver vazia, mostra a miniatura do próprio vídeo no YouTube.</small>
            @if ($video?->imagem)
              <img src="{{ $video->imagem }}" alt="Pré-visualização" style="max-width: 260px; margin-top: 10px; border-radius: 6px;">
            @endif
            @error('imagem')<p class="form-error" role="alert">{{ $message }}</p>@enderror
          </div>
          <div class="dashboard-form-group">
            <label class="dashboard-form-label">Publicado em</label>
            <input class="dashboard-form-input" type="date" name="published_at" value="{{ old('published_at', ($video?->published_at ?? now())->format('Y-m-d')) }}">
            <small class="help-text">Serve de ordenação — os vídeos mais recentes aparecem primeiro no site.</small>
            @error('published_at')<p class="form-error" role="alert">{{ $message }}</p>@enderror
          </div>
        </div>
      </div>
      <div class="dashboard-form-actions">
        <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
        <a class="btn-ghost" href="{{ route('admin.videos') }}">Cancelar</a>
      </div>
    </form>
  </div>
</div>
@endsection
