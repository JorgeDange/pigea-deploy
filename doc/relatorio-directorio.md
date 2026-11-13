# Relatório — Directório PIGEA (`/directorio`)

**Data:** 2026-11-12 · **Âmbito:** redesign mínimo e cirúrgico da área pública do Directório · **Estado:** working tree (nada commitado)

---

## (a) O que mudou e porquê

### Problema reportado pelo cliente
> «melhorar a area de diretorio, esta mal e desorganizado» · «os documentos como as empresas não tem icone» · «esta uma confusão» · «implementa a melhor forma de organizar» · «seguindo o design do site, sem inventar»

### Antes (diagnóstico visual)
- Cards de empresas e documentos sem identidade visual: texto plano sobre branco, sem ícone, sem avatar, sem hierarquia — impossível distinguir empresa de documento em < 2 s.
- Títulos centrados, sem alinhamento à esquerda, altura variável por cartão.
- Chips de áreas de atuação com fundo `#f4f4f8` ad-hoc, inconsistentes com o tema.
- Abas `Empresas | Artigos/Documentos` feitas como botões soltos com as classes `small-btn` / `color2-bg` — **`small-btn` e `color2-bg` não existem em nenhum CSS carregado no site público** (a aba inativa saía `#292929` por fallback de `.btn`; a ativa ficava vermelha por `.color-bg`).
- Paginação usava `.dashboard-pagination` / `.filter-search` de `dashboard.css` — **esse ficheiro só é carregado nos layouts `admin` e `auth`; a página pública nunca o carrega** → o número da página aparecia minúsculo e sem estado ativo visível.
- Sidebar: os dois `.banner-widget` ("Registe a sua empresa" / "Profissional independente") estavam sem `.bg-wrap`/`data-bg` → o `<h5>` branco do tema ficava branco sobre branco: **os títulos estavam invisíveis** (verificado no screenshot *antes*).

### Depois (o que foi feito)
**`resources/views/livewire/frontend/directory-listing.blade.php`** — reescrito cirurgicamente:

1. **Abas** passam ao padrão de abas nativo do tema — `.tabs-widget` + `.tabs-menu` + `<li class="current">` (idêntico ao usado em Notícias/Legislação/Eventos/Projetos), com ícone (`fa-building` / `fa-file-alt`) e rótulo. `wire:click.prevent` evita salto de `#`.
2. **Pesquisa** agora em linha cheia, abaixo das abas, com altura/borda/raio alinhados ao design system (border `#E0E0E0`, foco vermelho `#C8102E` com anel suave), `aria-label` completo.
3. **Card de empresa**:
   - Avatar-badge quadrado (58×58, raio 6px) **vermelho PIGEA `#C8102E`** com **iniciais** da empresa (2 letras significativas, ignorando sufixos legais: Lda/SA/e/da…), sombra ténue e tipografia Playfair Display — eco do `.author-img` (80×80, raio 4px, sombra) do tema.
   - Nome em destaque (h4, 15px, alinhado à esquerda), abaixo micro-rótulo «✓ EMPRESA APROVADA» (`fa-check-circle` dourado `#F2A900`) — diferencia imediatamente empresa de documento.
   - Telefone com ícone `fa-phone-alt` vermelho; redes sociais via `.footer-social` (a linha canónica do site, fundo `#C8102E` da `color.css`), com `aria-label` por rede.
   - **NIF continua deliberadamente ausente do público** (restrutura.md §6 — comentário preservado).
4. **Card de documento**:
   - Ícone **por tipo** numa caixa **escura `#1A1A1A` com glifo dourado `#F2A900`** (distingue-se ao primeiro relance da empresa vermelha): `lei` → `fa-gavel` · `artigo` → `fa-newspaper` · `documento` → `fa-file-contract` · `default` → `fa-file-alt`. Todos verificados como presentes no FA 5 Pro 5.12.0 (plugins.css).
   - Etiqueta dourada (`#F2A900`, uppercase) com o tipo (`Artigo` / `Lei` / `Documento`) via `tipo_label` existente.
