@extends('layouts.dashboard')

@section('title', ($noticia ?? null) ? 'Editar notícia' : 'Nova notícia')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li><a href="{{ route('admin.noticias') }}">Notícias</a></li><li class="active" aria-current="page">{{ ($noticia ?? null) ? 'Editar' : 'Nova' }} notícia</li></ol>
@endsection

@section('content')
<h1 class="sr-only">{{ ($noticia ?? null) ? 'Editar notícia' : 'Nova notícia' }}</h1>
@php
    $ehAdmin = auth()->user()?->hasRole('admin') ?? false;
    $podePublicar = $ehAdmin || (bool) config('cms.editor_pode_publicar');
    $statusAtual = $noticia?->status ?? 'draft';
    $estadoLabel = ['draft' => 'Rascunho', 'em_revisao' => 'Em revisão', 'published' => 'Publicado'][$statusAtual] ?? $statusAtual;
    $estadoClasse = ['draft' => 'neutral', 'em_revisao' => 'pending', 'published' => 'active'][$statusAtual] ?? 'neutral';
@endphp
@if (session('erro'))
    <div class="flash-toast" data-flash-toast="error" hidden>{{ session('erro') }}</div>
@endif
@if (session('estado'))
    {{-- Fase 5 (cms_v.md): flash simples → toast global (dashboard.js) --}}
    <div class="flash-toast" data-flash-toast="success" hidden>{{ session('estado') }}</div>
@endif

