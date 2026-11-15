@extends('layouts.dashboard')

@section('title', $membro === null ? 'Novo membro — Equipa' : 'Editar membro — Equipa')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.equipa') }}">Equipa</a></li><li class="active" aria-current="page">{{ $membro === null ? 'Novo membro' : 'Editar membro' }}</li></ol>
@endsection

@section('content')

@php
    $ativoCheck = (bool) old('ativo', $membro?->ativo ?? true);
@endphp

<form class="editor-grid" action="{{ $membro === null ? route('admin.equipa.novo.store') : route('admin.equipa.atualizar', ['id' => $membro->id]) }}" method="post" id="equipa-form" data-loading-submit>
    @csrf
    @if ($membro !== null) @method('PUT') @endif

    <div class="editor-col">
        <div class="card">
            <div class="card-head">
                <h2 class="card-title">{{ $membro === null ? 'Novo membro' : 'Editar membro' }}</h2>
                <span class="status-badge {{ $ativoCheck ? 'active' : 'neutral' }}">{{ $ativoCheck ? 'Ativo' : 'Inativo' }}</span>
            </div>
            <div class="card-body">
                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="nome">Nome *</label>
                    <input class="dashboard-form-input" type="text" id="nome" name="nome" placeholder="Ex.: Ana Maria Coutinho" value="{{ old('nome', $membro?->nome) }}" required autofocus aria-describedby="erro-nome">
                    @error('nome')<p class="form-error" id="erro-nome" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="cargo_select">Cargo *</label>
                    <select class="dashboard-form-select" id="cargo_select" name="cargo_select" required aria-describedby="erro-cargo_select ajuda-cargo">
                        @foreach (\App\Support\Institucional::SECCOES_EQUIPA as $valor => $rotulo)
                        <option value="{{ $valor }}" @selected((string) old('cargo_select', $membro?->cargo_select ?? 'redacao') === (string) $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                    <small class="help-text" id="ajuda-cargo">Hierarquia da página: os membros ficam agrupados por esta seçção em /equipa. Para um cargo fora da lista, escolha «Outros» e escreva-o abaixo.</small>
                    @error('cargo_select')<p class="form-error" id="erro-cargo_select" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="cargo_outro">Defina o cargo <small class="form-label-hint">(obrigatório quando o select acima é «Outros»)</small></label>
                    <input class="dashboard-form-input" type="text" id="cargo_outro" name="cargo_outro" placeholder="Ex.: Especialista em SIG" value="{{ old('cargo_outro', $membro?->cargo_outro) }}" aria-describedby="erro-cargo_outro">
                    <small class="help-text">Usado apenas quando o Cargo é «Outros». Caso contrário, o cargo mostrado é a própria seçção.</small>
                    @error('cargo_outro')<p class="form-error" id="erro-cargo_outro" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-group">
                    <label class="dashboard-form-label" for="foto_url">Foto de perfil <small class="form-label-hint">(URL — não carrega ficheiros)</small></label>
                    <input class="dashboard-form-input" type="url" id="foto_url" name="foto_url" placeholder="https://…" value="{{ old('foto_url', $membro?->foto_url) }}" aria-describedby="erro-foto_url">
                    <small class="help-text">Recomendado: retrato quadrado (~400×400 px). Se vazio, mostram-se as iniciais no cartão.</small>
                    @error('foto_url')<p class="form-error" id="erro-foto_url" role="alert">{{ $message }}</p>@enderror
                </div>

                <div class="dashboard-form-row" style="gap:16px">
                    <div class="dashboard-form-col">
                        <label class="dashboard-form-label" for="facebook">Facebook</label>
                        <input class="dashboard-form-input" type="url" id="facebook" name="facebook" placeholder="https://facebook.com/…" value="{{ old('facebook', $membro?->facebook) }}" aria-describedby="erro-facebook">
                        @error('facebook')<p class="form-error" id="erro-facebook" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-col">
                        <label class="dashboard-form-label" for="instagram">Instagram</label>
                        <input class="dashboard-form-input" type="url" id="instagram" name="instagram" placeholder="https://instagram.com/…" value="{{ old('instagram', $membro?->instagram) }}" aria-describedby="erro-instagram">
                        @error('instagram')<p class="form-error" id="erro-instagram" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-col">
                        <label class="dashboard-form-label" for="linkedin">LinkedIn</label>
                        <input class="dashboard-form-input" type="url" id="linkedin" name="linkedin" placeholder="https://linkedin.com/in/…" value="{{ old('linkedin', $membro?->linkedin) }}" aria-describedby="erro-linkedin">
                        @error('linkedin')<p class="form-error" id="erro-linkedin" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="dashboard-form-row" style="gap:16px">
                    <div class="dashboard-form-col">
                        <label class="dashboard-form-label" for="ordem">Ordem <small class="form-label-hint">(menor aparece primeiro na seçção)</small></label>
                        <input class="dashboard-form-input" type="number" id="ordem" name="ordem" min="0" max="10000" value="{{ old('ordem', (string) ($membro?->ordem ?? 0)) }}" aria-describedby="erro-ordem">
                        @error('ordem')<p class="form-error" id="erro-ordem" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-col">
                        <label class="dashboard-checkbox" style="margin-top:32px"><input type="checkbox" name="ativo" value="1" @checked($ativoCheck)> Ativo — mostrar em /equipa</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="editor-col">
        <div class="card editor-actions">
            <div class="card-head"><h2 class="card-title">Ações</h2></div>
            <div class="editor-actions-body">
                <button type="submit" class="btn-primary"><i class="fa fa-save" aria-hidden="true"></i> Guardar membro</button>
                <a href="{{ route('admin.equipa') }}" class="btn-secondary"><i class="fa fa-times" aria-hidden="true"></i> Cancelar</a>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><h2 class="card-title">Cartão público</h2></div>
            <div class="card-body">
                <p class="help-text">Como aparecerá em <a href="{{ route('equipa') }}" target="_blank" rel="noopener">pigea.ao/equipa</a>:</p>
                <div class="help-text" style="margin-top:10px;padding:14px;border:1px solid #E0E0E0;border-radius:8px;text-align:center">
                    <strong>{{ old('nome', $membro?->nome) ?? 'Nome do membro' }}</strong><br>
                    {{ \App\Support\Institucional::cargoVisivel($membro ?? (object) ['cargo_select' => old('cargo_select', 'redacao'), 'cargo_outro' => old('cargo_outro')]) }}
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
