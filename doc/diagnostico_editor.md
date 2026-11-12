# Diagnóstico do Editor de Notícias — CMS PIGEA

**Data:** 11/11/2026 · **Modo:** SÓ LEITURA (nenhum ficheiro do projecto foi alterado/criado/apagado; o único ficheiro criado é este)
**Ficheiros citados:** caminhos **completos** indicados ao longo do documento
**Método:** leitura directa dos ficheiros (view, controllers, modelos, migrations, rotas, CSS) + `Select-String`/`node --check` quando indicado. **Nenhum teste executado em browser** (ver §11).

> **Snapshot ao qual este diagnóstico se refere:** 11/11/2026, após a **Fase 10.1** — o editor de notícias foi migrado de componente Livewire full-page para **Blade tradicional + POST clássico** (o morph do Livewire destruía o DOM do TinyMCE). Por isso: **o componente Livewire `NewsEditor` já não existe**; era `app/Livewire/Admin/NewsEditor.php`, apagado sem nunca ter sido commitado (`git log -- app/Livewire/Admin/NewsEditor.php` → vazio). O diagnóstico descreve o estado **actual**, não o de há dois dias.

Marcadores de confiança usados neste documento:

- **CONFIRMADO** — lido directamente no ficheiro.
- **NÃO EXISTE** — procurado e ausente (grep indicado).
- **NÃO CONFIRMADO** — exige browser/runtime ou informação não disponível.

---

## 1. Versão e instalação do TinyMCE

### 1.1 Versão (CONFIRMADO)

`C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\package.json`:

```json
"@tailwindcss/vite": "^4.0.0",
"tailwindcss": "^4.0.0",
"tinymce-langs": "^1.0.0",
"tinymce": "^7.9.3"
```

`C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\package-lock.json`:

```json
"node_modules/tinymce": {
    "version": "7.9.3",
    "resolved": "https://registry.npmjs.org/tinymce/-/tinymce-7.9.3.tgz",
    "integrity": "sha512-Mtm54U5YJ6Pyo/GaAx+JSHXTGEuxrg2AowVWCD9zy1eBolp5Ub7S1rTtsyQdxhPegfhLuR3VLiTKGw1tacv09g==",
    "license": "GPL-2.0-or-later"
```

`node_modules/tinymce/package.json` (instalado): **name=tinymce, version=7.9.3** (CONFIRMADO).

### 1.2 Como é carregado (CONFIRMADO)

O TinyMCE **não é servido pelo Vite/npm** — foi copiado manualmente para **`public/js/tinymce/`** (self-host estático, ~7,44 MB medidos; contém `icons/`, `langs/`, `models/`, `plugins/`, `skins/`, `themes/`, `tinymce.min.js`, `license.md`). O `package.json` que regista a dependência existe para o `node_modules`/lock, mas o que é servido em produção é o directório estático.

Carregamento na vista Blade (`@push('scripts')`, no fim da vista, após `@endsection` — ver §3):

```blade
<script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
```

Linguagem: `language: 'pt_PT'` + `language_url: '/js/tinymce/langs/pt_PT.js'` (ficheiro **existe**: `public/js/tinymce/langs/pt_PT.js`, CONFIRMADO).

`plugins.css` (vendor do protótipo) e os assets do painel são servidos do mesmo `public/`.

### 1.3 Licença (CONFIRMADO)

- TinyMCE 7.x sob **GPL-2.0-or-later** (registo no lock; atributo correcto para self-host gratuito).
- No `tinymce.init`: **`license_key: 'gpl'`** — escolha deliberada para uso GPL self-hosted (sem chave comercial).
- O ficheiro `license.md` está presente em `public/js/tinymce/license.md` (exigência de atribuição GPL — mantido).

---

## 2. Configuração do TinyMCE

### 2.1 Onde está (CONFIRMADO)

Ficheiro: `C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\resources\views\admin\noticia-editor.blade.php` — bloco `@push('scripts')`, **depois** de `@endsection` (secção `content`). O layout `layouts/dashboard.blade.php` rende a stack em **linha 43 `@stack('scripts')`** (CONFIRMADO por grep), depois de `@livewireScripts` (linha 42).

### 2.2 Bloco completo do `tinymce.init` (CONFIRMADO — copiado literal)

```
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
```

### 2.3 Valores pedidos pela análise (CONFIRMADO, linha por linha)

| Chave pedida | Valor real |
|---|---|
| `plugins` | `['link', 'lists', 'image', 'autoresize', 'code']` |
| `toolbar` | `'undo redo | bold italic underline | bullist numlist | link image | dropcap blockquote twocols'` |
| `menubar` | `false` |
| `content_css` | **NÃO DEFINIDO** |
| `content_style` | *string com regras*: Poppins 15px #1A1A1A; h1-h3 Playfair Display; `.has-drop-cap::first-letter` (float, 2.4em, Playfair, #C8102E); `blockquote` (border-left 4px #C8102E, #FDF3F5, itálico); `.texto-duas-colunas` (column-count:2, column-gap:2.2em); `img{max-width:100%}` — ver bloco §2.2 (literal) |
| `style_formats` | **NÃO DEFINIDO** |
| `formats` | `{ dropcap: { selector: 'p', classes: 'has-drop-cap' }, twocols: { selector: 'p', classes: 'texto-duas-colunas' }, blockquote: { block: 'blockquote' } }` |
| `valid_elements` | **NÃO DEFINIDO** |
| `extended_valid_elements` | **NÃO DEFINIDO** |
| `valid_classes` | **NÃO DEFINIDO** |
| `paste_*` | **NÃO DEFINIDO** (nenhuma chave `paste_*` — ver nota em §2.4) |
| `images_upload_handler` | **NÃO DEFINIDO** |
| `automatic_uploads` | **NÃO DEFINIDO** |
| `height` | `430` (existe também `min_height: 320`) |
| `language` | `'pt_PT'` |
| `branding` | `false` |
| `license_key` | `'gpl'` |
| `iframe_aria_text` | `'Corpo da notícia'` (bónus; nome acessível do iframe para WCAG) |
| `menubar`/`toolbar` ativos | apenas dropcap, blockquote, twocols como botões custom; resto é built-in do TinyMCE |