<form method="POST" action="{{ $noticia ? route('admin.noticia-editor.update', $noticia) : route('admin.noticia-editor.store') }}" enctype="multipart/form-data" id="news-editor-form" data-preview-url="{{ route('admin.noticia-pre-visualizar') }}" data-image-url="{{ route('admin.noticia-imagem.store') }}" data-autosave-url="{{ route('admin.noticia-autosave') }}" data-update-url="{{ url(config('app.admin_path') . '/noticias/ID') }}" data-id="{{ $noticia?->id ?? '' }}" data-loading-submit>
    @csrf
    @if ($noticia)
        @method('PUT')
    @endif

    <div class="card">
        <div class="card-head">
            <h2 class="card-title">{{ $noticia ? 'Editar' : 'Nova' }} notícia</h2>
            <span class="status-badge {{ $estadoClasse }}">{{ $estadoLabel }}</span>
        </div>
        <div class="card-body">
            <div class="editor-grid">
                {{-- Coluna da esquerda: corpo do editor --}}
                <div class="editor-col">
                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label" for="titulo">Título <span aria-hidden="true">*</span></label>
                        <input class="dashboard-form-input" id="titulo" name="titulo" type="text" maxlength="190" placeholder="Título da notícia" value="{{ old('titulo', $noticia?->title ?? '') }}" required aria-describedby="erro-titulo contador-titulo">
                        <div class="form-counter"><span id="contador-titulo">0</span>/190</div>
                        @error('titulo')<p class="form-error" id="erro-titulo" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label" for="subtitulo">Resumo <small class="form-label-hint">(linha de destaque usada na lista e no SEO)</small></label>
                        <input class="dashboard-form-input" id="subtitulo" name="subtitulo" type="text" maxlength="500" placeholder="Resumo curto (opcional)" value="{{ old('subtitulo', $noticia?->excerpt ?? '') }}" aria-describedby="erro-subtitulo contador-subtitulo">
                        <div class="form-counter"><span id="contador-subtitulo">0</span>/500</div>
                        @error('subtitulo')<p class="form-error" id="erro-subtitulo" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label" for="conteudo-editor">Corpo da notícia</label>
                        <textarea id="conteudo-editor" name="conteudo" class="dashboard-form-textarea" rows="16" aria-describedby="erro-conteudo">{{ old('conteudo', $noticia?->content ?? '') }}</textarea>
                        @error('conteudo')<p class="form-error" id="erro-conteudo" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <details class="editor-more">
                        <summary>Mais opções</summary>
                        <div class="editor-more-body">
                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="slug">Endereço <small class="form-label-hint">(/noticias/…)</small></label>
                                <input class="dashboard-form-input slug-input" id="slug" name="slug" type="text" maxlength="190" value="{{ old('slug', $noticia?->slug ?? '') }}" placeholder="titulo-da-noticia" autocapitalize="off" spellcheck="false" data-slug-url="{{ route('admin.noticia-slug-unico') }}" data-ignorar="{{ $noticia?->id ?? '' }}" aria-describedby="erro-slug slug-state">
                                <span class="slug-state" id="slug-state" role="status"></span>
                                @error('slug')<p class="form-error" id="erro-slug" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="tags">Etiquetas</label>
                                <input class="dashboard-form-input" id="tags" name="tags" type="text" maxlength="250" placeholder="Ex.: mineração, Angola, diamantes" value="{{ old('tags', $noticia?->tags ?? '') }}">
                                @error('tags')<p class="form-error" id="erro-tags" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="publicar_em">Publicar em <small class="form-label-hint">(agendar; vazio = agora)</small></label>
                                <input class="dashboard-form-input" id="publicar_em" name="publicar_em" type="datetime-local" value="{{ old('publicar_em', $noticia?->published_at?->format('Y-m-d\TH:i')) }}" aria-describedby="erro-publicar_em publicar_em-hint">
                                <small class="help-text" id="publicar_em-hint">Só entra em vigor quando primires «Publicar». O artigo fica visível no site a partir desta data/hora.</small>
                                @error('publicar_em')<p class="form-error" id="erro-publicar_em" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <details class="editor-more-seo">
                                <summary>Título e descrição nas pesquisas (Google)</summary>
                                <div class="dashboard-form-group">
                                    <label class="dashboard-form-label" for="meta_title">Título da página <small class="form-label-hint">(ideal 60 caracteres)</small></label>
                                    <input class="dashboard-form-input" id="meta_title" name="meta_title" type="text" maxlength="190" value="{{ old('meta_title', $noticia?->meta_title ?? '') }}" aria-describedby="erro-meta_title contador-meta-title">
                                    <div class="form-counter"><span id="contador-meta-title">0</span>/60</div>
                                    @error('meta_title')<p class="form-error" id="erro-meta_title" role="alert">{{ $message }}</p>@enderror
                                </div>
                                <div class="dashboard-form-group">
                                    <label class="dashboard-form-label" for="meta_description">Descrição da página <small class="form-label-hint">(ideal 160 caracteres)</small></label>
                                    <textarea class="dashboard-form-textarea" id="meta_description" name="meta_description" rows="2" maxlength="500" aria-describedby="erro-meta_description contador-meta-description">{{ old('meta_description', $noticia?->meta_description ?? '') }}</textarea>
                                    <div class="form-counter"><span id="contador-meta-description">0</span>/160</div>
                                    @error('meta_description')<p class="form-error" id="erro-meta_description" role="alert">{{ $message }}</p>@enderror
                                </div>
                            </details>
                        </div>
                    </details>
                </div>

                {{-- Coluna lateral direita: ações + metadados --}}
                <div class="editor-col" role="group" aria-label="Ações e metadados">
                    {{-- Ações: guarda/publica por submissão tradicional; pré-visualização nunca grava --}}
                    <div class="card editor-actions">
                        <div class="card-head">
                            <h2 class="card-title">Ações</h2>
                        </div>
                        <div class="editor-actions-body">
                            <button type="button" class="btn-ghost js-modal-open" data-modal="news-preview-modal"><i class="fa fa-eye" aria-hidden="true"></i> Pré-visualizar</button>
                            <button type="submit" name="acao" value="rascunho" class="btn-secondary"><i class="fa fa-save" aria-hidden="true"></i> Guardar rascunho</button>
                            @if ($podePublicar)
                                <button type="submit" name="acao" value="publicar" class="btn-primary"><i class="fa fa-paper-plane" aria-hidden="true"></i> Publicar</button>
                            @else
                                <button type="submit" name="acao" value="submeter_revisao" class="btn-primary"><i class="fa fa-paper-plane" aria-hidden="true"></i> Submeter para revisão</button>
                            @endif
                            @if ($ehAdmin && $noticia?->status === 'published')
                                <button type="submit" name="acao" value="devolver_rascunho" class="btn-secondary" formnovalidate><i class="fa fa-undo" aria-hidden="true"></i> Devolver a rascunho</button>
                                <button type="submit" name="acao" value="despublicar" class="btn-danger" formnovalidate><i class="fa fa-eye-slash" aria-hidden="true"></i> Despublicar</button>
                            @endif
                        </div>
                        <p class="autosave" id="autosave-indicador" role="status"><i class="fa fa-cloud-check" aria-hidden="true"></i> Autosave grava sozinho ao parares de escrever.</p>
                    </div>

                    {{-- Categoria + media + links --}}
                    <div class="card">
                        <div class="card-body">
                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="categoria">Categoria</label>
                                <select class="dashboard-form-select" id="categoria" name="categoria" aria-describedby="erro-categoria">
                                    <option value="">Selecionar categoria…</option>
                                    @foreach ($tops as $grupo)
                                        @php $temFilhos = isset($filhos[$grupo->id]) && $filhos[$grupo->id]->count() > 0; @endphp
                                        @if ($temFilhos)
                                            <optgroup label="{{ $grupo->name }}">
                                                <option value="{{ $grupo->id }}" {{ old('categoria', $noticia?->category_id ?? $categoriasSelecionada) == $grupo->id ? 'selected' : '' }}>{{ $grupo->name }} (geral)</option>
                                                @foreach ($filhos[$grupo->id] as $filho)
                                                    <option value="{{ $filho->id }}" {{ old('categoria', $noticia?->category_id ?? $categoriasSelecionada) == $filho->id ? 'selected' : '' }}>&nbsp;&nbsp;{{ $filho->name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @else
                                            <option value="{{ $grupo->id }}" {{ old('categoria', $noticia?->category_id ?? $categoriasSelecionada) == $grupo->id ? 'selected' : '' }}>{{ $grupo->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('categoria')<p class="form-error" id="erro-categoria" role="alert">{{ $message }}</p>@enderror
                            </div>

                            @php
                                $fonteAtual = old('capa_fonte', ($noticia && $noticia->image && str_starts_with($noticia->image, 'http')) ? 'link' : 'ficheiro');
                            @endphp
                            <div class="dashboard-form-group">
                                <span class="dashboard-form-label">Imagem de capa</span>
                                <div class="cover-source">
                                    <label class="dashboard-checkbox"><input type="radio" name="capa_fonte" value="ficheiro" {{ $fonteAtual === 'ficheiro' ? 'checked' : '' }}> Ficheiro (upload)</label>
                                    <label class="dashboard-checkbox"><input type="radio" name="capa_fonte" value="link" {{ $fonteAtual === 'link' ? 'checked' : '' }}> Link (URL)</label>
                                </div>

                                {{-- Painel: upload do ficheiro --}}
                                <div id="capa-ficheiro" class="file-upload" data-limit="capa"{{ $fonteAtual === 'link' ? ' hidden' : '' }}>
                                    @if ($noticia?->image)
                                        <img class="file-upload-preview" src="{{ image_url($noticia->image) }}" alt="Capa atual da notícia (imagem em uso)">
                                    @endif
                                    <label class="file-upload-button" for="capa"><i class="fa fa-folder-open" aria-hidden="true"></i> Escolher ficheiro</label>
                                    <div class="file-upload-meta">
                                        <span class="file-upload-name">{{ $noticia?->image ? 'Imagem atual: ' . basename($noticia->image) : 'Nenhum ficheiro selecionado' }}</span>
                                        <small class="help-text" id="capa-hint">Recomendado: 1200x675px (16:9). JPG, PNG, WebP. Máx: 2MB.</small>
                                    </div>
                                    <input type="file" id="capa" name="capa" accept="image/png,image/jpeg,image/webp" aria-describedby="erro-capa capa-hint"{{ $fonteAtual === 'link' ? ' disabled' : '' }}>
                                    <button type="button" class="file-upload-remove">Remover</button>
                                    <p class="file-upload-erro" role="alert"></p>
                                </div>

                                {{-- Painel: imagem por link (sem ficheiro) --}}
                                <div id="capa-link" class="dashboard-form-group"{{ $fonteAtual === 'link' ? '' : ' hidden' }}>
                                    <label class="dashboard-form-label" for="capa_link">Link da imagem</label>
                                    <input class="dashboard-form-input" id="capa_link" name="capa_link" type="url" placeholder="https://…/imagem.jpg" value="{{ old('capa_link', ($noticia && $noticia->image && str_starts_with($noticia->image, 'http')) ? $noticia->image : '') }}" aria-describedby="erro-capa_link"{{ $fonteAtual === 'link' ? '' : ' disabled' }}>
                                    <small class="help-text" id="capa_link-hint">Cola o URL direto da imagem. A imagem não é descarregada; o link é mostrado no site. Se usares este campo, o campo «Ficheiro» é ignorado (e vice-versa).</small>
                                    @error('capa_link')<p class="form-error" id="erro-capa_link" role="alert">{{ $message }}</p>@enderror
                                </div>

                                <div class="dashboard-form-group">
                                    <label class="dashboard-form-label" for="image_alt">Texto alternativo da capa</label>
                                    <input class="dashboard-form-input" id="image_alt" name="image_alt" type="text" maxlength="250" placeholder="Descreve a imagem para leitores de écran" value="{{ old('image_alt', $noticia?->image_alt ?? '') }}" aria-describedby="erro-image_alt">
                                    @error('image_alt')<p class="form-error" id="erro-image_alt" role="alert">{{ $message }}</p>@enderror
                                </div>
                                <div class="dashboard-form-group">
                                    <label class="dashboard-form-label" for="image_caption">Crédito da imagem</label>
                                    <input class="dashboard-form-input" id="image_caption" name="image_caption" type="text" maxlength="250" placeholder="Ex.: Foto: Ricardo Mendes (Lusa)" value="{{ old('image_caption', $noticia?->image_caption ?? '') }}" aria-describedby="erro-image_caption">
                                    @error('image_caption')<p class="form-error" id="erro-image_caption" role="alert">{{ $message }}</p>@enderror
                                </div>
                                @error('capa')<p class="form-error" id="erro-capa" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="contexto">Imagens de contexto</label>
                                <div class="file-upload" data-limit="contexto">
                                    <label class="file-upload-button" for="contexto"><i class="fa fa-folder-open" aria-hidden="true"></i> Escolher ficheiro(s)</label>
                                    <div class="file-upload-meta">
                                        <span class="file-upload-name">Nenhum ficheiro selecionado</span>
                                        <small class="help-text" id="contexto-hint">Recomendado: 800px de largura. JPG, PNG, WebP. Máx: 4MB por imagem (até 10).</small>
                                    </div>
                                    <input type="file" id="contexto" name="contexto[]" accept="image/png,image/jpeg,image/webp" multiple aria-describedby="erro-contexto erro-contexto-item contexto-hint">
                                    <button type="button" class="file-upload-remove">Remover</button>
                                    <p class="file-upload-erro" role="alert"></p>
                                </div>
                                @error('contexto')<p class="form-error" id="erro-contexto" role="alert">{{ $message }}</p>@enderror
                                @error('contexto.*')<p class="form-error" id="erro-contexto-item" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="videoLink">Vídeo (YouTube)</label>
                                <input class="dashboard-form-input" id="videoLink" name="videoLink" type="url" placeholder="https://youtube.com/watch?v=…" value="{{ old('videoLink', $noticia?->video_link ?? '') }}" aria-describedby="erro-videoLink">
                                @error('videoLink')<p class="form-error" id="erro-videoLink" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="documentoLink">Documento (PDF)</label>
                                <input class="dashboard-form-input" id="documentoLink" name="documentoLink" type="url" placeholder="https://…" value="{{ old('documentoLink', $noticia?->documento_link ?? '') }}" aria-describedby="erro-documentoLink">
                                @error('documentoLink')<p class="form-error" id="erro-documentoLink" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="link">Link (URL) <small class="form-label-hint">externo</small></label>
                                <input class="dashboard-form-input" id="link" name="link" type="url" placeholder="https://…" value="{{ old('link', $noticia?->link ?? '') }}" aria-describedby="erro-link">
                                @error('link')<p class="form-error" id="erro-link" role="alert">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Pré-visualização: convenção de modais do painel (`.dashboard-modal` + `.open`,
     dashboard.js trata de abrir/fechar, Escape, backdrop e devolução de foco).
     Abre sem gravar, fecha pelo ×, pelo rodapé, Escape ou clique no backdrop. --}}
<div class="dashboard-modal" id="news-preview-modal" role="dialog" aria-modal="true" aria-labelledby="pv-titulo">
    <div class="dashboard-modal-content news-preview-modal-content">
        <div class="dashboard-modal-header">
            <h3 id="pv-titulo">Pré-visualização</h3>
            <button type="button" class="dashboard-modal-close js-modal-close" aria-label="Fechar pré-visualização"><i class="fa fa-times" aria-hidden="true"></i></button>
        </div>
        <div class="dashboard-modal-body news-preview">
            <img class="news-preview-capa" id="preview-capa" alt="Imagem de capa da notícia" hidden>
            <div class="news-preview-meta">
                <span class="post-category" id="preview-categoria">Geral</span>
                <span class="post-date"><i class="far fa-clock" aria-hidden="true"></i> {{ $noticia?->date_label ?? now()->locale('pt')->translatedFormat('d M Y') }}</span>
            </div>
            <p class="news-preview-title" role="heading" aria-level="3" id="preview-titulo">(sem título)</p>
            <p class="news-preview-lead" id="preview-subtitulo" hidden></p>
            <p class="form-error" id="preview-error" role="alert" hidden></p>
            {{-- Fase 1: o mesmo parcial do site público, obtido por fetch ao servidor --}}
            <div class="article-body" id="preview-article"></div>
        </div>
        <div class="dashboard-modal-footer">
            <button type="button" class="btn-ghost js-modal-close">Fechar pré-visualização</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('js/admin/news-editor.js') }}"></script>
@endpush

