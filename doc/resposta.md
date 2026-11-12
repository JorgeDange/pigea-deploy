# resposta.md — Diagnóstico do Editor de Notícias (PIGEA)

**Data:** 11/11/2026 · **Pedido:** "documenta em resposta.md" + PROMPT_MASTER — Diagnóstico do Editor de Notícias (SÓ LEITURA).

## O que foi entregue

- **`pigea-deploy/diagnostico_editor.md`** — diagnóstico completo, só‑leitura, conforme a PROMPT_MASTER: 11 secções numeradas, código literal em blocos (vista Blade completa, controller completo, modelo News completo, 4 migrations, bloco `tinymce.init`), cada afirmação marcada como **CONFIRMADO / NÃO EXISTE / NÃO CONFIRMADO**, e no fim "O QUE NÃO FOI POSSÍVEL CONFIRMAR" (12 itens).
- Este `resposta.md` — apenas o registo do pedido + sumário (não duplica o diagnóstico).
- **Nada foi alterado no projecto** — zero writes em `app/`, `resources/`, `public/`, `routes/`, `database/`. Único ficheiro criado: `diagnostico_editor.md`.
- Nota honesta: na primeira montagem automática do ficheiro (via PowerShell), um bug meu de caminhos escreveu um rascunho partido em `pigea/diagnostico_editor.md` (32 KB, sem código literal). Foi **apagado por mim** imediatamente após deteção; o ficheiro final foi escrito de novo com caminhos absolutos e verificado (71 197 bytes, 1 270 linhas, 30 blocos de código cercados, 0 placeholders).

## Snapshot do achado principal

Fase 10.1 (11/11/2026) migrou o editor de **Livewire full-page → Blade tradicional + POST clássico** (o morph incremental do Livewire destruía o DOM do TinyMCE). Estado actual:

1. **TinyMCE** 7.9.3, self-host em `public/js/tinymce/` (~7,44 MB), `license_key='gpl'`, `lang=pt_PT` self-host, sem CDN/Vite.
2. **Init** (em `resources/views/admin/noticia-editor.blade.php` @push scripts): plugins `link/lists/image/autoresize/code`; toolbar `undo redo | bold italic underline | bullist numlist | link image | dropcap blockquote twocols`; `menubar:false`, `branding:false`, `height:430`; **sem** `content_css`, `style_formats`, `valid_elements`, `valid_classes`, `paste_*`, `images_upload_handler`, `automatic_uploads` — o corpo passa sem qualquer whitelist.
3. **Sem sanitização em lado nenhum**: nem antes de guardar (validação só `string`), nem na saída pública — `news/show.blade.php` imprime `{!! $post->content !!}` cru (grep `strip_tags|HTMLPurifier|ezyang` → 0). Modelo de ameaça actual = "admin confiado".
4. **BD** `news`: slug unique, title, excerpt, content longText, image (legada), status, published_at (**date**), views_count, province, date_label, tags, meta_title/meta_description (SEO), video_link/documento_link (só links), author_id, category_id, softDeletes. **Sem** `scheduled_at`/agendamento, **sem** destaque.
5. **Pré-visualização no admin** ≠ render público (dois sistemas de CSS paralelos; cláusulas `.news-preview-content` vs tema Gmag; cores/medida/fonte da letra capitular diferentes).
6. Botão de imagem do TinyMCE: **sem handler** ⇒ URL externo/HTML colado — **nunca** sobe para a Media Library (WebP, storage/); isso só acontece na capa (Spatie `capa`, singleFile, WebP) e no contexto (`contexto`, ≤10, ≤4MB).
7. Autosave: **não existe**. `wire:ignore`: **não existe** (editor não é Livewire). `@push('scripts')`: no fim da vista; layout rende a stack na linha 43 de `layouts/dashboard.blade.php`.
8. `.env` local: `DB_CONNECTION=sqlite`, `APP_ENV=local`, `APP_DEBUG=true`, `ADMIN_PATH=gestao-pigea`. Suíte: MySQL `pigea_test`.
9. Testes manuais a/b/c: **NÃO TESTÁVEL SEM BROWSER** nesta sessão (sem browser headless); o que está coberto é CI/BD (última suíte 11/11/2026: OK, 156 testes, 567 assertions).
10. Pendentes honestos para UAT/browser: sítios `.autosave` mortos no CSS; sem toast global (só flash `.status-badge`); skip link 2.4.1 inexistente no layout; divergência pública/preview; `published_at` date não agenda publicação.