### 2.4 Notas importantes (CONFIRMADO)

- **NENHUM controlo de conteúdo HTML no cliente**: sem `valid_elements`, sem `valid_classes`, sem `paste_as_text`; o que o TinyMCE emite é o que vai para a BD. A sanitização na **saída** também não existe (ver §6).
- **`paste_*` por omissão do TinyMCE 7** no que respeita a colagem de imagens (data URI base64): **NÃO CONFIRMADO em browser** (comportamento por omissão do v7, dependente de build; a ausência de qualquer `paste_*` significa "comportamento por omissão do TinyMCE", não uma escolha explícita deste projecto).
- Os botões custom `dropcap`/`blockquote`/`twocols` chamam `editor.formatter.toggle(...)` no **clique** — a API v7 não expõe `editor.formatter` em `setup()` durante a construção; a declaração fica em `formats` no nível superior.
- O `content_style` duplica **de propósito** as regras `.news-preview-content` do `dashboard.css` (o iframe do TinyMCE não herda o CSS da página). Há comentário no código a avisar para manter os dois sincronizados — **risco de desincronização** (presente mas não verificado; ver §7).



---

## 3. Vista Blade do editor

### 3.1 Ficheiro (CONFIRMADO)

`C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\resources\views\admin\noticia-editor.blade.php`

Estrutura: `@extends('layouts.dashboard')`; secções `title`/`sidebar`/`breadcrumb`/`content`; ecrã criado **e** edição usam a mesma vista (`$noticia` presente ⇒ PUT + PUT `_method`).

### 3.2 `wire:ignore` (NÃO EXISTE)

Procurado no ficheiro e em todo `app/` + `resources/` (`Select-String -Pattern "wire:ignore"`). **Não há `wire:ignore` em lado nenhum** — o editor já **não** é Livewire:

- Em 11/11/2026 o componente Livewire `app/Livewire/Admin/NewsEditor.php` (full-page) foi **apagado** porque o morph incremental do Livewire destruía o DOM do TinyMCE (o `<textarea>` fonte é substituído pelo iframe/toolbar do TinyMCE).
- O ficheiro foi apagado **por código não commitado** (git log vazio para esse path).
- Não existe em nenhum outro ecrã do projecto `wire:ignore` — o painel tem outros componentes Livewire legítimos (tabelas CRUD), nenhum envolve o editor de notícias.

### 3.3 `@push('scripts')` (CONFIRMADO)

No **fim do ficheiro** (após `@endsection` da secção `content`). Carrega (1) o `<script src="{{ asset('js/tinymce/tinymce.min.js') }}">` e (2) o `<script>` inline com `tinymce.init({...})`, gestão de foco no primeiro campo com erro, guarda contra duplo-submit, e a pré-visualização vanilla (preenche modal em cada clique em «Pré-visualizar»; não grava nada no servidor). O layout `resources/views/layouts/dashboard.blade.php` rende esta stack na **linha 43 `@stack('scripts')`** (CONFIRMADO por grep; linha 42 = `@livewireScripts`, linha 14 = `@livewireStyles`).

### 3.4 Como o conteúdo do TinyMCE é enviado para o servidor (CONFIRMADO)

**Submissão NATIVA do `<form method="POST">`** — já **não** há Livewire, nem `$wire.set`, nem debounce, nem evento. O TinyMCE sincroniza o `<textarea id="conteudo-editor" name="conteudo">` automaticamente antes de o formulário submeter (comportamento nativo do TinyMCE na submissão; esta aplicação **não** usa callback custom de sync — **NÃO CONFIRMADO em browser**; é o fluxo standard).

- Edição ⇒ `admin.noticia-editor.update` (PUT, com `@method('PUT')`), com `['noticia' => $noticia]` na action.
- Criação ⇒ `admin.noticia-editor.store` (POST).
- Botões «Guardar rascunho»/«Publicar» levam `name="acao" value="rascunho|publicar"` — o controller lê `acao` (`$request->input('acao') === 'publicar'`).
- O botão «Pré-visualizar» é `type="button"` — **nunca** submete.

### 3.5 Vista completa (CONFIRMADO — literal)

```blade
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

```

---

## 4. Componente Livewire do editor

### 4.1 Veredicto (CONFIRMADO)

**O componente Livewire do editor NÃO EXISTE.** Foi apagado em 11/11/2026 (Fase 10.1). O substituto server-side é o controller Blade tradicional:

`C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\app\Http\Controllers\Admin\NewsEditorController.php` (listado por inteiro em §4.4).

Rotas correspondentes em `C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\routes\web.php` (dentro do grupo `Route::prefix(config('app.admin_path'))->name('admin.')` — nome sempre prefixado `admin.`):

```php
    /**
     * Editor de notícias (cms_pro.md §4.2 — "o ecrã mais importante").
     * Blade tradicional (sem Livewire) para evitar o bug de morph incremental
     * do Livewire destruir o DOM do TinyMCE.
     */
    Route::get('/noticias/nova', [NewsEditorController::class, 'create'])
        ->middleware('can:news.manage')
        ->name('noticia-nova');
    Route::post('/noticias/nova', [NewsEditorController::class, 'store'])
        ->middleware('can:news.manage')
        ->name('noticia-editor.store');
    Route::get('/noticias/{noticia}/editar', [NewsEditorController::class, 'edit'])
        ->middleware('can:news.manage')
        ->whereNumber('noticia')
        ->name('noticia-editar');
    Route::put('/noticias/{noticia}', [NewsEditorController::class, 'update'])
        ->middleware('can:news.manage')
        ->whereNumber('noticia')
        ->name('noticia-editor.update');
```

### 4.2 Propriedades públicas / estado (NÃO APLICÁVEL)

Não há propriedades Livewire; não há estado no servidor entre pedidos; não há sincronização cliente↔servidor. Estado só em request/response (validação redirect-back com `old()` + `$errors`).

