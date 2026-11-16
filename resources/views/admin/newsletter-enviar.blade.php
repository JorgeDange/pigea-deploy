@extends('layouts.dashboard')

@section('title', 'Aviso de novo artigo — Newsletter')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.newsletter') }}">Newsletter</a></li><li class="active" aria-current="page">Aviso de artigo</li></ol>
@endsection

@section('content')
<h1 class="sr-only">Aviso de novo artigo</h1>

@if (session('newsletter_msg') && isset(session('newsletter_msg')['texto']))
    <p class="newsletter-msg newsletter-msg-{{ (session('newsletter_msg')['tipo'] ?? 'ok') === 'ok' ? 'ok' : 'erro' }}" role="status">{{ session('newsletter_msg')['texto'] }}</p>
@endif

<div class="card">
    <div class="card-head">
        <h2 class="card-title">Aviso de novo artigo — para os subscritores</h2>
        <div class="card-actions">
            <a href="{{ route('admin.newsletter') }}" class="btn-secondary btn-sm"><i class="fa fa-arrow-left" aria-hidden="true"></i> Voltar</a>
        </div>
    </div>
    <div class="card-body">
        <p class="help-text" style="margin-top:0;">Envia um email aos <strong>{{ $ativos }}</strong> subscritores <strong>activos</strong> (os que confirmaram a subscrição). Cada clique envia um máximo de <strong>100 emails</strong>, começando pelos que ainda não receberam nesta edição. Falhados são tentados de novo no clique seguinte.</p>

        @if ($artigos->isEmpty())
            <p class="help-text">Não há artigos publicados recentemente.</p>
        @else
        <form method="post" action="{{ route('admin.newsletter.enviar') }}" id="form-enviar-artigo">
            @csrf
            <fieldset class="artigo-lista" style="border:0; padding:0; margin:0 0 18px;">
                <legend class="dashboard-form-label">Escolha o artigo a publicitar</legend>
                <div style="display:grid; grid-template-columns:1fr; gap:10px; max-height:520px; overflow:auto;">
                    @foreach ($artigos as $artigo)
                        <label class="artigo-op" style="display:flex; gap:12px; align-items:flex-start; border:1px solid #E2E2E2; border-radius:8px; padding:10px 12px; cursor:pointer; background:#FCFCFC;">
                            <input type="radio" name="artigo_id" value="{{ $artigo->id }}" @if ($loop->first) checked @endif required style="margin-top:4px;">
                            <span style="display:flex; gap:12px; align-items:flex-start; min-width:0;">
                                <span style="display:block; width:92px; min-width:92px; height:62px; border-radius:6px; background:url('{{ image_url($artigo->image, 'images/logo.webp') }}') center/cover no-repeat #EDEDED; border:1px solid #E0E0E0;" role="img" aria-label="{{ $artigo->image_alt ?: $artigo->title }}"></span>
                                <span style="min-width:0;">
                                    <strong style="display:block; font-size:15px; line-height:1.35; color:#1A1A1A;">{{ $artigo->title }}</strong>
                                    <small style="color:#666; font-size:12px; display:block; margin-top:2px;">{{ $artigo->published_at?->format('d/m/Y') }}</small>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('artigo_id')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </fieldset>

            <div class="dashboard-form-actions" style="gap:10px; flex-wrap:wrap;">
                <button type="submit" name="teste" value="1" class="btn-secondary"><i class="fa fa-flask" aria-hidden="true"></i> Enviar teste (só para a tua caixa)</button>
                <button type="submit" class="btn-primary"><i class="fa fa-paper-plane" aria-hidden="true"></i> Enviar lote (até 100)</button>
            </div>
            <p class="help-text" style="margin-bottom:0;">Exemplo: com 250 subscritores ativos, clica 3 vezes — o 1.º envia 100, o 2.º 100, o 3.º 50.</p>
        </form>
        @endif
    </div>
</div>

@if ($edicoes->isNotEmpty())
<div class="card">
    <div class="card-head">
        <h2 class="card-title">Últimas edições enviadas</h2>
    </div>
    <div class="card-body">
        <div class="table-wrap">
            <table class="dashboard-table">
                <thead><tr><th scope="col">Artigo</th><th scope="col">Total</th><th scope="col">Enviados</th><th scope="col">Falhados</th><th scope="col">Estado</th><th scope="col">Criada por</th></tr></thead>
                <tbody>
                    @foreach ($edicoes as $edicao)
                        <tr>
                            <td><a href="{{ $edicao->article ? route('news.show', $edicao->article->slug) : '#' }}">{{ $edicao->article?->title ?? '(artigo removido)' }}</a></td>
                            <td>{{ $edicao->total }}</td>
                            <td>{{ $edicao->enviados }}</td>
                            <td>{{ $edicao->falhados }}</td>
                            <td><span class="status-badge {{ $edicao->estado === 'done' ? 'active' : 'pending' }}">{{ $edicao->estado === 'done' ? 'Concluída' : 'Em envio' }}</span></td>
                            <td>{{ $edicao->creator?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