## O que foi verificado vs não verificado

- **Verificado por código**: tudo o que está marcado CONFIRMADO/NÃO EXISTE no diagnóstico.
- **NÃO verificado (browser em falta)**: runtime do TinyMCE (foco, colagem, diálogo de imagem, autosubmit de sync), contraste visual real, responsividade 390px, skip-link, comportamento do iframe anunciado por screen readers. A conformidade AA do ecrã só se declara com a verificação em browser listada no `fase_all.md` (Fase 10.1).

## Regra permanente (11/11/2026): commit e push SÓ com permissão explícita

- Nunca `git commit` nem `git push` sem pedido/confirmação do utilizador, caso a caso.
- Trabalho em curso fica no working tree sem commit; alterações em `.env` ficam locais (gitignored).
- O último commit pushado para `origin/updates` foi `16dd72c` (autor JorgeDange); `phpunit.xml` (DB_PORT=3306), `.env`, e as atualizações a `fase_all.md` + `resposta.md` estão no working tree por commitar.

## FASE 11B — Recursos extras no editor de notícias (11/11/2026, noite, a pedido do cliente)

Conclui o `cms_v.md` com três pedidos adicionais:

1. **Link genérico** (novo campo `link` em notícias): migration `add_link_to_news_table` (ALTER TABLE aditivo, sem doctrine/dbal); `NewsEditorController::validar()` aceita `url:http,https`; `atributos()` passa pelo campo; UI mostra «Link (URL) — externo» após «Documento (PDF)». Sem exposição no frontend público (mesmo tratamento de `documento_link`).
2. **Capa por URL** (alternativa ao upload): no editor, toggles «Ficheiro (upload)» / «Link (URL)» (radios `capa_fonte`); se não tens o ficheiro, colas o URL direto da imagem. O Url absoluto é gravado em `news.image` — nenhum ficheiro é descarregado. Mútuo exclusivo server-side (`ValidationException` se os dois forem preenchidos). Frontend: helper global **`image_url()`** + 56 ocorrências de `asset($item->image)` em 16 views substituídas para suportar URLs http externas.
3. **Categorias de notícias hierárquicas** (select, lista, filtro): livewire NewsListTable + select do editor mostra **optgroup** com os pais (Mineração / Petróleo e Gás / Geociências) com opção «(geral)» + subcategorias filhas de `parent_id`. Categoria «Outros» criada via migration idempotente (type=news, slug=outros, order=999) — resolve a falta de captura genérica.

- **Suite**: OK (185 tests, 643 assertions). **Review prévia por `reality-checker` adiada** — URLs absolutos de imagem não verificadas em browser (o helper foi escrito exactamente para esse caso).
- **Para entrega a UAT**: passar também o `verificar_cms.md` (AA + browser) e uma checklist deste Fase 11B: capa URL real / select de categorias com optgroup / icon de validação.
- **Nada foi commitado** — `git status` continua por tua conta.

## Ambiente de testes migrado (11/11/2026, tarde)

- **MySQL Server 8.0.46** instalado como serviço `MySQL80` (utf8mb4), porta **3306** (o cliente mudou 3307→3306 durante a tarde).
- `pigea_test` criada na 3306; suíte completa **OK (156 tests / 567 assertions)** contra a instância nova (bootstrap do `tests/` correu migração+seed).
- Working tree (NÃO commitado): `phpunit.xml` com `DB_PORT=3306` (para o PR/UAT); `.env` local (gitignored) com `DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD` apenas como fallback de credenciais da suíte — **a app dev continua em sqlite** (intacta).
- Quem mandar a suíte contra esta instância precisa do cliente oficial (`C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe`), não do do XAMPP (plugin `caching_sha2_password` não carrega).
- O XAMPP/MariaDB antigo já não serve a porta 3306 (ocupada pelo MySQL80) — o processo XAMPP ainda existe mas **não escuta em nenhuma porta** (pid 17308 sem listener; `port=3308`/`port=3306` no my.ini; não é o instância usado pela suíte). Se não o quiseres a correr de todo, é contigo: `xampp-control.exe` → Stop → MySQL (não o toquei).

