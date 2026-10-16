@extends('layouts.dashboard')

@section('title', $titulo)

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ul><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route($conf['lista']) }}">{{ $rotuloLista }}</a></li><li class="active">{{ $migalha }}</li></ul>
@endsection

@section('content')

        <div class="card">
          <div class="card-head">
            <h3 class="card-title">{{ $titulo }}</h3>

          </div>
          <div class="card-body">
@php
    // Duas colunas equilibradas — `noticia-nova.html` tem 6+6, mas o nº de
    // campos varia por tipo (vagas têm requisitos/responsabilidades, projetos
    // mineral/fase/…), por isso o corte é calculado em vez de fixo.
    $colunas = array_chunk($campos, (int) ceil(count($campos) / 2));

    $action = $registo === null
        ? route('admin.conteudo.novo.store', ['tipo' => $tipo])
        : route('admin.conteudo.atualizar', ['tipo' => $tipo, 'id' => $registo->id]);
@endphp
<form class="dashboard-form" action="{{ $action }}" method="post" enctype="multipart/form-data">
              @csrf
              @if ($registo !== null)@method('PUT')@endif
              <div class="dashboard-form-row">
                @foreach ($colunas as $coluna)
                <div class="dashboard-form-col">
                @foreach ($coluna as $campo)
              <div class="dashboard-form-group">
                <label class="dashboard-form-label">{{ $campo['rotulo'] }}</label>
                @switch($campo['tipo'])
                  @case('text')
                    <input class="dashboard-form-input" type="text" name="{{ $campo['nome'] }}" placeholder="{{ $campo['placeholder'] ?? '' }}" value="{{ $campo['valor'] ?? '' }}" @required($campo['obrigatorio'] ?? false)>
                    @break

                  @case('textarea')
                    <textarea class="dashboard-form-textarea" name="{{ $campo['nome'] }}" rows="{{ $campo['linhas'] ?? 3 }}" placeholder="{{ $campo['placeholder'] ?? '' }}">{{ $campo['valor'] ?? '' }}</textarea>
                    @break

                  @case('datetime')
                    <input class="dashboard-form-input" type="datetime-local" name="{{ $campo['nome'] }}" value="{{ $campo['valor'] ?? '' }}">
                    @break

                  @case('date')
                    <input class="dashboard-form-input" type="date" name="{{ $campo['nome'] }}" value="{{ $campo['valor'] ?? '' }}">
                    @break

                  @case('number')
                    <input class="dashboard-form-input" type="number" name="{{ $campo['nome'] }}" step="0.01" min="0" value="{{ $campo['valor'] ?? '' }}">
                    @break

                  @case('categoria')
                    <select class="dashboard-form-select" name="categoria">
                      <option value="">Selecionar categoria…</option>
                      @foreach ($categorias as $categoria)
                      <option value="{{ $categoria->id }}" @selected(old('categoria', $campo['valor']) == $categoria->id)>{{ $categoria->name }}</option>
                      @endforeach
                    </select>
                    @break

                  @case('estado')
                    <select class="dashboard-form-select" name="estado">
                      <option value="">Selecionar estado…</option>
                      @foreach ($conf['estados'] as $chave => $estado)
                      <option value="{{ $chave }}" @selected(old('estado', $estadoAtual) === $chave)>{{ $estado[1] }}</option>
                      @endforeach
                    </select>
                    @break

                  @case('select')
                    <select class="dashboard-form-select" name="{{ $campo['nome'] }}">
                      <option value="">Selecionar…</option>
                      @foreach ($campo['opcoes'] as $valor => $rotulo)
                      <option value="{{ $valor }}" @selected((string) old($campo['nome'], $campo['valor'] ?? '') === (string) $valor)>{{ $rotulo }}</option>
                      @endforeach
                    </select>
                    @break

                  @case('ficheiro')
                    <div class="upload-box">PNG ou JPG até 4MB
                      @if (!empty($campo['actual']))<br><small>Imagem actual: {{ $campo['actual'] }}</small>@endif
                      <br><input type="file" name="{{ $campo['nome'] }}"></div>
                    @break
                @endswitch
                @error($campo['nome'])<p class="form-error">{{ $message }}</p>@enderror
              </div>
                @endforeach
                </div>
                @endforeach
              </div>
              <div class="dashboard-form-actions">
                <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Guardar</button>
                <button type="button" class="btn-secondary"><i class="fa fa-eye"></i> Pré-visualizar</button> <button type="button" class="btn-success"><i class="fa fa-paper-plane"></i> Publicar</button>
              </div>
          </form>
          </div>
        </div>
@endsection