### 4.3 Regras de validação (CONFIRMADO — literal, `validar()`)

```php
$regras = [
    'titulo' => ['required', 'string', 'max:190'],
    'subtitulo' => ['nullable', 'string', 'max:500'],
    'conteudo' => ['nullable', 'string'],
    'categoria' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'news')],
    'videoLink' => ['nullable', 'url:http,https', 'max:300'],
    'documentoLink' => ['nullable', 'url:http,https', 'max:300'],
    'capa' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
    'contexto' => ['nullable', 'array', 'max:10'],
    'contexto.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
];

// Só quando "Publicar":
if ($publicar) {
    $regras['conteudo'] = ['required', 'string'];
    $regras['categoria'] = ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'news')];
}
```

### 4.4 Autosave / Publicar / Guardar rascunho / Pré-visualizar (CONFIRMADO)

- **Autosave: NÃO EXISTE.** Nenhuma lógica de autosave (nem Livewire, nem JS, nem `pagehide` beacon). O que existe é a protecção HTML contra duplo-submit e o painel antigo cita `.autosave` **apenas como classe CSS morta** em `dashboard.css` (não há markup nem JS que a use — CONFIRMADO por grep no ficheiro Blade: zero ocorrências).
- **Guardar rascunho:** `acao=rascunho` ⇒ valida só `titulo` obrigatório; `status=draft`; redirect para `admin.noticia-editar` do próprio registo com flash «Rascunho guardado.».
- **Publicar:** `acao=publicar` ⇒ exige `conteudo` + `categoria`; `status=published`, `published_at=now()`, `date_label` formatado PT; redirect para `admin.noticias` com flash «Notícia publicada!» (a lista `admin/noticias.blade.php` renderiza a flash — CONFIRMADO).
- **Pré-visualizar:** 100% cliente (JS); **nunca** grava; abre/fecha modal segundo convenção do painel (`.dashboard-modal` + `.open`; `dashboard.js` trata abrir/fechar/Escape/backdrop/foco).
- **Upload de capa:** `capa` (image, PNG/JPG/WebP ≤2MB) ⇒ Spatie MediaLibrary colecção `capa` (**singleFile**, substitui a anterior, não deixa órfãs) + `news.image = 'storage/'.$media->file_name` (coluna legada mantida; a conversão **WebP** corre no upload, `nonQueued`, qualidade 85).
- **Upload de contexto:** `contexto[]` (até 10, ≤4MB cada) ⇒ colecção `contexto` (WebP também, sem substituição — acrescenta).
- **Slug:** gerado de `Str::slug(titulo)`; se existir (incluindo soft-deleted), sufixa `-1`, `-2`…; **em edição parcial o slug NÃO é rederivado** (`$atual?->slug ?? ...`).

### 4.5 Sanitização do conteúdo ANTES de guardar (CONFIRMADO: NENHUMA)

- `conteudo` → guardado **tal qual** pelo TinyMCE (`content` coluna `longText`), **sem** `strip_tags`, **sem** HTMLPurifier, **sem** whitelist de classes.
- Verificação: `Get-ChildItem -Recurse app,resources -Include *.php,*.blade.php | Select-String "strip_tags|HTMLPurifier|ezyang"` → **zero resultados** (CONFIRMADO).
- Conclusão: **qualquer HTML/JS que o TinyMCE emitir é persistido e, mais tarde, impresso raw no público** (§6). Hoje isso só permite que autores do `admin` injectem conteúdo/acções no site — modelo de ameaça «admin confiado». Se a confiança no autor mudar, a sanitização tem de entrar na escrita **e** na leitura.

### 4.6 Controller completo (CONFIRMADO — literal)

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Editor de notícias (CMS Pro §4.2) — versão Blade tradicional.
 *
 * Substitui a versão Livewire para evitar o problema de morph incremental
 * do Livewire destruir o DOM do TinyMCE (o textarea é substituído pelo
 * TinyMCE; o Livewire, sem `wire:ignore` assistido, remonta o componente
 * reescrevendo o textarea e apagando o editor).
 *
 * O TinyMCE mantém-se self-hosted. O autosave/preview são opcionais na
 * arquitetura (mantidos com JS vanilla pela robustez).
 */
