# PROMPT_MASTER — Melhoria do Editor de Notícias e do CMS PIGEA

## 1. Contexto

Projecto: PIGEA (Laravel 12, Tailwind 4, Vite, Spatie Permission, Spatie Media Library).
O editor de notícias é uma página **Blade tradicional com POST clássico** (não é Livewire). Usa TinyMCE 7.9.3 self-hosted em `public/js/tinymce/`, com `license_key: 'gpl'`.

O objectivo é que o artigo tenha **o mesmo aspecto no editor, na pré-visualização e no site público**, que o conteúdo guardado seja **seguro**, e que o painel deixe de parecer amador.

### Problemas confirmados no diagnóstico (a corrigir)
1. O TinyMCE não tem `content_css`, `style_formats`, `valid_classes`, `paste_*` nem `images_upload_handler`.
2. A barra do editor é mínima (sem blocos H2/H3, alinhamento, tabela, limpar formatação, código-fonte, ecrã inteiro).
3. Não há sanitização do HTML: o site imprime `{!! $post->content !!}` cru.
4. O botão de imagem do editor não faz upload (aceita URL externo) e nunca passa pela conversão WebP.
5. A pré-visualização do admin (`.news-preview-content`) e o site público (tema Gmag) usam CSS diferentes. A letra capitular, a medida e as cores divergem.
6. `published_at` é do tipo `date`, por isso não permite agendar.
7. Interface do painel com aspecto amador (ver Fase 5).

## 2. Regras gerais (obrigatórias)

1. **Documentar antes de implementar.** Começar por um plano curto em `plano_cms.md`, listar os ficheiros que vão ser tocados e só depois alterar código.
2. **JavaScript puro (vanilla).** Sem frameworks novos no painel. Sem Livewire no editor.
3. **Sem novas cores hexadecimais.** Usar apenas as variáveis de `color.css` e `dashboard.css`. Podem-se criar **novas classes de componentes**, mas só com as variáveis existentes.
4. **Todo o texto visível ao utilizador em português (Angola/Portugal).** Incluindo os campos de ficheiro (sem "Choose File"), mensagens de erro e o TinyMCE (`pt_PT` já instalado).
5. **Usar os nomes reais do projecto.** Antes de escrever CSS ou listas brancas, ler o CSS público do artigo (`news/show.blade.php`, tema Gmag, `css/`) e o `dashboard.css`. Não inventar nomes de classes que contradigam os existentes. Onde a classe ainda não existir, criá-la e documentar.
6. **Não alterar o que não está neste prompt** (menu público, directório, banners, comentários, dashboard, etc.).
7. **Backup do banco antes de qualquer migration.** As migrations não podem apagar dados existentes.
8. **Desenvolver e testar em MySQL** (o MySQL Server foi instalado como serviço no PC). Não usar SQLite. Confirmar que o `.env` aponta para `mysql` e que as consultas do dashboard (visitas, contadores) funcionam no MySQL.
9. **Não fazer commit nem push** sem pedido explícito.
10. Cada fase termina com: `php artisan test` verde, `php -l` nos ficheiros PHP alterados e uma entrada nova em `modificar.md` (nova secção numerada) e em `memoria.md`.
11. O que não puder ser confirmado sem browser deve ser marcado **"NÃO TESTÁVEL SEM BROWSER"** numa lista de verificação manual para o Jorge. Não declarar sucesso sem prova.

## 3. Fases

### FASE 1 — Folha de estilos única do corpo do artigo (base de tudo)

Criar **um único** ficheiro CSS do corpo do artigo (ex.: `public/css/article.css` ou via Vite) com o escopo `.article-body`.

Este ficheiro é carregado por **três** sítios:
- o site público (`news/show.blade.php`);
- a pré-visualização do admin;
- o TinyMCE (`content_css`).

Definir nele, de forma fiel ao layout do portal:

| Elemento | Classe / regra |
|---|---|
| Parágrafo normal | `.article-body p` |
| Subtítulo | `.article-body h2`, `.article-body h3` (fonte dos títulos, ex.: Playfair Display) |
| Letra capitular | `.article-body p.drop-cap::first-letter` — letra grande, vermelha (cor da marca), fonte serifada, em bloco flutuante à esquerda |
| Citação em destaque | `.article-body blockquote.pull-quote` — faixa vermelha à esquerda, fundo cinzento claro, texto centrado e maior, ícone de aspas |
| Texto em coluna estreita | `.article-body .narrow` |
| Duas colunas | `.article-body .two-cols` (colapsa para 1 coluna no telemóvel) |
| Imagem com texto ao lado | `.article-body figure.media-left` e `.media-right` (flutuante, largura limitada, `figcaption` para legenda/crédito) |
| Imagens | `.article-body img` (`max-width:100%`, `height:auto`) |
| Tabelas | `.article-body table` (simples e legível) |
| Listas, links, `figcaption` | estilos consistentes |

