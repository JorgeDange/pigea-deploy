@extends('layouts.dashboard')

@section('title', 'Nova notícia')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.noticias') }}">Notícias</a></li><li class="active">Nova</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Dados da notícia</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.noticia-nova.store') }}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Título</label>
                <input class="dashboard-form-input" type="text" name="titulo" placeholder="Título da notícia" value="{{ old('titulo') }}" required>
                @error('titulo')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Slug (URL)</label>
                <input class="dashboard-form-input" type="text" name="slug" placeholder="slug-da-noticia" value="{{ old('slug') }}">
                @error('slug')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Categoria</label>
                <select class="dashboard-form-select" name="categoria">
                <option value="">Selecionar categoria…</option>
                @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria') == $categoria->id)>{{ $categoria->name }}</option>
                @endforeach
              </select>
                @error('categoria')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Província</label>
                <select class="dashboard-form-select" name="provincia">
                <option value="">Selecionar província…</option>
                <option value="luanda" @selected(old('provincia') === 'luanda')>Luanda</option>
                <option value="benguela" @selected(old('provincia') === 'benguela')>Benguela</option>
                <option value="huila" @selected(old('provincia') === 'huila')>Huíla</option>
                <option value="namibe" @selected(old('provincia') === 'namibe')>Namibe</option>
                <option value="nacional" @selected(old('provincia') === 'nacional')>Nacional</option>
              </select>
                @error('provincia')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Estado</label>
                <select class="dashboard-form-select" name="estado">
                <option value="">Selecionar estado…</option>
                <option value="rascunho" @selected(old('estado') === 'rascunho')>Rascunho</option>
                <option value="publicar" @selected(old('estado') === 'publicar')>Publicar</option>
              </select>
                @error('estado')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Agendamento</label>
                <input class="dashboard-form-input" type="datetime-local" name="agendamento" value="{{ old('agendamento') }}">
                @error('agendamento')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Excerto</label>
                <textarea class="dashboard-form-textarea" name="excerto" rows="3" placeholder="Resumo da notícia que aparece nas listagens…">{{ old('excerto') }}</textarea>
                @error('excerto')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Conteúdo</label>
                <div class="editor-box">Editor rich-text (reservado para a Fase 3)<textarea class="dashboard-form-textarea" name="conteudo" rows="7" placeholder="Corpo da notícia…">{{ old('conteudo') }}</textarea></div>
                @error('conteudo')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Imagem de destaque</label>
                <div class="upload-box">PNG ou JPG até 4MB<br><input type="file" name="imagem"></div>
                @error('imagem')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Tags</label>
                <input class="dashboard-form-input" type="text" name="tags" placeholder="separadas por vírgula" value="{{ old('tags') }}">
                @error('tags')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">SEO — meta title</label>
                <input class="dashboard-form-input" type="text" name="meta_title" placeholder="Título para os motores de busca (até 60 caracteres)" value="{{ old('meta_title') }}">
                @error('meta_title')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">SEO — meta description</label>
                <textarea class="dashboard-form-textarea" name="meta_description" rows="2" placeholder="Descrição para os motores de busca (até 160 caracteres)">{{ old('meta_description') }}</textarea>
                @error('meta_description')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                <button type="button" class="btn-secondary"><i class="fa fa-eye"></i> Pré-visualizar</button> <button type="button" class="btn-success"><i class="fa fa-paper-plane"></i> Publicar</button>
              </div>
          </form>
          </div>
        </div>
@endsection
