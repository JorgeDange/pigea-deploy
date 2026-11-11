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
@if (session('estado'))
    <p class="status-badge active" role="status">{{ session('estado') }}</p>
@endif

<form method="POST" action="{{ $noticia ? route('admin.noticia-editor.update', $noticia) : route('admin.noticia-editor.store') }}" enctype="multipart/form-data" id="news-editor-form">
    @csrf
    @if ($noticia)
        @method('PUT')
    @endif

    <div class="card">
        <div class="card-head">
            <h2 class="card-title">{{ $noticia ? 'Editar' : 'Nova' }} notícia</h2>
        </div>
        <div class="card-body">
            <div class="editor-grid">
                {{-- Coluna da esquerda: corpo do editor --}}
                <div class="editor-col">
                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label" for="titulo">Título <span aria-hidden="true">*</span></label>
                        <input class="dashboard-form-input" id="titulo" name="titulo" type="text" maxlength="190" placeholder="Título da notícia" value="{{ old('titulo', $noticia?->title ?? '') }}" required aria-describedby="erro-titulo">
                        @error('titulo')<p class="form-error" id="erro-titulo" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label" for="subtitulo">Subtítulo</label>
                        <input class="dashboard-form-input" id="subtitulo" name="subtitulo" type="text" maxlength="500" placeholder="Linha fina / destaque curto (opcional)" value="{{ old('subtitulo', $noticia?->excerpt ?? '') }}" aria-describedby="erro-subtitulo">
                        @error('subtitulo')<p class="form-error" id="erro-subtitulo" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="dashboard-form-group">
                        <label class="dashboard-form-label" for="conteudo-editor">Corpo da notícia</label>
                        <textarea id="conteudo-editor" name="conteudo" class="dashboard-form-textarea" rows="16" aria-describedby="erro-conteudo">{{ old('conteudo', $noticia?->content ?? '') }}</textarea>
                        @error('conteudo')<p class="form-error" id="erro-conteudo" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Coluna lateral direita: ações + metadados --}}
                <div class="editor-col" role="group" aria-label="Ações e metadados">
                    {{-- Ações: guarda/publica por submissão tradicional; pré-visualização nunca grava --}}
                    <div class="card editor-actions">
                        <div class="card-head">
                            <h2 class="card-title">Ações</h2>
                        </div>
                        <div class="editor-actions-body">
                            <button type="button" class="btn-secondary js-modal-open" data-modal="news-preview-modal"><i class="fa fa-eye" aria-hidden="true"></i> Pré-visualizar</button>
                            <button type="submit" name="acao" value="rascunho" class="btn-secondary"><i class="fa fa-save" aria-hidden="true"></i> Guardar rascunho</button>
                            <button type="submit" name="acao" value="publicar" class="btn-success"><i class="fa fa-paper-plane" aria-hidden="true"></i> Publicar</button>
                        </div>
                    </div>

                    {{-- Categoria + media + links --}}
                    <div class="card">
                        <div class="card-body">
                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="categoria">Categoria</label>
                                <select class="dashboard-form-select" id="categoria" name="categoria" aria-describedby="erro-categoria">
                                    <option value="">Selecionar categoria…</option>
                                    @foreach ($categorias as $opcao)
                                        <option value="{{ $opcao->id }}" {{ old('categoria', $noticia?->category_id) == $opcao->id ? 'selected' : '' }}>{{ $opcao->name }}</option>
                                    @endforeach
                                </select>
                                @error('categoria')<p class="form-error" id="erro-categoria" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="capa">Imagem de capa</label>
                                <div class="upload-box">
                                    @if ($noticia?->image)
                                        <img src="{{ asset($noticia->image) }}" alt="Capa atual da notícia (imagem em uso)" class="capa-atual">
                                        <br>
                                    @endif
                                    <input type="file" id="capa" name="capa" accept="image/png,image/jpeg,image/webp" aria-describedby="erro-capa capa-hint">
                                </div>
                                <small class="help-text" id="capa-hint">Recomendado: 1200x675px (16:9). Formatos: JPG, PNG, WEBP. Máx: 2MB.</small>
                                @error('capa')<p class="form-error" id="erro-capa" role="alert">{{ $message }}</p>@enderror
                            </div>

                            <div class="dashboard-form-group">
                                <label class="dashboard-form-label" for="contexto">Imagens de contexto</label>
                                <div class="upload-box">
                                    <input type="file" id="contexto" name="contexto[]" accept="image/png,image/jpeg,image/webp" multiple aria-describedby="erro-contexto erro-contexto-item contexto-hint">
                                </div>
                                <small class="help-text" id="contexto-hint">Recomendado: 800px de largura. Formatos: JPG, PNG, WEBP. Máx: 4MB por imagem.</small>
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
            <div class="news-preview-content" id="preview-content"><p><em>Corpo ainda vazio — escreve algo no editor.</em></p></div>
        </div>
        <div class="dashboard-modal-footer">
            <button type="button" class="btn-ghost js-modal-close">Fechar pré-visualização</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