class NewsEditorController extends Controller
{
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('admin.noticia-editor', [
            'noticia' => null,
            'categorias' => Category::where('type', 'news')->orderBy('name')->get(),
        ]);
    }

    public function edit(News $noticia): \Illuminate\Contracts\View\View
    {
        return view('admin.noticia-editor', [
            'noticia' => $noticia,
            'categorias' => Category::where('type', 'news')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $publicar = $request->input('acao') === 'publicar';
        $dados = $this->validar($request, null, $publicar);

        $noticia = $this->criarNoticia($request, $dados, $publicar);

        return $this->resposta($noticia, $publicar);
    }

    public function update(Request $request, News $noticia): RedirectResponse
    {
        $publicar = $request->input('acao') === 'publicar';
        $dados = $this->validar($request, $noticia, $publicar);

        $this->atualizarNoticia($request, $noticia, $dados, $publicar);

        return $this->resposta($noticia, $publicar);
    }

    /**
     * Para onde ir depois de guardar.
     *
     * Rascunho mantém o redator no editor (fluxo «guardar e continuar a escrever»
     * — o editor abre já com o registo acabado de criar/atualizar). Publicar
     * fecha o ciclo e leva à lista, com a confirmação em flash na própria
     * lista (que a renderiza).
     */
    private function resposta(News $noticia, bool $publicar): RedirectResponse
    {
        if ($publicar) {
            return redirect()
                ->route('admin.noticias')
                ->with('estado', 'Notícia publicada!');
        }

        return redirect()
            ->route('admin.noticia-editar', ['noticia' => $noticia->getKey()])
            ->with('estado', 'Rascunho guardado.');
    }

    /** @return array<string, mixed> */
    private function validar(Request $request, ?News $noticia, bool $publicar = false): array
    {
        $regras = [
            'titulo' => ['required', 'string', 'max:190'],
            'subtitulo' => ['nullable', 'string', 'max:500'],
            'conteudo' => ['nullable', 'string'],
            'categoria' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'news')],
            'videoLink' => ['nullable', 'url:http,https', 'max:300'],
            'documentoLink' => ['nullable', 'url:http,https', 'max:300'],
            'capa' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'contexto' => ['nullable', 'array', 'max:10'],
            'contexto.*' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ];

        if ($publicar) {
            $regras['conteudo'] = ['required', 'string'];
            $regras['categoria'] = ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'news')];
        }

        return $request->validate($regras, [
            'capa.max' => 'A imagem de capa tem no máximo 2MB.',
            'capa.mimes' => 'A capa tem de ser PNG, JPG ou WebP.',
            'videoLink.url' => 'O link do vídeo tem de ser um URL válido (https://…).',
            'documentoLink.url' => 'O link do documento tem de ser um URL válido (https://…).',
            'conteudo.required' => 'Escreve o corpo da notícia antes de publicar.',
            'categoria.required' => 'Escolhe a categoria antes de publicar.',
        ]);
    }

    private function criarNoticia(Request $request, array $dados, bool $publicar): News
    {
        $noticia = new News();
        $noticia->fill($this->atributos($dados, $publicar, $request));
        $noticia->save();

        $this->processarMidia($request, $noticia);

        return $noticia;
    }

    private function atualizarNoticia(Request $request, News $noticia, array $dados, bool $publicar): void
    {
        $noticia->fill($this->atributos($dados, $publicar, $request, $noticia));
        $noticia->save();

        $this->processarMidia($request, $noticia);
    }

    /**
     * Copia os campos do formulário para o modelo.
     *
     * Submissão parcial: em edição, um campo ausente do pedido mantém o valor
     * actual (mesma regra do formulário genérico — um POST incompleto não pode
     * apagar excerto/conteúdo/categoria). Campo presente mas vazio limpa-se.
     *
     * @return array<string, mixed>
     */
    private function atributos(array $dados, bool $publicar, Request $request, ?News $atual = null): array
    {
        $tem = fn (string $campo) => $request->has($campo);

        return [
            'title' => trim($dados['titulo']),
            'slug' => $atual?->slug ?? $this->slugUnico($dados['titulo']),
            'excerpt' => $tem('subtitulo')
                ? (trim((string) ($dados['subtitulo'] ?? '')) ?: null)
                : $atual?->excerpt,
            'content' => $tem('conteudo')
                ? ($dados['conteudo'] ?? null)
                : $atual?->content,
            'category_id' => $tem('categoria')
                ? ($dados['categoria'] ?? null)
                : $atual?->category_id,
            'video_link' => $tem('videoLink')
                ? ($dados['videoLink'] ?? null)
                : $atual?->video_link,
            'documento_link' => $tem('documentoLink')
                ? ($dados['documentoLink'] ?? null)
                : $atual?->documento_link,
            'status' => $publicar ? 'published' : ($atual?->status ?? 'draft'),
            'published_at' => $publicar ? ($atual?->published_at ?? now()) : $atual?->published_at,
            'date_label' => $publicar
                ? ($atual?->date_label ?? now()->locale('pt')->translatedFormat('d M Y'))
                : $atual?->date_label,
            'author_id' => $request->user()?->id,
        ];
    }

    private function processarMidia(Request $request, News $noticia): void
    {
        if ($request->hasFile('capa')) {
            $media = $noticia->addMedia($request->file('capa'))->toMediaCollection('capa');
            $noticia->image = 'storage/' . $media->file_name;
            $noticia->save();
        }

        foreach ($request->file('contexto', []) as $ficheiro) {
            $noticia->addMedia($ficheiro)->toMediaCollection('contexto');
        }
    }

    /** Slug único global (rascunhos e publicações convivem). */
    private function slugUnico(string $titulo): string
    {
        $base = Str::slug($titulo);
        $candidato = $base;
        $n = 1;

        while (News::query()->withTrashed()->where('slug', $candidato)->exists()) {
            $candidato = $base . '-' . $n++;
        }

        return $candidato;
    }
}

```



---

## 5. Base de dados da notícia

### 5.1 Modelo `News` (CONFIRMADO — literal completo)

`C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\app\Models\News.php`

```php
<?php

namespace App\Models;

use App\Models\Concerns\Contentful;
use App\Models\Concerns\SearchesContent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Notícia (restrutura.md §3.4).
 *
 * Único modelo com Spatie Media Library (restrutura.md §7.5):
 *  - coleção `capa` — imagem principal (uma por notícia);
 *  - coleção `contexto` — imagens de contexto do corpo;
 *  - conversão WebP nas duas coleções.
 *
 * Vídeo e documento são APENAS LINK (`video_link`, `documento_link` —
 * restrutura.md §1). A coluna legada `image` mantém-se enquanto as vistas
 * usam o campo antigo; a migração para `capa` acontece nas Fases 4-5.
 */
class News extends Model implements HasMedia
{
    use Contentful, HasFactory, InteractsWithMedia, SearchesContent, SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'category_id', 'author_id', 'title', 'slug', 'excerpt', 'content',
        'image', 'image_caption', 'status', 'published_at', 'date_label',

        'province',

        // Campos de `admin/noticia-nova.html` (tags + SEO).
        'tags', 'meta_title', 'meta_description',

        // Links externos (restrutura.md §3.4) — vídeo e documento são só link.
        'video_link', 'documento_link',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'views_count' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        // Imagem principal — uma (a última submetida substitui a anterior).
        $this->addMediaCollection('capa')->singleFile();

        // Galeria de imagens de contexto do corpo do artigo.
        $this->addMediaCollection('contexto');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        // Conversão WebP (restrutura.md §3.4). `nonQueued` porque não há
        // worker de filas no servidor atual — a conversão corre no request.
        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(85)
            ->performOnCollections('capa', 'contexto')
            ->nonQueued();
    }
}

