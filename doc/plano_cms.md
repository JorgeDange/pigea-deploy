# plano_cms.md — Melhoria do Editor de Notícias e do CMS PIGEA

**Data:** 11/11/2026 · **Autor:** sessão de agente · **Fonte:** `cms_v.md` (PROMPT_MASTER) · **Estado:** plano antes de qualquer alteração de código (regra #1 de cms_v.md)

> Executa as Fases 1–7 + documentação (Fase 8 = opcional, só se 1–7 estiverem verdes). Sujeito às regras de cms_v.md: sem novas cores, sem commit/push sem pedido, sem alterar tudo o que estiver fora deste prompt.

## Decisões técnicas (e alternativas rejeitadas)

| Decisão | Escolha | Porquê |
|---|---|---|
| CSS do artigo | `public/css/article.css` com escopo `.article-body` (sem Vite) | Consistente com todo o frontend (ficheiros estáticos), TinyMCE consegue carregá-lo via `content_css` como caminho `/css/article.css` |
| Parcial Blade único | `resources/views/news/partials/article-body.blade.php` com `<div class="article-body">{!! $content !!}</div>` | Usado pelo site público (show), pelo preview da PRÉ-VISUALIZAÇÃO do admin (endpoint POST novo) e pelo TinyMCE via `content_css` — o admin deixa de ter CSS divergente |
| Preview no admin | Novo endpoint `POST /gestao-pigea/noticias/pre-visualizar` (JSON→HTML) no `NewsEditorController`, sem gravar | Cumpre literalmente "o mesmo parcial Blade"; vanilla fetch; mantém o modal actual; fecha com #11 (sem sucesso sem prova) |
| Whitelist única | `config/article.php` — usada pelo TinyMCE (runtime de um só sítio) e pelo purificador | Uma fonte de verdade; regra #5 |
| Sanitizador | `ezyang/htmlpurifier` via Composer (dep direta, sem wrappers) se o registry responder | Exigência explícita da Fase 3; `mews/purifier` como alternativa; **sem rede ⇒ bloqueia-se e reporta-se, não se inventa purifier próprio** |
| Upload corpo | `POST {ADMIN_PATH}/noticias/imagem`, permissão admin/editor, `storage/app/public/noticias/corpo/AAAA/MM/`, GD resize máx. 1200 px + WebP(q85), JSON `{location}` | Fase 4 literal; Staff Media Library fora porque a notícia pode ainda não existir |
| Estados da notícia | `rascunho` / `em_revisao` / `publicado` como valores na coluna `status` existente; transição por papel; constante `config('cms.editor_pode_publicar')` | Fase 6 sem migration de status (o `status` é string(20)); `rascunho`≡`draft` actual, `publicado`≡`published` actual |
| Agendamento | Só se 1–7 verdes ao fim: migration segura `published_at date → datetime` + campo "Publicar em" + `Task Scheduler` documentado (não automatizado sem OK) | Fase 8 opcional |
| Imagens seed/origem | Normalizar `database/data/news.json` para as classes novas; `articles:sanitize --dry-run` para os artigos já gravados (executar só com OK) | Mantém a regra "manter a mesma saída HTML sempre que possível" dos artigos guardados |
| Preview do artigo | Parcial + `article.css` — as duas páginas ficam idênticas; `.news-preview-*` do preview actual **removido** após migração (sem código morto) | Fase 1 tarefas 2–3 |
| Backup antes de migrations | `php artisan backup:create` NÃO existe; backup lógico = `mysqldump` da base `pigea` para `backups/pigea_pre_cms_AAAAMMDD.sql` ANTES de qualquer migration (Fase 8.2); Fases 1–7 NÃO fazem migrations (a única possível depende da Fase 8) | Regra #7 |

**Premissas registadas**: `.env` aponta para `mysql` (confirmado; suíte verde em `pigea_test`; dev usa `pigea`); o site corre com seed actualizado.

## Ordem e ficheiros a tocar, por fase

### Fase 1 — article.css + parcial
- **Novos**: `public/css/article.css`; `resources/views/news/partials/article-body.blade.php`
- **Modificados**: `resources/views/news/show.blade.php` (usa parcial; amarrar CSS); `resources/views/admin/noticia-editor.blade.php` (preview usa `/css/article.css` + wrapper `.article-body`; remover regras `.news-preview-*` específicas do dashboard.css); `public/css/dashboard.css` (apagar `.news-preview-*` só após migração; manter o resto); `public/css/style.css` (remover bloco CMS Pro `.texto-duas-colunas` se a Fase 1 consolidar para `.article-body .two-cols`; ajustar `.has-drop-cap` global → só para trás, sem breaking)
- **Verificar**: site público HTTP 200 + mesma cara (spot check manual = Jorge, porque sem browser isto só se confirma por leitura)

### Fase 2 — TinyMCE completo
- **Modificados**: `resources/views/admin/noticia-editor.blade.php` (init completo; `block_formats` P/H2/H3; `menubar` reduzido; toolbar agrupada; `style_formats` "Estilos do PIGEA"; `paste_*`; `images_upload_handler`; `automatic_uploads`; `image_description`/`image_caption`; `height`, `elementpath`, `statusbar` counter)
- **Novos**: `public/js/admin/news-editor.js` (move o init para ficheiro próprio; view fica limpa); `config/article.php` (lista única)
- **Remover**: botões `dropcap`/`twocols`/`blockquote` antigos; `.autosave` se continuar morto (só se associado ao 8.1)

### Fase 3 — Sanitização
- **Novos**: `composer require ezyang/htmlpurifier`; `app/Services/ArticleSanitizer.php`; `app/Console/Commands/SanitizeArticles.php` (`articles:sanitize --dry-run`); `tests/Feature/ArticleSanitizerTest.php`
- **Modificados**: `NewsEditorController` (sanitizar ao guardar: create/update + uploads? só corpo); check `old('conteudo')` caminho de erro já repopula

### Fase 4 — Upload no corpo
- **Novos**: `app/Http/Controllers/Admin/NoticiaImagemController.php`; `tests/Feature/NoticiaImagemUploadTest.php`
- **Modificados**: `routes/web.php` (rota); `resources/views/admin/noticia-editor.blade.php` (config TinyMCE: `images_upload_handler` + help text); `config/filesystems.php` se necessário

### Fase 5 — Painel
- **Modificados**: `resources/views/layouts/dashboard.blade.php` (skip link; topbar sem logout duplicado; nome+papel); `components/dashboard-header.blade.php` (nome, papel, sino alinhado); `public/css/dashboard.css` (sticky `.editor-actions`, breadcrumb linha única, ghost para pré-visualizar, `.toast`, `.modal-confirm`, `.empty-state`, `.is-loading`, nova classe `.btn` uniformes, `.file-upload` component); `public/js/dashboard.js` (toast global, confirmação apagar, submit loading); sidebar `components/sidebar-admin.blade.php` (remover Sair duplicado se ficar no topbar)

### Fase 6 — Campos + estados/fluxo
- **Modificados**: `NewsEditorController` (slug editável + unicidade check; contadores media_title/meta_description/excerpt via JS; alt/crédito da capa); `resources/views/admin/noticia-editor.blade.php` (campos Slug/Resumo/Tags/SEO recolhido/alt+crédito capa; badge de estado); `app/Models/News.php` e `config/cms.php` (nova const `editor_pode_publicar`); `resources/views/admin/noticias.blade.php` (mostrar estado; provavelmente pouco — o Livewire content-table já vive na lista)
- **Estados**: usar `rascunho`/`em_revisao`/`publicado` (map para `draft`/`in_review`/`published`), hífen legível no badge; conforme leitura tranquila da regra "Não alterar o que não está neste prompt"

### Fase 7 — Lista de notícias
- **Modificados**: `app/Livewire/Admin/ContentTable.php` (ou novo componente `NewsListTable` exclusivo; duplicar/eliminar/restaurar); `resources/views/livewire/admin/content-table.blade.php`; `public/css/dashboard.css` (badges estado; estado vazio)
- **Interação**: `duplicar` = clone não-guardado com novo slug único + estado rascunho; `apagar` = modal confirmação; `restaurar` = `onlyTrashed` scope (SoftDeletes já existe)
- **Risco controlado**: manter o ContentTable genérico intacto para os outros tipos; criar `NewsListTable` só para notícias? Decisão: **estender ContentTable especializado** um pouco; se ficar confuso, novo componente só para notícias — documentar a decisão tomada em `modificar.md`

### Fase 8 (opcional) — só se 1–7 verdes

## Rastreio de verificação (regras #10, #11)
Cada fase fecha com: `php artisan test` verde; `php -l` dos ficheiros alterados; entrada nova numerada em `modificar.md` + `memoria.md`. O que não puder ser verificado sem browser vai para `verificar_cms.md` (Jorge).

## Riscos e mitigação
- **Rede/registry indisponível** para `composer require` → bloqueio da Fase 3: parar e reportar (não inventar purifier).
- **Seed com classes antigas** (`has-drop-cap`, `texto-duas-colunas`) → normalizar `news.json` + comando `articles:sanitize --dry-run` (não executar sem OK).
- **MySQL urge**: a suíte usa `pigea_test` com `RefreshDatabase`; os dados de dev (`pigea`) nunca são tocados.
- **Commit**: nenhum commit sem pedido explícito (regra #9 de cms_v.md).