Tarefas:
1. Mapear o que existe hoje no CSS do tema e do `.news-preview-content` e **consolidar** em `.article-body`.
2. Remover a duplicação: o site público e a pré-visualização passam a usar **o mesmo parcial Blade** (ex.: `news/partials/article-body.blade.php`) com `<div class="article-body">…</div>`.
3. Garantir que, após a migração, o site público continua igual ao que está hoje (sem regressões visuais).

Critério de aceitação: a mesma notícia, vista no editor, na pré-visualização e no site, tem a mesma letra capitular, a mesma citação, as mesmas colunas e a mesma tipografia.

### FASE 2 — Configuração completa do TinyMCE

Reescrever o `tinymce.init` em `resources/views/admin/noticia-editor.blade.php` (ou, melhor, num ficheiro JS próprio, ex.: `public/js/admin/news-editor.js`).

Requisitos:

1. **Fidelidade:**
   - `content_css` apontando para o CSS da Fase 1;
   - `body_class: 'article-body'`;
   - `content_style` mínimo (só o que for estritamente necessário para o iframe).
2. **Plugins:** `advlist autolink lists link image table code fullscreen wordcount searchreplace autoresize` (e `visualblocks` se útil).
3. **Menu superior reduzido:** `menubar: 'edit view insert format table'`.
4. **Barra de ferramentas** (agrupada):
   - `undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent`
   - `link image table | pigeastyles | removeformat | code fullscreen`
5. **Blocos (`block_formats`):** apenas Parágrafo, Título 2 (H2), Título 3 (H3). Sem H1 (o título da notícia é o H1 da página).
6. **Menu "Estilos do PIGEA" (`style_formats`)**, em vez de botões soltos:
   - Letra capitular → `p` com classe `drop-cap`
   - Citação em destaque → `blockquote` com classe `pull-quote`
   - Coluna estreita → classe `narrow`
   - Duas colunas → `div` com classe `two-cols`
   - Imagem à esquerda / à direita → classes `media-left` / `media-right` na `figure`
   Os botões antigos `dropcap`, `blockquote` e `twocols` devem ser removidos e substituídos por este menu, mantendo a mesma saída HTML sempre que possível (para não quebrar notícias já guardadas).
7. **Classes permitidas:** `valid_classes` e `extended_valid_elements` alinhados **exactamente** com a lista branca do servidor (Fase 3). Criar uma única fonte de verdade (ex.: `config/article.php` com a lista de classes e elementos permitidos), usada pelo TinyMCE e pelo purificador.
8. **Colagem (`paste_*`):** ao colar do Word ou de sites, manter apenas negrito, itálico, listas, links, títulos e tabelas; descartar estilos inline, fontes, cores e classes alheias. `paste_data_images: false`.
9. **Imagens:** `image_description: true` (texto alternativo), `image_caption: true`, `image_advtab: false`, `automatic_uploads: true`, `images_upload_handler` (ver Fase 4).
10. **Aparência:** `height` mínima confortável (≥ 520), `autoresize` com máximo razoável, `branding: false`, `promotion: false`, `statusbar` com contador de palavras (a barra com o caminho `p` pode ficar escondida via `elementpath: false`), `language: 'pt_PT'`.
11. Garantir que o conteúdo é **sincronizado antes do submit** (`tinymce.triggerSave()` ou equivalente) e que, ao falhar a validação, o corpo volta repreenchido com `old('content')`.

Critério de aceitação: as classes dos Estilos do PIGEA sobrevivem a guardar → reabrir → publicar. A colagem de um texto do Word não traz `style=` nem `<span>` inúteis.

### FASE 3 — Sanitização segura do HTML

