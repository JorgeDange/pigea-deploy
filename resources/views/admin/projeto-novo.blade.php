@extends('layouts.dashboard')

@section('title', 'Novo projeto')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.projetos') }}">Projetos</a></li><li class="active">Novo</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Dados do projeto</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" action="{{ route('admin.projeto-novo.store') }}" method="post" enctype="multipart/form-data">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Nome do projeto</label>
                <input class="dashboard-form-input" type="text" name="nome" placeholder="Nome do projeto" value="{{ old('nome') }}" required>
                @error('nome')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Mineral</label>
                <select class="dashboard-form-select" name="minereral">
                <option value="">Selecionar mineral…</option>
                <option value="ferro" @selected(old('minereral') === 'ferro')>Ferro</option>
                <option value="ouro" @selected(old('minereral') === 'ouro')>Ouro</option>
                <option value="diamantes" @selected(old('minereral') === 'diamantes')>Diamantes</option>
                <option value="litio" @selected(old('minereral') === 'litio')>Lítio</option>
                <option value="manganes" @selected(old('minereral') === 'manganes')>Manganês</option>
                <option value="cobre" @selected(old('minereral') === 'cobre')>Cobre</option>
                <option value="fosfatos" @selected(old('minereral') === 'fosfatos')>Fosfatos</option>
              </select>
                @error('minereral')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Província</label>
                <select class="dashboard-form-select" name="provincia">
                <option value="">Selecionar província…</option>
                <option value="huila" @selected(old('provincia') === 'huila')>Huíla</option>
                <option value="namibe" @selected(old('provincia') === 'namibe')>Namibe</option>
                <option value="uige" @selected(old('provincia') === 'uige')>Uíge</option>
                <option value="cuanza-sul" @selected(old('provincia') === 'cuanza-sul')>Cuanza Sul</option>
                <option value="benguela" @selected(old('provincia') === 'benguela')>Benguela</option>
                <option value="cuando-cubango" @selected(old('provincia') === 'cuando-cubango')>Cuando Cubango</option>
              </select>
                @error('provincia')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Fase</label>
                <select class="dashboard-form-select" name="fase">
                <option value="">Selecionar fase…</option>
                <option value="prospeccao" @selected(old('fase') === 'prospeccao')>Prospecção</option>
                <option value="exploracao" @selected(old('fase') === 'exploracao')>Exploração</option>
                <option value="producao" @selected(old('fase') === 'producao')>Produção</option>
              </select>
                @error('fase')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Operador</label>
                <input class="dashboard-form-input" type="text" name="operador" placeholder="Nome do operador" value="{{ old('operador') }}">
                @error('operador')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Área (km²)</label>
                <input class="dashboard-form-input" type="number" name="area" placeholder="Ex.: 120" value="{{ old('area') }}">
                @error('area')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Estado</label>
                <select class="dashboard-form-select" name="estado">
                <option value="">Selecionar estado…</option>
                <option value="ativo" @selected(old('estado') === 'ativo')>Ativo</option>
                <option value="em-analise" @selected(old('estado') === 'em-analise')>Em análise</option>
                <option value="expirada" @selected(old('estado') === 'expirada')>Expirada</option>
              </select>
                @error('estado')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Descrição</label>
                <textarea class="dashboard-form-textarea" name="descricao" rows="4" placeholder="Descrição do projeto, contexto e objetivos…">{{ old('descricao') }}</textarea>
                @error('descricao')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Documentos</label>
                <div class="upload-box">PDF (pode selecionar vários)<br><input type="file" name="documentos[]" multiple></div>
                @error('documentos')<p class="form-error">{{ $message }}</p>@enderror
                @error('documentos.*')<p class="form-error">{{ $message }}</p>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Imagens</label>
                <div class="upload-box">PNG ou JPG (pode selecionar vários)<br><input type="file" name="imagens[]" multiple></div>
                @error('imagens')<p class="form-error">{{ $message }}</p>@enderror
                @error('imagens.*')<p class="form-error">{{ $message }}</p>@enderror
              </div>
                </div>
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Localização no mapa</label>
                <div class="map-box">Espaço reservado para o mapa (Leaflet + PostGIS na Fase 3)</div>
                
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                <button type="button" class="btn-success"><i class="fa fa-paper-plane"></i> Publicar</button>
              </div>
          </form>
          </div>
        </div>
@endsection