<script>
    // Editor rico TinyMCE. Servido de public/js/tinymce (self-host).
    // Nota CSP: a app corre em Content-Security-Policy-Report-Only, logo
    // nada é bloqueado em runtime; o self-host mantém o script no mesmo
    // origin caso a política seja endurecida para enforce.
    // Nota: as regras de content_style duplicam de propósito as de
    // `.news-preview-content` em dashboard.css — o TinyMCE injeta um iframe
    // que não herda o CSS da página. Manter os dois sincronizados.
    tinymce.init({
        selector: '#conteudo-editor',
        height: 430,
        min_height: 320,
        menubar: false,
        branding: false,
        license_key: 'gpl',
        language: 'pt_PT',
        language_url: '/js/tinymce/langs/pt_PT.js',
        // Nome acessível do iframe do editor (o <label> não atravessa o iframe)
        iframe_aria_text: 'Corpo da notícia',
        plugins: ['link', 'lists', 'image', 'autoresize', 'code'],
        toolbar: 'undo redo | bold italic underline | bullist numlist | link image | dropcap blockquote twocols',
        formats: {
            dropcap: { selector: 'p', classes: 'has-drop-cap' },
            twocols: { selector: 'p', classes: 'texto-duas-colunas' },
            blockquote: { block: 'blockquote' }
        },
        content_style: 'body { font-family: Poppins, sans-serif; font-size: 15px; color: #1A1A1A; } h1,h2,h3 { font-family: "Playfair Display", serif; } .has-drop-cap::first-letter { float: left; font-size: 2.4em; line-height: .85; padding: 4px 10px 0 0; font-family: "Playfair Display", serif; color: #C8102E; } blockquote { border-left: 4px solid #C8102E; margin: 1em 0; padding: .4em 1.2em; background: #FDF3F5; font-style: italic; color: #444; } .texto-duas-colunas { column-count: 2; column-gap: 2.2em; } img { max-width: 100%; height: auto; }',
        setup: function (editor) {
            editor.ui.registry.addButton('dropcap', {
                text: 'Letra capitular',
                tooltip: 'Letra capitular no início do parágrafo',
                onAction: function () { editor.formatter.toggle('dropcap'); }
            });
            editor.ui.registry.addButton('blockquote', {
                icon: 'blockquote',
                tooltip: 'Citação destacada',
                onAction: function () { editor.formatter.toggle('blockquote'); }
            });
            editor.ui.registry.addButton('twocols', {
                text: 'Duas Colunas',
                tooltip: 'Texto em duas colunas',
                onAction: function () { editor.formatter.toggle('twocols'); }
            });
        }
    });

    // A validação devolve o utilizador a esta página com old()+$errors preenchidos;
    // coloca o foco no primeiro campo com erro (sem autofocus fixo no título).
    (function () {
        var primeiro = document.querySelector('.form-error');
        if (!primeiro) return;
        var grupo = primeiro.closest('.dashboard-form-group');
        var campo = grupo ? grupo.querySelector('input, select, textarea') : null;
        // O TinyMCE esconde o textarea (#conteudo-editor) — .focus() num
        // elemento oculto é um no-op; o foco real é o editor rich text.
        if (campo && campo.id === 'conteudo-editor' && window.tinymce) {
            var ed = tinymce.get('conteudo-editor');
            if (ed) { ed.focus(); return; }
            var area = document.querySelector('.tox .tox-edit-area');
            if (area) { area.setAttribute('tabindex', '-1'); area.focus(); return; }
        }
        if (campo) campo.focus();
    })();

    // Impede duplo clique em "Guardar rascunho" / "Publicar" (duplo POST → notícia duplicada).
    (function () {
        var form = document.getElementById('news-editor-form');
        form.addEventListener('submit', function () {
            form.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = true; });
        });
    })();

    // Pré-visualização: aqui só se preenchem os dados, faz-se trap de Tab e
    // gere-se o object URL. Abrir/fechar, Escape, backdrop e devolução de foco
    // ao botão de origem são da responsabilidade do dashboard.js (js-modal-open/close).
    (function () {
        var modal = document.getElementById('news-preview-modal');
        var capa = document.getElementById('capa');
        var previewCapa = document.getElementById('preview-capa');
        var previewTitulo = document.getElementById('preview-titulo');
        var previewSubtitulo = document.getElementById('preview-subtitulo');
        var previewContent = document.getElementById('preview-content');
        var previewCategoria = document.getElementById('preview-categoria');
        var ultimoPreviewUrl = null;

        function preencherPreview() {
            // O HTML injectado é o que o TinyMCE emite para um autor do painel
            // (conteúdo confiado) — é o mesmo HTML que a página pública da
            // notícia renderiza; sanitizar só aqui criaria dois comportamentos
            // divergentes para o mesmo corpo de texto.
            var ed = window.tinymce && tinymce.get('conteudo-editor');
            var conteudo = ed && ed.getContent ? ed.getContent() : document.getElementById('conteudo-editor').value;

            previewTitulo.textContent = document.getElementById('titulo').value || '(sem título)';
            var sub = document.getElementById('subtitulo').value.trim();
            previewSubtitulo.textContent = sub;
            previewSubtitulo.hidden = sub === '';

            var selectCat = document.getElementById('categoria');
            previewCategoria.textContent = selectCat.selectedIndex > 0 ? selectCat.options[selectCat.selectedIndex].text : 'Geral';

            previewContent.innerHTML = conteudo || '<p><em>Corpo ainda vazio — escreve algo no editor.</em></p>';

            if (ultimoPreviewUrl) {
                URL.revokeObjectURL(ultimoPreviewUrl);
                ultimoPreviewUrl = null;
            }
            if (capa && capa.files && capa.files[0]) {
                ultimoPreviewUrl = URL.createObjectURL(capa.files[0]);
                previewCapa.src = ultimoPreviewUrl;
                previewCapa.alt = 'Capa da notícia (pré-visualização)';
                previewCapa.hidden = false;
            } else {
                previewCapa.removeAttribute('src');
                previewCapa.hidden = true;
            }
        }

        // Preenche no mesmo clique que abre (delegação; o dashboard.js trata do .open).
        document.addEventListener('click', function (e) {
            if (e.target.closest('[data-modal="news-preview-modal"]')) preencherPreview();
        });

        // Revoga o object URL assim que o modal fecha — por qualquer caminho
        // (× do cabeçalho, rodapé, Escape ou clique no backdrop).
        new MutationObserver(function () {
            if (!modal.classList.contains('open') && ultimoPreviewUrl) {
                URL.revokeObjectURL(ultimoPreviewUrl);
                ultimoPreviewUrl = null;
            }
        }).observe(modal, { attributes: true, attributeFilter: ['class'] });

        // Trap de Tab/Shift+Tab dentro do diálogo (WCAG 2.4.3).
        // O dashboard.js foca o próprio contentor (tabindex=-1); sem cobrir
        // esse caso, Shift+Tab a partir dele saía para a página de fundo.
        modal.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab') return;
            var focaveis = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (!focaveis.length) return;
            var primeiro = focaveis[0];
            var ultimo = focaveis[focaveis.length - 1];
            var ativo = document.activeElement;
            var noContainer = ativo === modal || !modal.contains(ativo);
            if (e.shiftKey && (ativo === primeiro || noContainer)) { ultimo.focus(); e.preventDefault(); }
            else if (!e.shiftKey && (ativo === ultimo || noContainer)) { primeiro.focus(); e.preventDefault(); }
        });
    })();
</script>
@endpush