```

### 5.2 Migrations (CONFIRMADO — literais)

Criação da tabela (`2026_09_28_220002_create_content_tables.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'news',
        'projects',
        'suppliers',
        'events',
        'legislation',
        'research',
        'interviews',
        'technologies',
        'directory',
        'training',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content')->nullable();
                $table->string('image')->nullable();
                $table->string('image_caption')->nullable();
                $table->string('status', 20)->default('published')->index();
                $table->date('published_at')->nullable()->index();
                $table->unsignedInteger('views_count')->default(0);
                $table->timestamps();
                $table->softDeletes();

                if ($name === 'news') {
                    $table->string('province', 120)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::dropIfExists($name);
        }
    }
};

```

`date_label` (`2026_09_28_220004_add_date_label_to_content_tables.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'news',
        'projects',
        'suppliers',
        'events',
        'legislation',
        'research',
        'interviews',
        'technologies',
        'directory',
        'training',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('date_label', 40)->nullable()->after('published_at');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('date_label');
            });
        }
    }
};

```

Campos dos formulários `noticia-nova` (tags/SEO) (`2026_09_30_020000_add_form_fields_to_news_and_projects_tables.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colunas para os formulários `admin/noticia-nova.html` e `admin/projeto-novo.html`
 * (decisão do utilizador, 30/09/2026): campos que o protótipo mostra e que não
 * tinham onde ser guardados.
 *
 * `news`: `tags`, `meta_title`, `meta_description`.
 * `projects`: `mineral`, `province` (já via news), `phase`, `operator`,
 * `area` (km², decimal), `documents` e `images` (JSON com os caminhos, guardados
 * sem exibição — o protótipo não tem galeria nem lista de documentos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('tags')->nullable()->after('image_caption');
            $table->string('meta_title')->nullable()->after('tags');
            $table->string('meta_description')->nullable()->after('meta_title');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('province', 64)->nullable()->after('image_caption');
            $table->string('mineral', 64)->nullable()->after('province');
            $table->string('phase', 64)->nullable()->after('mineral');
            $table->string('operator')->nullable()->after('phase');
            $table->decimal('area', 12, 2)->nullable()->after('operator');
            $table->json('documents')->nullable()->after('area');
            $table->json('images')->nullable()->after('documents');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['tags', 'meta_title', 'meta_description']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['province', 'mineral', 'phase', 'operator', 'area', 'documents', 'images']);
        });
    }
};

```

Links externos (`2026_11_10_120004_add_video_and_document_links_to_news_table.php`):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos externos das notícias (restrutura.md §3.4).
 *
 * Vídeo e documento suportam APENAS link (restrutura.md §1 — "Vídeos e
 * Documentos suportam apenas Link"); as imagens (capa/contexto) passam a
 * ser geridas pelo Spatie Media Library no modelo News.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('video_link')->nullable()->after('image_caption');
            $table->string('documento_link')->nullable()->after('video_link');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['video_link', 'documento_link']);
        });
    }
};

```

### 5.3 Colunas e metadados de `news` (CONFIRMADO)

| Campo | Presente? | Tipo / detalhe |
|---|---|---|
| `id` | ✔ | bigint |
| `category_id` | ✔ | FK `categories(id)`, nullOnDelete |
| `author_id` | ✔ | FK `users(id)`, nullOnDelete — **autor existe** |
| `title` | ✔ | string |
| `slug` | ✔ | string **unique** |
| `excerpt` | ✔ | text nullable — **resumo existe** (no CMS o campo chama-se «subtítulo») |
| `content` | ✔ | longText nullable — corpo |
| `image` | ✔ | string nullable — capa «legada» (string `storage/…` — preenchida pela Media Library no upload de capa) |
| `image_caption` | ✔ | string nullable |
| `status` | ✔ | string(20) default `published`, index |
| `published_at` | ✔ | **date** (não datetime), nullable, index |
| `views_count` | ✔ | unsignedInteger default 0 — **visualizações existem (contador interno)** |
| `province` | ✔ | string(120) — só `news` |
| `date_label` | ✔ | string(40) nullable (texto já localizado, ex. «11 nov 2026») |
| `tags` | ✔ | string nullable |
| `meta_title` | ✔ | string nullable — **SEO existe** |
| `meta_description` | ✔ | string nullable — **SEO existe** |
| `video_link` | ✔ | string nullable (link APENAS) |
| `documento_link` | ✔ | string nullable (link APENAS) |
| `timestamps` | ✔ | created_at/updated_at |
| `softDeletes` | ✔ | `deleted_at` |
| **`scheduled_at` / agendamento futuro** | **NÃO EXISTE** | `published_at` é `date`; não há mecanismo de publicação agendada (nenhum job/scheduler; o `scopePublished` exige `status=published AND published_at <= now()` — para data futura a notícia fica simplesmente invisível até à data, **sem publicar automaticamente**; a UI não expõe este campo) |
| **destaque / featured** | **NÃO EXISTE** | nenhuma coluna de destaque |
| `views_count` externa vs. `page_views` | ✔ | as visitas da home são contadas à parte na tabela `page_views` (migration `2026_11_11_000200_create_page_views_table.php`) — `news.views_count` é a coluna histórica (grep por incrementos por rota pública: **não verificado**) |

Casts relevantes (CONFIRMADO do modelo): `published_at` → `date`, `views_count` → `integer`. Datas de criação/edição via `timestamps`. `SoftDeletes` ativo.

---

## 6. Como o artigo aparece no site público

### 6.1 Vista `news.show` (CONFIRMADO — literal, linhas 74–115)

`C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\resources\views\news\show.blade.php`:

```blade
<!-- single-post-content   -->
                                        <div class="single-post-content spc_column fl-wrap">
                                            <div class="single-post-content_column">
                                                <div class="share-holder ver-share fl-wrap">
                                                    <div class="share-title">Partilhar <br> Article:</div>
                                                    <div class="share-container  isShare"></div>
                                                </div>
                                            </div>
                                            <div class="fs-wrap smpar fl-wrap">
                                                <div class="fontSize"><span class="fs_title">Tamanho da letra: </span><input type="text" class="rage-slider" data-step="1" data-min="12" data-max="15" value="12"></div>
                                                <a class="print-btn" href="javascript:window.print()" data-microtip-position="bottom"><span>Imprimir</span><i class="fal fa-print"></i></a>
                                            </div>
                                            <div class="clearfix"></div>
                                            {!! $post->content !!}
                                            <div class="single-post-footer fl-wrap">
                                                <div class="post-single-tags">
                                                    <span class="tags-title"><i class="fas fa-tag"></i> Tags : </span>
                                                    <div class="tags-widget">
                                                        <a href="#">Internacional</a>
                                                        <a href="#">Água</a>
                                                        <a href="#">Ambiente</a>
                                                        <a href="#">Lifestyle</a>
                                                    </div>
                                                </div>
                                            </div>
```

