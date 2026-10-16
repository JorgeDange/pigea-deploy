@extends('layouts.dashboard')

@section('title', 'Perfil da empresa')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li class="active">Perfil da empresa</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Editar perfil da empresa</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" method="POST" action="{{ route('company.perfil.guardar') }}" enctype="multipart/form-data">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Nome da empresa</label>
                <input class="dashboard-form-input" type="text" name="empresa" placeholder="Nome da empresa" value="{{ old('empresa', $perfil?->name ?? auth()->user()->name) }}" required>
                @error('empresa')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">NIF</label>
                <input class="dashboard-form-input" type="text" name="nif" placeholder="NIF" value="{{ old('nif', $perfil?->nif) }}" required>
                @error('nif')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Setor</label>
                <select class="dashboard-form-select" name="setor">
                <option value="">Selecionar setor…</option>
                <option value="mineracao" @selected(old('setor', $perfil?->sector) === 'mineracao')>Mineração</option>
                <option value="agua" @selected(old('setor', $perfil?->sector) === 'agua')>Água</option>
                <option value="ambiente" @selected(old('setor', $perfil?->sector) === 'ambiente')>Ambiente</option>
                <option value="energia" @selected(old('setor', $perfil?->sector) === 'energia')>Energia</option>
                <option value="construcao" @selected(old('setor', $perfil?->sector) === 'construcao')>Construção e Obras</option>
                <option value="servicos" @selected(old('setor', $perfil?->sector) === 'servicos')>Serviços</option>
              </select>
                @error('setor')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Província</label>
                <select class="dashboard-form-select" name="provincia">
                <option value="">Selecionar província…</option>
                <option value="luanda" @selected(old('provincia', $perfil?->province) === 'luanda')>Luanda</option>
                <option value="benguela" @selected(old('provincia', $perfil?->province) === 'benguela')>Benguela</option>
                <option value="huila" @selected(old('provincia', $perfil?->province) === 'huila')>Huíla</option>
                <option value="malanje" @selected(old('provincia', $perfil?->province) === 'malanje')>Malanje</option>
                <option value="zaire" @selected(old('provincia', $perfil?->province) === 'zaire')>Zaire</option>
              </select>
                @error('provincia')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Endereço</label>
                <input class="dashboard-form-input" type="text" name="endereco" placeholder="Rua, bairro, município" value="{{ old('endereco', $perfil?->address) }}">
                @error('endereco')<div class="form-error">{{ $message }}</div>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Email</label>
                <input class="dashboard-form-input" type="email" name="email" placeholder="geral@empresa.ao" value="{{ old('email', $perfil?->email ?? auth()->user()->email) }}" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Telefone</label>
                <input class="dashboard-form-input" type="tel" name="telefone" placeholder="+244 000 000 000" value="{{ old('telefone', $perfil?->phone) }}">
                @error('telefone')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Website</label>
                <input class="dashboard-form-input" type="text" name="website" placeholder="https://…" value="{{ old('website', $perfil?->website) }}">
                @error('website')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Descrição da empresa</label>
                <textarea class="dashboard-form-textarea" name="descricao" rows="4" placeholder="Descreva a empresa, a sua atuação e projetos…">{{ old('descricao', $perfil?->description) }}</textarea>
                @error('descricao')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Logótipo</label>
                <div class="upload-box">PNG ou JPG até 2MB<br><input type="file" name="logo"></div>
                @error('logo')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Certificações</label>
                <div class="upload-box">PDF ou imagem (pode selecionar vários)<br><input type="file" name="certificacoes" multiple></div>
                @error('certificacoes')<div class="form-error">{{ $message }}</div>@enderror
              </div>
                </div>
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Localização no mapa</label>
                <div class="map-box">Espaço reservado para o mapa (Leaflet + PostGIS na Fase 3)</div>
                
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar alterações</button>
                <button type="button" class="btn-ghost">Cancelar</button>
              </div>
          </form>
          </div>
        </div>
@endsection
