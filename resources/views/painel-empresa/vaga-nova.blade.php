@extends('layouts.dashboard')

@section('title', 'Publicar nova vaga')

@section('sidebar')
<x-sidebar-company />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('home') }}">Início</a></li><li><a href="{{ route('company.dashboard') }}">Painel</a></li><li><a href="{{ route('company.vagas') }}">Vagas</a></li><li class="active">Nova vaga</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">Dados da vaga</h3>
            
          </div>
          <div class="card-body">
<form class="dashboard-form" method="POST" action="{{ route('company.vaga-nova.guardar') }}">
              @csrf
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Título da vaga</label>
                <input class="dashboard-form-input" type="text" name="titulo" placeholder="Ex.: Geólogo Sénior" value="{{ old('titulo') }}" required>
                @error('titulo')<div class="form-error">{{ $message }}</div>@enderror
              </div>
              <div class="dashboard-form-row">
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Categoria</label>
                <select class="dashboard-form-select" name="categoria">
                <option value="">Selecionar categoria…</option>
                <option value="geologia" @selected(old('categoria') === 'geologia')>Geologia</option>
                <option value="mineracao" @selected(old('categoria') === 'mineracao')>Mineração</option>
                <option value="ambiente" @selected(old('categoria') === 'ambiente')>Ambiente</option>
                <option value="agua" @selected(old('categoria') === 'agua')>Água e Hidrogeologia</option>
                <option value="sig" @selected(old('categoria') === 'sig')>Cartografia / SIG</option>
                <option value="engenharia" @selected(old('categoria') === 'engenharia')>Engenharia</option>
              </select>
                @error('categoria')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Tipo de contrato</label>
                <select class="dashboard-form-select" name="tipo">
                <option value="">Selecionar tipo…</option>
                <option value="efetivo" @selected(old('tipo') === 'efetivo')>Efetivo</option>
                <option value="temporario" @selected(old('tipo') === 'temporario')>Temporário</option>
                <option value="estagio" @selected(old('tipo') === 'estagio')>Estágio</option>
                <option value="consultoria" @selected(old('tipo') === 'consultoria')>Consultoria</option>
              </select>
                @error('tipo')<div class="form-error">{{ $message }}</div>@enderror
              </div>
                </div>
                <div class="dashboard-form-col">
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Província</label>
                <select class="dashboard-form-select" name="provincia">
                <option value="">Selecionar província…</option>
                <option value="luanda" @selected(old('provincia') === 'luanda')>Luanda</option>
                <option value="benguela" @selected(old('provincia') === 'benguela')>Benguela</option>
                <option value="huila" @selected(old('provincia') === 'huila')>Huíla</option>
                <option value="malanje" @selected(old('provincia') === 'malanje')>Malanje</option>
                <option value="cuando-cubango" @selected(old('provincia') === 'cuando-cubango')>Cuando Cubango</option>
              </select>
                @error('provincia')<div class="form-error">{{ $message }}</div>@enderror
              </div>              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Prazo de candidatura</label>
                <input class="dashboard-form-input" type="date" name="prazo" value="{{ old('prazo') }}" required>
                @error('prazo')<div class="form-error">{{ $message }}</div>@enderror
              </div>
                </div>
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Descrição da vaga</label>
                <textarea class="dashboard-form-textarea" name="descricao" rows="4" placeholder="Descreva a função, o contexto e o perfil procurado…">{{ old('descricao') }}</textarea>
                @error('descricao')<div class="form-error">{{ $message }}</div>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Requisitos</label>
                <textarea class="dashboard-form-textarea" name="requisitos" rows="4" placeholder="Formação, experiência, competências técnicas…">{{ old('requisitos') }}</textarea>
                @error('requisitos')<div class="form-error">{{ $message }}</div>@enderror
              </div>
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">Responsabilidades</label>
                <textarea class="dashboard-form-textarea" name="responsabilidades" rows="4" placeholder="Principais responsabilidades da função…">{{ old('responsabilidades') }}</textarea>
                @error('responsabilidades')<div class="form-error">{{ $message }}</div>@enderror
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-paper-plane"></i> Publicar</button>
                <button type="button" class="btn-secondary"><i class="fa fa-save"></i> Guardar rascunho</button>
              </div>
          </form>
          </div>
        </div>
@endsection