1. Instalar um purificador de HTML (ex.: `mews/purifier` ou `ezyang/htmlpurifier`) e configurar um perfil `article`.
2. Sanitizar **ao guardar** (no controller de notícias, FormRequest ou mutator do modelo `News`), nunca só na saída. Guardar o HTML já limpo.
3. Lista branca vinda do `config/article.php` (Fase 2):
   - Elementos: `p, h2, h3, strong, em, u, s, ul, ol, li, a[href|target|rel], blockquote, figure, figcaption, img[src|alt|width|height|loading], table, thead, tbody, tr, th, td, br, div, span (só se necessário)`.
   - Atributos: sem `style`, sem `on*`, sem `script`, sem `iframe`.
   - Classes: apenas `drop-cap, pull-quote, narrow, two-cols, media-left, media-right` (e quaisquer outras definidas na Fase 1).
   - Links: apenas `http`, `https`, `mailto`; adicionar `rel="noopener noreferrer"` em `target="_blank"`.
   - `img[src]`: apenas URLs do próprio site (`/storage/...`) ou HTTPS.
4. Migração dos artigos já existentes: criar um comando Artisan (ex.: `articles:sanitize`) que reprocessa os conteúdos antigos, **com modo `--dry-run`** que só reporta o que mudaria. Não executar sem confirmação do Jorge.
5. Na saída pública mantém-se `{!! !!}` mas só sobre conteúdo já sanitizado.
6. Testes automáticos: um `<script>`, um `onerror=`, um `style=`, uma classe desconhecida e um `javascript:` em link devem ser removidos; as classes do PIGEA e o `alt` devem ser mantidos.

### FASE 4 — Upload de imagens dentro do corpo do artigo

1. Criar uma rota autenticada, protegida por `ADMIN_PATH` e por permissão (admin/editor), tipo `POST {ADMIN_PATH}/noticias/imagem`.
2. Validar: tipo real do ficheiro (`mimetypes`), `jpg, jpeg, png, webp`, máximo 4 MB, dimensões razoáveis.
3. Guardar em `storage/app/public/noticias/corpo/AAAA/MM/`, redimensionar para **largura máxima 1200 px** (recomendado 800 px) e converter para **WebP** (GD já está activo).
4. Devolver JSON `{ "location": "<url>" }` para o TinyMCE.
5. **Não usar o Spatie Media Library aqui**, porque a notícia pode ainda não existir (já houve um erro de `media` sem `id`). A capa e as imagens de contexto continuam a usar o Spatie como hoje.
6. Texto alternativo obrigatório no diálogo de imagem; mostrar aviso se estiver vazio.
7. Texto de ajuda sob o botão: *"Recomendado: 800px de largura. Formatos: JPG, PNG, WEBP. Máx: 4MB."*
8. Limitar tentativas de upload (rate limit) e devolver erros em português.

### FASE 5 — Aspecto e usabilidade do painel

Usar só as variáveis e classes existentes (`.card`, `.btn-primary`, `.btn-ghost`, `.status-badge`, etc.) e criar as classes em falta com as variáveis já definidas.

1. **Hierarquia dos botões** (cartão de acções):
   - **Publicar** → botão principal sólido (`.btn-primary` ou equivalente de sucesso);
   - **Guardar rascunho** → secundário;
   - **Pré-visualizar** → contorno/discreto (`.btn-ghost`).
   Os três com o mesmo tamanho, o mesmo raio e o mesmo alinhamento de ícone.
2. **Barra de acções fixa** (sticky) na coluna direita, para o botão Publicar nunca sair do ecrã.
3. **Breadcrumb numa única linha** (`Admin › Notícias › Nova notícia`), em todo o painel.
4. **Título grande**, na fonte dos títulos (Playfair Display), com aspecto de "título do artigo", e contador de caracteres no título e no subtítulo.
5. **Campos de ficheiro em português**, sem os botões nativos em inglês: componente de upload com área de largar, botão "Escolher ficheiro", **pré-visualização da imagem**, botão **Remover**, nome e tamanho do ficheiro, e erro claro (tipo/tamanho). Vanilla JS. Manter os textos de dimensões já existentes.
6. **Cartões da coluna direita** com títulos consistentes e espaçamento regular.
7. **Componentes em falta:**
   - toast/aviso global (sucesso, erro) substituindo o flash simples;
   - modal de confirmação ao apagar;
   - estados vazios;
   - indicador de carregamento nos botões ao submeter (evitar duplo clique).
