@extends('layouts.dashboard')

@section('title', $titulo)

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.destaques') }}">Destaques</a></li><li class="active" aria-current="page">{{ $destaque === null ? 'Novo' : 'Editar' }} destaque</li></ol>
@endsection

@section('content')
<h1 class="sr-only">{{ $titulo }}</h1>

@php
    $imagemUrl = $destaque?->imagem_url;
    $imagemSrc = ($imagemUrl && str_starts_with($imagemUrl, 'http')) ? $imagemUrl : ($imagemUrl ? asset($imagemUrl) : '');
    $fonteImagem = old('imagem_fonte', ($imagemUrl && str_starts_with($imagemUrl, 'http')) ? 'link' : 'ficheiro');
    $escopoAtual = old('escopo', $destaque?->escopo ?? 'site');
    $escopoLabel = \App\Models\Destaque::ESCOPOS[$escopoAtual] ?? 'Escopo: selecionar';
@endphp

<form class="editor-grid" action="{{ $destaque === null ? route('admin.destaques.novo.store') : route('admin.destaques.atualizar', ['id' => $destaque->id]) }}" method="post" enctype="multipart/form-data" id="destaque-form" data-loading-submit>
    @csrf
    @if ($destaque !== null) @method('PUT') @endif

    {{-- ========== Coluna principal =============== --}}
    <div class="editor-col">
        <div class="card">
            <div class="card-head">
                <h2 class="card-title">{{ $titulo }}</h2>
                @php
                    $estadoLabel = ($destaque?->ativo ?? true) ? 'Ativo' : 'Inativo';
                    $estadoClasse = ($destaque?->ativo ?? true) ? 'active' : 'neutral';
                @endphp
                <span class="status-badge {{ $estadoClasse }}">{{ $estadoLabel }}</span>
            </div>
            <div class="card-body">
                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="titulo">Título</label>
                    <input class="dashboard-form-input" type="text" id="titulo" name="titulo" placeholder="Ex.: Campanha Minex Angola 2026" value="{{ old('titulo', $destaque?->titulo) }}" required autofocus aria-describedby="erro-titulo">
                    @error('titulo')<p class="form-error" id="erro-titulo" role="alert">{{ $message }}</p>@enderror
                </div>

                {{-- Imagem: upload assíncrono OU link externo --}}
                <fieldset class="dashboard-form-group" aria-describedby="erro-imagem">
                    <legend class="dashboard-form-label">Imagem do destaque</legend>

                    <div class="cover-source">
                        <label class="dashboard-checkbox"><input type="radio" name="imagem_fonte" value="ficheiro" {{ $fonteImagem === 'ficheiro' ? 'checked' : '' }}> Ficheiro (upload)</label>
                        <label class="dashboard-checkbox"><input type="radio" name="imagem_fonte" value="link" {{ $fonteImagem === 'link' ? 'checked' : '' }}> Link externo (URL)</label>
                    </div>

                    <div id="imagem-ficheiro" class="upload-async" role="group" aria-label="Carregar imagem" data-upload-url="{{ route('admin.banner.imagem.store') }}"{{ $fonteImagem === 'link' ? ' hidden' : '' }}>
                        @if ($imagemSrc && ! str_starts_with($imagemUrl, 'http'))
                            <div class="upload-async-preview">
                                <img class="upload-async-img" src="{{ $imagemSrc }}" alt="Imagem atual do destaque">
                                <p class="upload-async-current">Imagem atual: <strong>{{ basename($imagemUrl) }}</strong></p>
                            </div>
                        @endif
                        <button type="button" class="upload-async-button"><i class="fa fa-folder-open" aria-hidden="true"></i> Escolher imagem</button>
                        <input type="file" name="imagem_upload_temporario" accept="image/jpeg,image/png,image/webp" aria-hidden="true" tabindex="-1">

                        <div class="upload-async-meta">
                            <span class="upload-async-name">{{ $imagemSrc ? 'Ficheiro atual carregado' : 'Nenhuma imagem selecionada' }}</span>
                            <span class="upload-progress" hidden><span class="upload-progress-bar"></span></span>
                            <span class="upload-progress-value"></span>
                        </div>
                        <button type="button" class="upload-async-remove" {{ $imagemSrc && ! str_starts_with($imagemUrl, 'http') ? '' : 'hidden' }}>Remover</button>
                        <p class="upload-async-erro" role="alert"></p>
                    </div>

                    <div id="imagem-link" class="dashboard-form-group"{{ $fonteImagem === 'link' ? '' : ' hidden' }}>
                        <label class="dashboard-form-label" for="imagem_link_url">URL da imagem</label>
                        <input class="dashboard-form-input" type="url" id="imagem_link_url" placeholder="https://…/destaque.jpg" value="{{ old('imagem_fonte') === 'link' ? old('imagem_link') : (str_starts_with((string) $imagemUrl, 'http') ? $imagemUrl : '') }}" aria-describedby="erro-imagem">
                        <small class="help-text">A imagem não é descarregada; o site mostra o URL diretamente.</small>
                    </div>
                    <input type="hidden" name="imagem_link" id="imagem_link" value="{{ old('imagem_fonte') === 'link' && old('imagem_link') ? old('imagem_link') : $imagemUrl }}">

                    <small class="help-text">Recomendado: 1200 × 600 px (≈2:1). JPG, PNG, WebP. Máx.: 4MB.</small>
                    <small class="help-text"><i class="fa fa-rotate" aria-hidden="true"></i> O widget da página inicial/projeto roda os destaques de 4 em 4 segundos.</small>
                    @error('imagem_link')<p class="form-error" id="erro-imagem" role="alert">{{ $message }}</p>@enderror
                </fieldset>

                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="link_destino">Link de destino <small class="form-label-hint">(opcional)</small></label>
                    <input class="dashboard-form-input" type="url" id="link_destino" name="link_destino" placeholder="https://… (para onde o destaque leva)" value="{{ old('link_destino', $destaque?->link_destino) }}" aria-describedby="erro-link-destino">
                    @error('link_destino')<p class="form-error" id="erro-link-destino" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-row" style="gap:16px">
                    <div class="dashboard-form-col">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label" for="escopo">Onde aparece</label>
                            <select class="dashboard-form-select" id="escopo" name="escopo" required aria-describedby="erro-escopo">
                                <option value="">Selecionar…</option>
                                @foreach ($escopos as $valor => $rotulo)
                                <option value="{{ $valor }}" @selected((string) old('escopo', $destaque?->escopo) === (string) $valor)>{{ $rotulo }}</option>
                                @endforeach
                            </select>
                            <small class="help-text">"Todo o site" → home e páginas interiores. "Só página inicial" → widget da home. "Só páginas interiores" → widget da página de projeto.</small>
                            @error('escopo')<p class="form-error" id="erro-escopo" role="alert">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="dashboard-form-col">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label" for="ordem">Ordem <small class="form-label-hint">(1 = primeiro)</small></label>
                            <input class="dashboard-form-input" type="number" id="ordem" name="ordem" min="1" max="20" value="{{ old('ordem', $destaque?->ordem ?? 1) }}" aria-describedby="erro-ordem">
                            <small class="help-text">Não mais de 3 destaques em simultâneo.</small>
                            @error('ordem')<p class="form-error" id="erro-ordem" role="alert">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="dashboard-form-row" style="gap:16px">
                    <div class="dashboard-form-col">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label" for="data_inicio">Data de início <small class="form-label-hint">(opcional)</small></label>
                            <input class="dashboard-form-input" type="date" id="data_inicio" name="data_inicio" value="{{ old('data_inicio', $destaque?->data_inicio?->format('Y-m-d')) }}" aria-describedby="erro-data-inicio">
                            @error('data_inicio')<p class="form-error" id="erro-data-inicio" role="alert">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="dashboard-form-col">
                        <div class="dashboard-form-group">
                            <label class="dashboard-form-label" for="data_fim">Data de fim <small class="form-label-hint">(opcional)</small></label>
                            <input class="dashboard-form-input" type="date" id="data_fim" name="data_fim" value="{{ old('data_fim', $destaque?->data_fim?->format('Y-m-d')) }}" aria-describedby="erro-data-fim">
                            @error('data_fim')<p class="form-error" id="erro-data-fim" role="alert">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="dashboard-form-group">
                    <label class="dashboard-checkbox"><input type="checkbox" name="ativo" id="ativo" value="1" @checked((bool) old('ativo', $destaque?->ativo ?? true))> Ativo — mostrar no site assim que o período começar.</label>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== Coluna lateral: Ações + pré-visualização =============== --}}
    <div class="editor-col">
        <div class="card editor-actions">
            <div class="card-head">
                <h2 class="card-title">Ações</h2>
            </div>
            <div class="editor-actions-body">
                <button type="submit" class="btn-primary"><i class="fa fa-save" aria-hidden="true"></i> Guardar destaque</button>
                <a href="{{ route('admin.destaques') }}" class="btn-secondary"><i class="fa fa-times" aria-hidden="true"></i> Cancelar</a>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2 class="card-title">Pré-visualização</h2></div>
            <div class="card-body">
                <div class="preview-banner" aria-hidden="true">
                    <div class="preview-banner-box" style="aspect-ratio: 2 / 1">
                        <span class="preview-banner-tag">Publicidade</span>
                        <img class="preview-banner-img{{ $imagemSrc ? '' : ' is-hidden' }}" id="banner-preview-img" alt="" src="{{ $imagemSrc }}">
                        <span class="preview-banner-empty{{ $imagemSrc ? ' is-hidden' : '' }}" id="banner-preview-empty">Escolhe uma imagem e vê aqui o resultado.</span>
                    </div>
                    <div class="preview-banner-meta">
                        <strong id="banner-preview-pos">{{ $escopoLabel }}</strong>
                    </div>
                </div>
                @if ($destaque?->link_destino)
                    <p class="form-hint" style="margin-top:10px"><i class="fa fa-link" aria-hidden="true"></i> Atual: <a href="{{ $destaque->link_destino }}" target="_blank" rel="noopener">Abrir destino</a></p>
                @endif
            </div>
        </div>
    </div>
</form>
@endsection