5. **Chips de áreas de atuação** (empresas e documentos) passam a usar o **`.post-category-marker`** existente no tema (o mesmo chip vermelho das listas de notícias do site — regra `.list-post-content .post-category-marker { background:#C8102E }` da `color.css`, espelhada de forma escopada, ver CSS abaixo). Fim dos chips cinzentos ad-hoc e do ruído visual.
6. **Estrutura do card**: `.box-widget-content` existente + `.author-widget` (classe já usada no ficheiro), `display:flex`, conteúdo em coluna flex com `margin-top:auto` na linha de rodapé → **alturas alinhadas por linha** e separador `1px solid #F0F0F0` no rodapé. Borda + sombra suaves equivalentes a `.post-widget-item`/`.list-post-wrap` (inline, padrão já usado no ficheiro — nenhuma classe nova inventada).
7. **Estado vazio** passa a `.empty-state` + `.empty-state-icon` + `.empty-state-title` + `.empty-state-text` (padrão do admin `news-list-table`, **classe pré-existente em `dashboard.css`, não uma classe nova**). Como o layout público não carrega `dashboard.css`, as regras foram replicadas **escopadas** em `style.css` (ver abaixo).
8. **Paginação** passa ao padrão nativo do tema — `.pagination` com `current-page` (vermelho via `color.css`), `prevposts-link`/`nextposts-link` + `fa-caret-left/right` (idêntico a `news-filter.blade.php` e às outras páginas públicas). `aria-current="page"` e `aria-label` por item. Números no formato do tema (`01.`, `02.`…). Em vez do `»` solto anterior.

**`resources/views/directory/index.blade.php`**:
9. Sidebar — os dois `.banner-widget` ganham `.bg-wrap` + `.bg data-bg` (`images/bg/6.jpg` e `images/bg/7.jpg`, o mesmo padrão de `contacts.blade.php` e `legislation/index.blade.php`) → o `<h5>` branco do tema volta a ser legível. Estrutura intocada (col-md-4 + col-md-8, secções, `pr-subtitle prs_big`, CTAs) declarada como estável.

**`public/css/style.css`** — apenas um bloco appended, **zero classes novas**, todos os seletores escopados a `.directorio-listing` (classe pré-existente no wrapper do componente):

```css
.directorio-listing .post-category-marker { background:#C8102E; color:#FFFFFF; display:inline-block; margin:0 6px 6px 0; }
.directorio-listing .filter-search:focus  { border-color:#C8102E; outline:none; box-shadow:0 0 0 3px rgba(200,16,46,.12); }
.directorio-listing .empty-state / .empty-state-icon / .empty-state-title / .empty-state-text { … } /* espelho do bloco idêntico de dashboard.css */
```

### O que NÃO foi tocado (por contrato)
Lógica Livewire/`DirectoryListing.php`, models, routes, migrations, paginação (8/página), filtros, NIF oculto, seed/DB — nada disto foi alterado. Nenhum atributo `aria-*` foi removido (só adicionados). Nenhuma classe CSS nova foi criada; as únicas edições a CSS são o bloco acima.

---

## (b) Screenshots antes/depois

| Ficheiro | Conteúdo |
|---|---|
| `relatorio-directorio-antes.png` | Estado anterior (abas soltas, sem ícones, sidebar com h5 invisível, paginação minúscula) |
| `relatorio-directorio-depois-empresas.png` | Aba **Empresas** — avatares com iniciais, «Empresa aprovada», chips vermelhos, grelha alinhada |
| `relatorio-directorio-depois-documentos.png` | Aba **Artigos/Documentos** (`/directorio?aba=documentos`) — caixas escuras com glifos dourados (gavel/jornal/contrato), tag dourada por tipo, botões Download |
| `relatorio-directorio-depois-mobile.png` | 480×2400 — cartões empilhados, sem overflow |

Validação de execução: `http://127.0.0.1:8000/directorio` → HTTP 200 (verificado antes e depois).

**Legibilidade empresa vs. documento em < 2 s:** empresa = avatar **vermelho** + «EMPRESA APROVADA»; documento = ícone **preto/dourado** + tag dourada de tipo. Cumprido.

---

## (c) Riscos e validação