8. **Detalhes:**
   - remover o logout duplicado (manter só no topbar, ou só na sidebar) e corrigir o contraste do "Sair";
   - mostrar nome e **papel** do utilizador (Admin / Editor) no topbar, em vez de "Test User";
   - alinhar o badge do sino;
   - adicionar *skip link* ("Saltar para o conteúdo") ao layout.
9. Manter as correcções de acessibilidade já feitas (foco visível, contraste AA, `aria-*`).

### FASE 6 — Campos em falta e fluxo editorial

**Campos no formulário** (a tabela `news` já tem `excerpt`, `tags`, `meta_title`, `meta_description`):
- Slug (gerado do título, editável, com verificação de unicidade);
- Resumo (`excerpt`, com contador; usado na lista e no SEO);
- Etiquetas (`tags`);
- Painel "SEO" recolhível: meta título e meta descrição com contadores (≈ 60 e 160 caracteres);
- Texto alternativo e crédito/legenda da imagem de capa.

**Estados da notícia:** `rascunho`, `em_revisao`, `publicado` (e `agendado`, ver Fase 8).
- **Editor:** pode guardar rascunho e **submeter para revisão**.
- **Admin:** pode publicar, devolver a rascunho e despublicar.
- Se esta separação de permissões for demasiado restritiva, deve ficar **numa única constante/config** fácil de alterar (ex.: `config('cms.editor_pode_publicar')`).
- Mostrar o estado actual no editor (badge) e na lista.

### FASE 7 — Lista de notícias

1. Tabela com: título, categoria, autor, estado (badge), data, visitas e acções.
2. Pesquisa por título e filtros por **estado, categoria e autor**; ordenação; paginação.
3. Acções: editar, **duplicar**, pré-visualizar, apagar (com modal de confirmação) e **restaurar** (SoftDeletes já existe).
4. Badges de estado com cores consistentes (rascunho neutro, revisão âmbar, publicado sucesso).
5. Estado vazio quando não houver resultados.

### FASE 8 — Opcionais (só implementar se as Fases 1–7 estiverem verdes)

**8.1 Autosave** (sem Livewire):
- `fetch` com debounce (ex.: 15 s) para uma rota que grava como rascunho;
- indicador "A guardar…" / "Guardado há X min";
- aviso ao sair da página com alterações por guardar (`beforeunload`);
- nunca publicar via autosave; tratar 419/401 (sessão expirada) com mensagem clara.

**8.2 Agendamento:**
- migration segura: converter `published_at` de `date` para `datetime` **sem perder dados**, ou criar `scheduled_at`;
- campo "Publicar em" (data e hora) no editor;
- comando agendado (`schedule`) que publica as notícias `agendado` quando chegar a hora; documentar o cron/Task Scheduler necessário.

## 4. Testes obrigatórios

Automáticos (PHPUnit/Pest):
- sanitização (Fase 3): remoção de `script`, `on*`, `style`, `javascript:`, classes desconhecidas; manutenção das classes do PIGEA e do `alt`;
- upload de imagem do corpo (Fase 4): tipo inválido, tamanho excedido, utilizador sem permissão (403), sucesso com WebP;
- permissões por papel (editor/admin) nos estados (Fase 6);
- slug único, validação dos campos novos;
- (se feito) autosave e agendamento.

Lista de verificação manual no browser (marcar como "NÃO TESTÁVEL SEM BROWSER" quando a IA não puder executar) — entregar em `verificar_cms.md` para o Jorge:
1. Escrever texto, usar cada Estilo do PIGEA, guardar, reabrir: as classes mantêm-se.
2. Colar texto do Word: sem lixo de estilos.
3. Inserir imagem pelo editor: sobe, converte para WebP e aparece no artigo.
4. Publicar com título vazio: erro visível e o corpo do editor mantém-se.
5. Comparar a mesma notícia no editor, na pré-visualização e no site: aspecto igual.
6. Largura 390 px: editor, painel e artigo públicos legíveis.
7. Navegar só com teclado: foco visível, skip link funciona.

## 5. Entrega

1. `plano_cms.md` (feito antes de implementar) e `verificar_cms.md` (lista manual).
2. Nova secção em `modificar.md` por fase e `memoria.md` actualizado.
3. Resumo final com: o que foi feito por fase, ficheiros alterados, resultado dos testes, e uma secção **"O QUE NÃO FOI POSSÍVEL CONFIRMAR"**.
4. Reportar honestamente qualquer decisão que tenha desviado deste prompt e porquê.