### 6.2 Saída do conteúdo (CONFIRMADO)

**Linha 93: `{!! $post->content !!}`** — HTML **cru, sem fugar**, directamente.

### 6.3 Existe sanitização na saída? (CONFIRMADO: NÃO)

- `grep`s em `app/` e `resources/` por `strip_tags|HTMLPurifier|ezyang` → **zero resultados**.
- `composer.json` não inclui `ezyang/htmlpurifier` (só laravel/*, livewire, spatie medialibrary + seus deps).
- Consequência directa: o corpo da notícia é emitido como está na BD; **desde que o autor seja confiado**, o HTML do TinyMCE (`<p class="has-drop-cap">`, `<blockquote>`, `<img src>`, `<h2>`…) chega ao browser sem alterações.

### 6.4 CSS aplicado ao corpo do artigo (CONFIRMADO)

Tema **Gmag** (`public/css/style.css`, tema de 12/2021, pré-CMS, mantido em paralelo com CMS Pro). Classes/elementos e ficheiros:

| Elemento/classe | Selector-pai / regra (CONFIRMADO) | Ficheiro |
|---|---|---|
| Container do artigo | `.single-post-content` / `.spc_column` — `position: absolute` a `.single-post-content_column`; `.single-post-content` usa `fl-wrap` para o fluxo | `public/css/style.css:81-83, 2481, 5323, 5356` |
| Coluna de partilha (sticky) | `.single-post-content_column` + `.ver-share .share-title:before` | idem |
| Tamanho da letra | `.fontSize` (+ `.fs_title`, `.rage-slider`) — controlo JS do tema | style.css:~2521 |
| Corpo | `.single-post-content_text p` — `font-size: 12px; font-weight: 400; color:#000` | style.css:2503 |
| Imagens no corpo | `.single-post-content_text_media` + `img{border-radius:4px}` | style.css:2497-2501 |
| **Letra capitular** | `.has-drop-cap:first-letter` — `font-size:70px; float:left; padding:20px 20px 40px 0; font-family:'Playfair Display',cursive; color:#000` | **style.css:~2489-2496** |
| **Blockquote** | `blockquote` (global) — `float:left; padding:20px; border-left:15px solid #eee; background:#f9f9f9` ; `blockquote p` — Playfair; `blockquote:before` — ícone Font Awesome quote (conteúdo de ícone de citação) | **style.css:56-75** (tema Gmag) |
| **Duas colunas** | `.texto-duas-colunas` — `column-count:2; column-gap:2em` (adicionado no CMS Pro) | **style.css:~5556** |
| Títulos no corpo | não há regras específicas de `h2`/`h3` dentro do artigo; herdam do tema global | **NÃO DEFINIDO específico** |
| Imagens soltas | `img{max-width:100%}` **só** via `content_style` do TinyMCE e regras de media queries do tema — **não há regra project-specific para `<img>` solto no artigo** | — |
| Preview no admin | regras **diferentes** em `.news-preview-content*` (dashboard.css:1632-1644) — port-foward visual do público, **não idênticas** | dashboard.css |

---

## 7. Estilos especiais: editor vs. site

| Botão | (a) HTML que o TinyMCE gera | (b) classe/elemento | (c) existe no CSS do site? | (d) sobrevive a guardar e mostrar? |
|---|---|---|---|---|
| **Letra capitular** (`formats.dropcap`, `selector:'p'`) | `<p class="has-drop-cap">Texto…</p>` | `.has-drop-cap` | **SIM** — `style.css:.has-drop-cap:first-letter` (70px, preto, Playfair cursive) + contrapartida `.news-preview-content .has-drop-cap` no admin | **SIM** — guardar não filtra classes (§4.5); mostrar é raw (§6.2). **Divergência visual**: pública #000/70px/`cursive` vs admin preview #C8102E/2.4em/`Playfair Display` — **não são idênticas** |
| **Duas colunas** (`formats.twocols`, `selector:'p'`) | `<p class="texto-duas-colunas">Texto…</p>` | `.texto-duas-colunas` | **SIM** — adicionado no CMS Pro: `style.css` `.texto-duas-colunas{column-count:2;column-gap:2em}` | **SIM** — mesma cadeia (guardar raw, mostrar raw) |
| **Citação em destaque** (`formats.blockquote`, `block:'blockquote'`) | `<blockquote><p>Texto…</p></blockquote>` | elemento `<blockquote>` | **SIM** — estilos globais do tema Gmag (`border-left:15px solid #eee; background:#f9f9f9; float:left`) | **SIM** — sobrevive; **divergência** no admin (`.news-preview-content blockquote` tem border 4px, itálico e `#FDF3F5` — não idênticos ao público) |

Conclusão (CONFIRMADO): **as três funcionalidades sobrevivem** à escrita/leitura porque não há sanitização nem whitelist; a diferença real é o aspecto entre admin (preview) e público — **dois sistemas de regras paralelos**; qualquer alteração tem de ser feita nos dois sítios (comentário no código avisa disso, mas não há teste que o garanta).

---

## 8. Upload de imagens dentro do editor

### 8.1 Botão «Imagem» do TinyMCE (CONFIRMADO — do código init)

- Plugin `'image'` presente ⇒ o botão aparece na toolbar.
- **Não há `images_upload_handler` nem `automatic_uploads` nem `images_upload_base_path`** — nenhum handler de upload está registado no `tinymce.init`.
- Consequência no TinyMCE v7 (docs do produto): o diálogo de imagem aceita **URL externa ou HTML colado**; não faz upload para qualquer endpoint. **O comportamento exacto do diálogo nesta build (incluindo a acção "Upload" ausente) não foi executado em browser — NÃO CONFIRMADO em runtime; o facto de código é que não há handler** (CONFIRMADO).
- **Não passa pela Media Library** — imagens inseridas no corpo são **URLs externos** (ou HTML embedado colado); **não** são transferidas para `storage/`, **não** são convertidas para WebP, **não** são otimizadas, **não** são associadas à notícia.
- Upload nativo para o servidor só acontece pelos **campos próprios**: «Imagem de capa» (`capa` ⇒ Spatie colecção `capa`) e «Imagens de contexto» (`contexto[]` ⇒ colecção `contexto`) — ambos via `<input type="file">` do formulário tradicional (`enctype="multipart/form-data"` + `@csrf`), e **não** via TinyMCE.

---

## 9. Sistema visual do painel

### 9.1 Variáveis CSS (`:root`) — NÃO EXISTEM no projecto (CONFIRMADO)

`grep -E -- '--[a-zA-Z]'` por ficheiro:

| Ficheiro | Custom properties `--…` | Observação |
|---|---|---|
| `public/css/color.css` (17 linhas) | **0** | Só overrides de marca (#C8102E, `#A00D25` implicit) — re-tinting selectivo do tema Gmag |
| `public/css/dashboard.css` | **0** | Painel não usa `:root`/`--var`; constantes repetidas inline |
| `public/css/style.css` | **0** (as ocorrências de `------` são comentários, não vars) | Tema Gmag sem variáveis |
| `public/css/menu.css`, `public/css/topo.css` (CMS Pro) | **0** | — |
| `public/css/plugins.css` (vendor) | **0** `--bs-*` | não é Bootstrap var-based |
| inline do Livewire | `:root{--livewire-progress-bar-color: #2299dd;}` | injectado pelo runtime Livewire em todas as páginas admin (CONFIRMADO em `phpunit` output) — a única `:root` visível no painel, e não é do projecto |

### 9.2 Componentes existentes (CONFIRMADO — `grep` das classes no ficheiro)

**Botões** (`dashboard.css`): `.btn-primary` / `.btn-primary:hover` · `.btn-secondary` (dourado #F2A900) · `.btn-danger` (#8B0000) · `.btn-ghost` · `.btn-success` (#2E7D32) · `.btn-sm` · `.btn-block` · `.btn-google` · `.btn-quick` (banner de acção rápida do dashboard)

**Cartões**: `.card` · `.card-head` · `.card-title` · `.card-actions` · `.card-body` · variante do editor: `.editor-actions`, `.editor-actions-body`, `.editor-grid`, `.editor-col`

**Inputs/formulário**: `.dashboard-form-group` · `.dashboard-form-label` · `.dashboard-form-input` · `.dashboard-form-select` · `.dashboard-form-textarea` · `.dashboard-form-row` · `.dashboard-form-col` · `.dashboard-form-checkbox` · `.dashboard-form-hint` · `.dashboard-form-actions` · `.upload-box` · `.help-text` · `.form-error` (role=alert)

**Badges de estado**: `.status-badge` (base) · `.active` (verde #256628 — AA corrigido) · `.pending` · `.rejected` · `.neutral` — **não existe toast**.

**Tabelas**: `.table-wrap` · `.dashboard-table` · `.table-user` — as CRUDs Livewire usam markup próprio com estas classes (CONFIRMADO no diff).

**Modais**: `.dashboard-modal` · `.open` · `.dashboard-modal-content` · `.dashboard-modal-header` · `.dashboard-modal-close` (× com alvo ≥24px — AA corrigido) · `.dashboard-modal-body` · `.dashboard-modal-footer` — convenção global do painel (`dashboard.js`).

**Avisos/toasts**: **NÃO EXISTE toast/snackbar** em lado nenhum do projecto (grep em `*.blade.php`, `dashboard.js`, `scripts.js` → zero). O único «aviso» é o **flash de sessão** renderizado como `.status-badge.active` + `role="status"` na própria lista (`noticias.blade.php`) e no editor. Erros de validação: `.form-error` `role="alert"` junto ao campo.

**Estados vazios**: `.filter-empty` (dashboard.css:1438) e `.chart-vazio` (dashboard.css:1469) — **só** para filtros e para o gráfico dos 7 dias sem visitas. **Não existe** estado vazio para listas CRUD genéricas nem para o corpo do editor (o placeholder do preview é o único).

**O que NÃO existe (lista honesta)**: toast/snackbar · badge de notificação genérica reutilizável (o sino usa `<span class="badge">` local genérica, sem componente próprio) · skeletons · dropdown/tooltip reutilizável · componente de paginação estilizado reutilizável (tabelas usam `dashboard-pagination`) · `:root` com tokens · breakpoint var · estado vazio CRUD genérico.


---

## 10. Ambiente

### 10.1 Desenvolvimento local (CONFIRMADO — `.env`, sem segredos)

```
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
CACHE_STORE=database
SESSION_DRIVER=database
ADMIN_PATH=gestao-pigea
```

- Base local declarada: **sqlite** (`.env.example` também aponta para `sqlite`, com host/DB comentados).
- A suíte de testes corre contra **MySQL**: `phpunit.xml` define `DB_CONNECTION=mysql`, `DB_DATABASE=pigea_test` (por cima de qualquer `.env`); a suíte executa com `DB_USERNAME=root` e `DB_PASSWORD` vazia no shell.
- A entrega de desenvolvimento ao vivo (validação visual nas fases) usou **MySQL via XAMPP** (base `pigea`), que nesta máquina **crasha periodicamente** (tabelas Aria privilegiadas) — reinícios/reparos têm sido `aria_chk`/`ALTER TABLE … FORCE` server-side.
- PHP: **8.2.12** (XAMPP; `php -v` → `8.2.12`).
- `ADMIN_PATH=gestao-pigea` (alterado pelo cliente; **não** voltar a `admin`).

### 10.2 Versões de framework (CONFIRMADO)

| Pacote | Versão pedida/instalada |
|---|---|
| Laravel | `^12.0` |
| Livewire | `^3.8.3` |
| Tailwind CSS | `^4.0.0` (via `@tailwindcss/vite ^4.0.0`) — **não** usado pelo painel admin (CSS próprio `dashboard.css`) nem pelo tema público (tema Gmag); presente por necessidade do build front |
| Spatie MediaLibrary | `^11.23` |
| Vite | (dev-build do público; o admin e o TinyMCE **não** passam por ele) |
| Node (verificação local) | `v24.22.0` |

- Livewire continua instalado e em uso pelo painel (tabelas CRUD) e por partes do frontend (`DirectoryListing`, `RegistoEmpresa/Profissional`, etc.) — **remover `@livewireScripts` quebraria essas páginas**.

---

## 11. Testes manuais do bug do editor

**Método:** esta sessão não dispõe de browser headless/CLI para conduzir o site autenticado. Os pedidos pedidos de teste manual abaixo ficam **NÃO TESTÁVEL SEM BROWSER**. A consola PHPUnit pode validar o lado servidor (rotas/BD), mas **não** observa o TinyMCE/iframe/DOM no runtime.

| Teste | Resultado | Porquê |
|---|---|---|
| a) Escrever texto e esperar 10 s (autosave) — o editor mantém-se? | **NÃO TESTÁVEL SEM BROWSER** | **Autosave NÃO EXISTE** nesta versão: não há timer, beacon, nem handler; o editor manter-se-á obviamente em browser (nada está programado para o destruir) mas isso não foi observado. A suíte cobre CI/BD, não o runtime JS. |
| b) Publicar com título vazio — o conteúdo do editor mantém-se? | **NÃO TESTÁVEL SEM BROWSER** | O fluxo redirect-back server-side está coberto em `AdminFormsTest::test_criar_noticia_sem_titulo_devolve_erros` (302 + errors na sessão). Se o `old('conteudo')` re-renderiza o textarea com o HTML — CONFIRMADO no código (`{{ old('conteudo', $noticia?->content ?? '') }}`) — o **conteúdo textual do servidor** mantém-se **se ainda não se tiver clicado em publicar**. Em runtime real (TinyMCE hidratado vs. HTML re-impresso no POST-redirect) implica validação visual. |
| c) Escolher imagem de capa e mudar categoria — o editor mantém-se? | **NÃO TESTÁVEL SEM BROWSER** | Mesma nota: o upload da capa é processado server-side em POST completo; mudança de categoria é um `<select>` normal. Nada foi observado em runtime. |

Comportamento **coberto por teste** (não manual): criação/publicação/redirect/flash/validação — `tests\Feature\AdminFormsTest.php`; edição genérica — `test_editar_actualiza_os_campos`; o caminho do editor próprio — `test_pagina_de_edicao_abre_para_todos_os_tipos` (noticias) e `AdminContentFormTest`.

---

## O QUE NÃO FOI POSSÍVEL CONFIRMAR (lista honesta)

1. **Autosave (a)**: não existe; o comportamento visual/runtime não foi observado.
2. **Publicação com erro no runtime (b)**: possível degradação real (perda de conteúdo para o TinyMCE vs. re-impressão via `old()`), **não verificada em browser**. O teste `test_criar_noticia_sem_titulo_devolve_erros` comprova 302 + errors, não a sobrevivência visual do iframe.
3. **Diálogo de imagem do TinyMCE (c-subconjunto)**: comportamento exacto do botão «Imagem» nesta build (ausência de upload; URLs/colagem) — confirmado por **grep do código**, não executado.
4. **`old('conteudo')` vs. conteúdo vivo**: o TinyMCE esconde o textarea; se a submissão completa **não** acontecer (voltar/re-entrar), não há mecanismo que re-hidrate o editor com o HTML recente; o comportamento real observado depende do browser: **não verificado**.
5. **`published_at` vs. agendamento futuro**: confirmado por schema + código (não há scheduler). A excepção teórica é `published_at` preenchido manualmente com data futura = notícia fica invisível mesmo com `status=published` até à data passar (pelo `scopePublished`) — **não validado em dados reais, confirmado por lógica**.
6. **Views públicas vs. `page_views`**: foi adicionado recentemente um contador separado (`page_views`, middleware `CountPublicVisits`); a coluna legada `news.views_count` **não foi reconciliada** nem verificado o que a incrementa — estado desconhecido.
7. **`.autosave` em `dashboard.css`**: classe morta (sem markup/JS correspondente) — confirmado; **não** removida (política deste diagnóstico: não alterar).
8. **`formatos` do TinyMCE** (`dropcap`/`twocols`/`blockquote`): confirmados na declaração; a **geração real do HTML** no iframe (por ex. `</p>` vs `<p …>` exacto) não foi executada.
9. **CSP efectiva**: comentário anterior dizia `script-src 'self'` mas a implementação real é `Content-Security-Policy-Report-Only` (comentário já corrigido no código); a política viva (e o seu report-only report endpoint `csp-report`?) não foi validada em runtime.
10. **Suíte completa**: não executada nesta sessão para evitar efeitos em ambiente (a última execução 11/11/2026: **OK (156 tests, 567 assertions)**).
11. **Media queries do painel (`.editor-grid`)**: colapsam @1064px — confirmado no CSS; **não** verificado em 390px reais.
12. **`language_url` `pt_PT.js`**: existe no `public/js/tinymce/langs/`; conteúdo/tradução completa do pack não foi lida nem auditada.

---

### Notas de âmbito e integridade

- Este documento foi produzido **sem browser** e **sem** observar o site em runtime. Todas as afirmações sobre runtime estão explicitamente marcadas como **NÃO CONFIRMADO**.
- Nenhuma linha de código do projecto foi alterada, criada ou apagada (excepção: este ficheiro `diagnostico_editor.md`, na raiz de `pigea-deploy/`, como pedido).
- Caminho completo de cada ficheiro citado é indicado na secção respetiva e/ou no título.