- A base `pigea` foi criada na mesma instância (utf8mb4), migrada+semeada para uso de desenvolvimento; a app responde 200 nas páginas públicas e 302→login no painel.
- `page_views` real (1 visita de 11/11) restaurada do sqlite; `settings` do sqlite eram placeholders vazios (nada a preservar); backup completo do sqlite em `%TEMP%\opencode\database-sqlite-backup-20261111.sqlite` (966.656 bytes).
- Nota: o dev server `artisan serve` pode manter o config antigo (sqlite) em memória — para forçar a recarga, o ambiente reinicia o processo; não o terminei.

- Suíte com conta dedicada: **OK (156 tests / 567 assertions)** — o histórico da Fase 10.1 passou a usar o utilizador `pigea`, não `root`.
- Utilizador dedicado criado para app/testes: `pigea`@localhost e `pigea`@127.0.0.1, `ALL` apenas em `pigea.*` e `pigea_test.*` (sem acesso global — `SELECT` em `mysql.user` foi verificado como **negado**). Password forte gerada localmente (48 hex) e guardada **só no `.env` local** (gitignored); `.env` atualizado para `DB_USERNAME=pigea`.
- `root/devs` fica para administração do MySQL Server 8.0.46 (criar utilizadores, grants, manutenção) — não é usado pela app nem pela suíte. XAMPP intacto por tua escolha (o PHP ainda vem de lá).

## Proposta de commit de infraestrutura (preparado, NÃO executado) — 11/11/2026

Working tree: apenas **1 ficheiro** modificado → `phpunit.xml` (+`<env name="DB_PORT" value="3306"/>`). Mais nada (verificado: `git status --short`).

- **Mensagem proposta** (estilo do repo): `phpunit.xml: correcao da porta da suite para o MySQL80 (3306)`
- **Corpo sugerido**: suíte migra para a instância MySQL 8.0.46 do sistema (antes: XAMPP via mysql 3307); a credencial da suíte continua a vir de `.env` local (fallback) — o ficheiro commitável **não contém password**.
- **Não entra:** `.env` (gitignored, tem credenciais locais); nada mais do working tree.
- **Nota:** `fase_all.md` e `resposta.md` (docs desta sessão) estão **fora** do repositório `pigea-deploy` (vivem em `pigea/`); não entram em commits deste repo. Se quiseres versionar também a pasta-raiz do dia-a-dia (com docs/decisões), isso é uma decisão separada e requer o teu OK.
- **Sem commit/push até autorização explícita** (regra de 11/11/2026).

## Ficheiros afectados por este pedido

| Ficheiro | Acção |
|---|---|
| `pigea-deploy/diagnostico_editor.md` | **criado** (entrega pedida — único ficheiro novo no projecto) |
| `resposta.md` (esta) | criado (registo pedido pelo "documenta em resposta.md") |
| `pigea/diagnostico_editor.md` | criado por engano meu e **apagado** pelo próprio autor do erro |
| `phpunit.xml` | modificado: `<env name="DB_PORT" value="3306"/>` — working tree, **não commitado** (para o teu PR/UAT) |
| `fase_all.md` | atualizado: Fase 10.1 + secção «Ambiente de testes migrado para MySQL 8.0.46» |
| `.env` (local, gitignored) | sqlite→mysql (DB_CONNECTION), mantendo DB_HOST/PORT/CREDS fallback; password nunca versionada |
| `.env.backup-3306-*` | **não criado** — backup do sqlite está em `%TEMP%\opencode\database-sqlite-backup-20261111.sqlite` |

*Sobre este push/commit: nada foi commitado sem pedido explícito; `diagnostico_editor.md` **foi** commitado e pushado para `updates` (16dd72c, autor JorgeDange). O MySQL **MySQL80** (3306) deve permanecer em execução; o XAMPP MySQL (processo 17308, sem listener) pode ser parado por ti no `xampp-control.exe`.*