**Riscos / observações**
1. **Dados de demonstração no público:** a BD local contém registos com `status = 'aprovado'` mas nomes como «GeoLab Laboratório Analítico (aguardando aprovação)» e «MineraSpec Fictícia (exemplo rejeitado)» — herança do seed, visível no público porque o scope `aprovado` só filtra por `status`. **Não foi alterado** (regra: não tocar na lógica). Recomenda-se à equipa: limpar/renomear o seed antes do lançamento.
2. `.color2-bg` (botão «Registar como Profissional» na sidebar) **não está definido em nenhum CSS do site público** — preexistente, fora do âmbito; fica igual ao antes (`.btn` escuro `#292929`). Se quiser, passo rápido: trocar por `color-bg` ou definir a classe no `style.css`.
3. Redes sociais sem URL válida por rede desconhecida caem no fallback `facebook-f` (comportamento anterior preservado; só o invólucro visual mudou).
4. Alturas de cartão por linha são consistentes via flex, mas linhas diferentes podem ter alturas distintas (grid float clássico do tema) — comportamento aceite no resto do site (listagens de notícias, etc.).
5. O bloco CSS replicado de `.empty-state` em `style.css` duplica o de `dashboard.css` de forma intencional (o layout público não carrega o dashboard); qualquer alteração futura ao `.empty-state` deve ser feita nos dois sítios (comentado no ficheiro).
6. Execução headless (`--screenshot`) valida layout estático; interatividade Livewire (troca de aba por clique real) foi validada via `?aba=documentos` (a aba é `#[Url]`), não via automação de clique.
7. Working tree partilhado com outras frentes (BannerFormController, MenuSeeder, dashboard.css, etc. já modificados antes desta tarefa) — nenhum desses ficheiros foi mexido; `git diff` dos meus ficheiros confere apenas o descrito.
8. `style.css` já estava modificado por outra frente; a minha adição está isolada no fim do ficheiro e íntegra (UTF-8 válido, sem BOM; o ficheiro original passou também a validação `UTF8Encoding(strict)`).

**Validação efetuada**
- `php vendor/bin/phpunit` → **OK (204 tests, 694 assertions)** (restantes testes verdes).
- `php artisan view:cache` → **Blade templates cached successfully** (sem erros).
- HTTP 200 em `/directorio` antes e depois.
- Screenshots Chrome headless: desktop 1440px (empresas / documentos) e mobile 480px — sem console visível de quebra de layout, sem overflow horizontal; tabs com estado ativo legível; paginação `.pagination` funcional (Página 1 ativa, vermelha).
- NIF: ausente do markup renderizado (confirmado no blade e no screenshot).
- Acessibilidade: ícones decorativos `aria-hidden`, `aria-label` por rede social e por botão de paginação, `aria-current="page"`, input de filtro com `aria-label`, chips como texto puro (não-focáveis).

---

## (d) Lista de testes corridos

| # | Comando / verificação | Resultado |
|---|---|---|
| 1 | `C:\xampp\php\php.exe vendor\bin\phpunit` | **OK (204 tests, 694 assertions)** — 36,9 s |
| 2 | `C:\xampp\php\php.exe artisan view:cache` | **OK** — «Blade templates cached successfully» |
| 3 | `GET /directorio` (HTTP, antes e depois) | **200** |
| 4 | Screenshot headless 1440×2350 empresas | render OK |
| 5 | Screenshot headless 1440×2350 `?aba=documentos` | render OK (aba e tipo tags/ícones corretos) |
| 6 | Screenshot headless 480×2400 | render OK, sem overflow |
| 7 | Verificação FA 5 Pro 5.12.0: `fa-building`, `fa-gavel`, `fa-newspaper`, `fa-file-contract`, `fa-file-alt`, `fa-download`, `fa-caret-left/right`, `fa-phone-alt`, `fa-check-circle` | **todos presentes** em `public/css/plugins.css` |
| 8 | `git diff --stat` dos ficheiros da tarefa | 3 ficheiros (index.blade.php, directory-listing.blade.php, style.css) — nada mais |
| 9 | Encoding dos ficheiros editados | UTF-8 sem BOM, PT-PT válido (acentos íntegros) |
| 10 | NIF ausente do output público | confirmado |

---

**Ficheiros editados**
- `resources/views/directory/index.blade.php` (bg dos banners da sidebar)
- `resources/views/livewire/frontend/directory-listing.blade.php` (redesign dos cards/abas/paginação/estado vazio)
- `public/css/style.css` (bloco final escopado `.directorio-listing` — espelho de classes pré-existentes)
- `relatorio-directorio.md` + 4 screenshots (novo, raiz do `pigea-deploy`)

**Não commitado** — conforme instrução, tudo fica no working tree.
