@extends('layouts.dashboard')

@section('title', 'Editar perfil')

@section('sidebar')
<x-sidebar-user />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('user.dashboard') }}">Painel</a></li><li class="active">Perfil</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Editar perfil</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" method="POST" action="{{ route('user.perfil.guardar') }}">
              @csrf
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Nome completo</label>
                <input class="dashboard-form-input" type="text" name="nome" placeholder="O seu nome completo" value="{{ old('nome', $user->name) }}" required>
                @error('nome')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Email</label>
                <input class="dashboard-form-input" type="email" name="email" placeholder="o.seu@email.com" value="{{ old('email', $user->email) }}" required>
                @error('email')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Telefone</label>
                <input class="dashboard-form-input" type="tel" name="telefone" placeholder="+244 000 000 000" value="{{ old('telefone', $user->phone) }}">
                @error('telefone')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Província</label>
                <select class="dashboard-form-select" name="provincia">
                <option value="">Selecionar província…</option>
                <option value="luanda" @selected(old('provincia', $user->province) === 'luanda')>Luanda</option>
                <option value="benguela" @selected(old('provincia', $user->province) === 'benguela')>Benguela</option>
                <option value="huila" @selected(old('provincia', $user->province) === 'huila')>Huíla</option>
                <option value="malanje" @selected(old('provincia', $user->province) === 'malanje')>Malanje</option>
                <option value="cuando-cubango" @selected(old('provincia', $user->province) === 'cuando-cubango')>Cuando Cubango</option>
                <option value="zaire" @selected(old('provincia', $user->province) === 'zaire')>Zaire</option>
              </select>
                @error('provincia')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Breve apresentação</label>
                <textarea class="dashboard-form-textarea" name="bio" rows="4" placeholder="Fale sobre a sua experiência nas geociências…">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')<div class="form-error">{{ $message }}</div>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Foto de perfil</label>
                <div class="upload-box">PNG ou JPG até 2MB<br><input type="file" name="foto"></div>
                
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Currículo (CV)</label>
                <div class="upload-box">PDF até 5MB<br><input type="file" name="cv"></div>
                
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Áreas de interesse</label>
                                <label class="dashboard-form-checkbox"><input type="checkbox" checked> <span>Mineração</span></label>                <label class="dashboard-form-checkbox"><input type="checkbox" checked> <span>Água</span></label>                <label class="dashboard-form-checkbox"><input type="checkbox"> <span>Ambiente</span></label>                <label class="dashboard-form-checkbox"><input type="checkbox"> <span>Investigação</span></label>
                
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">LinkedIn</label>
                <input class="dashboard-form-input" type="text" name="linkedin" placeholder="https://linkedin.com/in/…">
                
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Outra rede social</label>
                <input class="dashboard-form-input" type="text" name="rede" placeholder="https://…">
                
              </div>
                </div>
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar alterações</button>
                
              </div>
          </form>
          </div>
        </div>
@endsection
