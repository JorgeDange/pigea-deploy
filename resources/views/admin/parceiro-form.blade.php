@extends('layouts.dashboard')

@section('title', $parceiro === null ? 'Novo parceiro — Parceiros' : 'Editar parceiro — Parceiros')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.parceiros') }}">Parceiros</a></li><li class="active" aria-current="page">{{ $parceiro === null ? 'Novo parceiro' : 'Editar parceiro' }}</li></ol>
@endsection

@section('content')

@php
    $ativoCheck = (bool) old('ativo', $parceiro?->ativo ?? true);
    $tipoSelecionado = old('tipo', $parceiro?->tipo ?? 'empresa');
@endphp

<form class="editor-grid" action="{{ $parceiro === null ? route('admin.parceiros.novo.store') : route('admin.parceiros.atualizar', ['id' => $parceiro->id]) }}" method="post" id="parceiro-form" data-loading-submit>
    @csrf
    @if ($parceiro !== null) @method('PUT') @endif

    <div class="editor-col">
        <div class="card">
            <div class="card-head">
                <h2 class="card-title">{{ $parceiro === null ? 'Novo parceiro' : 'Editar parceiro' }}</h2>
                <span class="status-badge {{ $ativoCheck ? 'active' : 'neutral' }}">{{ $ativoCheck ? 'Ativo' : 'Inativo' }}</span>
            </div>
            <div class="card-body">
                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="nome">Nome *</label>
                    <input class="dashboard-form-input" type="text" id="nome" name="nome" placeholder="Ex.: Minex / Dra. Ana Maria" value="{{ old('nome', $parceiro?->nome) }}" required autofocus aria-describedby="erro-nome">
                    @error('nome')<p class="form-error" id="erro-nome" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="tipo">Tipo de parceiro *</label>
                    <select class="dashboard-form-select" id="tipo" name="tipo" required aria-describedby="ajuda-tipo erro-tipo">
                        @foreach (\App\Support\Institucional::TIPOS_PARCEIRO as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected((string) $tipoSelecionado === (string) $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                    <small class="help-text" id="ajuda-tipo">«Empresas» aparece em secção própria (logo); «Profissionais» noutra (foto/perfil).</small>
                    @error('tipo')<p class="form-error" id="erro-tipo" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="imagem_url">Imagem (logo ou foto) <small class="form-label-hint">(URL — não carrega ficheiros)</small></label>
                    <input class="dashboard-form-input" type="url" id="imagem_url" name="imagem_url" placeholder="https://…" value="{{ old('imagem_url', $parceiro?->imagem_url) }}" aria-describedby="erro-imagem_url">
                    <small class="help-text">Empresas: logo horizontal (~400×160 px). Profissionais: retrato de perfil. Se vazio, mostram-se as iniciais no cartão.</small>
                    @error('imagem_url')<p class="form-error" id="erro-imagem_url" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="detalhes">Detalhes <small class="form-label-hint">(opcional)</small></label>
                    <textarea class="dashboard-form-input" id="detalhes" name="detalhes" rows="4" maxlength="2000" placeholder="Ex.: parceiro âncora do programa Minex / consultoria em prospeção e pesquisa." aria-describedby="erro-detalhes">{{ old('detalhes', $parceiro?->detalhes) }}</textarea>
                    @error('detalhes')<p class="form-error" id="erro-detalhes" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-row" style="gap:16px">
                    <div class="dashboard-form-col">
                        <label class="dashboard-form-label" for="ordem">Ordem <small class="form-label-hint">(menor aparece primeiro)</small></label>
                        <input class="dashboard-form-input" type="number" id="ordem" name="ordem" min="0" max="10000" value="{{ old('ordem', (string) ($parceiro?->ordem ?? 0)) }}" aria-describedby="erro-ordem">
                        @error('ordem')<p class="form-error" id="erro-ordem" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-col">
                        <label class="dashboard-checkbox" style="margin-top:32px"><input type="checkbox" name="ativo" value="1" @checked($ativoCheck)> Ativo — mostrar em /parceiros</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="editor-col">
        <div class="card editor-actions">
            <div class="card-head"><h2 class="card-title">Ações</h2></div>
            <div class="editor-actions-body">
                <button type="submit" class="btn-primary"><i class="fa fa-save" aria-hidden="true"></i> Guardar parceiro</button>
                <a href="{{ route('admin.parceiros') }}" class="btn-secondary"><i class="fa fa-times" aria-hidden="true"></i> Cancelar</a>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><h2 class="card-title">Cartão público</h2></div>
            <div class="card-body">
                <p class="help-text">Como aparecerá em <a href="{{ route('parceiros') }}" target="_blank" rel="noopener">pigea.ao/parceiros</a>:</p>
                <div class="help-text" style="margin-top:10px;padding:14px;border:1px solid #E0E0E0;border-radius:8px;text-align:center">
                    <strong>{{ old('nome', $parceiro?->nome) ?? 'Nome do parceiro' }}</strong><br>
                    <span class="help-text">{{ \App\Support\Institucional::tipoParceiro((string) $tipoSelecionado) }}</span>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
