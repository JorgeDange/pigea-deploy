# PIGEA — Análise do Painel Administrativo
## Melhoramentos técnicos, desempenho e linguagem acessível

**Data:** 12 de Novembro de 2026
**Âmbito:** Painel de gestão (`/gestao-pigea`) — dashboard, tabelas, formulários e editor de notícias
**Método:** análise de código + medições reais no navegador (Chrome DevTools Protocol, servidor de desenvolvimento local, sessão autenticada `test@example.com`)

---

## 1. Resumo executivo

O painel funciona bem ao nível da base de dados: as listas carregam rápido (o servidor responde em **~0,3 segundos** em todas as páginas), as consultas estão bem construídas e as permissões estão corretas.

Os problemas encontrados são de **outra natureza**:

| # | Problema | Impacto para o utilizador |
|---|----------|---------------------------|
| 1 | **Cada página descarrega ~1 MB de ficheiros desnecessários** — inclui bibliotecas do site público que o painel **nunca usa** (315 KB de plugins JS + 150 KB de CSS de plugins + 120 KB de CSS do tema) | Navegação lenta, sobretudo em internet fraca (o caso comum em Angola); "peso morto" em cada clique |
| 2 | **Linguagem técnica** em formulários que o editor usa todos os dias ("Upload bidirecional", "Slug (URL)", "SEO — meta title", "Tags", "Meta description padrão", "Link de Download") | Utilizador final não sabe o que preencher; receio de "partir" qualquer coisa |
| 3 | **Erros e lacunas de apresentação**: etiqueta "Rodape" sem acento, um fecho de HTML errado na tabela de banners, e paginação que mostra **todas** as páginas (1,2,3…50) em vez de uma janela | Visual descuidado; tabelas ficam confusas quando há muitos registos |

**O que NÃO tem problema:** velocidade do servidor (TTFB ~290 ms ✓), consultas à base de dados (sem consultas em excesso nas tabelas ✓), editor de notícias (já usa linguagem simples ✓), menu lateral e filtros (claros ✓).

---

## 2. Como medimos (evidência)

Script automatizado (`cdp-admin-perf.mjs`) que faz login real e navega por 7 páginas do painel, medindo tempo e volume transferido. **Nota importante:** o servidor de desenvolvimento (`php artisan serve`) não envia cabeçalhos de cache — em produção (Apache/nginx) os ficheiros ficam em cache no browser, mas **o peso inicial de ~1 MB por página continua**, e é esse o problema real a corrigir.

### 2.1 Medição por página (local, melhor caso)

| Página | Servidor (TTFB) | Página pronta | Transferido | 3 maiores ficheiros |
|---|---|---|---|---|
| Dashboard | 307 ms | 498 ms | **1 214 KB** | plugins.js 316 · chart.umd.js 201 · plugins.css 150 |
| Notícias (lista) | 295 ms | 465 ms | **1 013 KB** | plugins.js 316 · plugins.css 150 · fonte de ícones 135 |
| Editor de notícia | 290 ms | 847 ms | **2 366 KB** | tinymce 436 · tinymce-theme 431 · plugins.js 316 |
| Banners | 283 ms | 446 ms | **1 613 KB** | imagem demo 371 · plugins.js 316 · plugins.css 150 |
| Empresas | 286 ms | 420 ms | **1 013 KB** | plugins.js 316 · plugins.css 150 · fonte de ícones 135 |
| Documentos | 267 ms | 388 ms | **1 013 KB** | plugins.js 316 · plugins.css 150 · fonte de ícones 135 |
| Utilizadores | 291 ms | — | **~1 013 KB** | plugins.js 316 · plugins.css 150 · fonte de ícones 135 |

### 2.2 Análise dos ficheiros carregados

O layout do painel (`layouts/dashboard.blade.php`) carrega:

```
CSS:  plugins.css (150 KB) · style.css (120 KB) · color.css (4 KB) ·
      dashboard.css (46 KB) · fonts.css (13 KB) · article.css (5 KB)
JS:   jquery.min.js (87 KB) · plugins.js (315 KB) · dashboard.js (9 KB) ·
      banner.js (12 KB) · [chart.umd.js 201 KB apenas no dashboard]
```

Verificações feitas, uma a uma:

- **`plugins.js` (315 KB)** — contém Swiper (slides do site público), lightGallery e outras bibliotecas. Os três únicos scripts próprios do painel (`dashboard.js`, `banner.js`, `news-editor.js`) **não usam nenhum delas** (verificado por pesquisa de "Swiper", "lightGallery", "Chart" nos ficheiros). O iniciador do tema (`scripts.js`) nem sequer é carregado no admin — ou seja, **o plugins.js é peso morto a 100%**.
- **`plugins.css` (150 KB)** — quase tudo é a biblioteca de ícones (FontAwesome) + estilos de galerias do site público. O painel só precisa de: os ícones que usa + a classe `.sr-only` (acessibilidade). Tudo o resto é desperdício.
- **`style.css` (120 KB)** + **`color.css` (4 KB)** — estilos do site público. O `dashboard.css` (46 KB) já define **tudo o que o admin usa** (`.card`, `.btn-primary`, `.status-badge`, `.dashboard-table`, `.stats-grid`, `.dashboard-form`, tabelas, modais, toasts, sidebar…) e é **autossuficiente** (não depende de variáveis do tema).
- **`chart.umd.js` (201 KB)** — usado **só no dashboard** (já está corretamente isolado; opcionalmente pode passar a carregar em segundo plano).
- **`jquery.min.js` (87 KB)** — **necessário**: os scripts do painel usam-no.
- **TinyMCE (436 + 431 KB)** — o editor de texto rico; carrega **só no editor de notícias**. Mantém-se (é a página única pesada, aceitável).
- **Fonte de ícones (135 KB woff2)** — descarrega-se quando há ícones no ecrã; mantém-se (o subconjunto de CSS de ícones não muda os ficheiros de fonte).

---

## 3. Inventário de linguagem técnica (o que confunde o utilizador final)

Com ficheiro e linha, o estado atual e a sugestão de reescrita:

| Ficheiro | Hoje | Proposta |
|---|---|---|
| `admin/banner-form.blade.php:84` | *"Upload bidirecional: escolhe o ficheiro para carregar já ou cola um URL direto."* | *"Coloque a imagem: carregue um ficheiro do seu computador ou cole o endereço (link) de uma imagem já publicada na internet."* |
| `admin/banner-form.blade.php:52` e `destaque-form.blade.php:52` | *"Link externo (URL)"* | *"Colar endereço de imagem (link)"* |
| `admin/banner-form.blade.php:67` e `destaque-form.blade.php:66` | *"Ficheiro atual carregado"* | *"Imagem atual: (nome do ficheiro)"* |
| `admin/destaque-form.blade.php:82` | *"O widget da página inicial/projeto roda os destaques de 4 em 4 segundos."* | *"A caixa de destaques da página inicial e dos projetos troca a imagem a cada 4 segundos."* |
| `admin/configuracoes.blade.php:153` | Cartão *"SEO"* | *"Aparência nas pesquisas (Google)"* |
| `admin/configuracoes.blade.php:160` | *"Meta title padrão"* | *"Título que aparece nas pesquisas"* |
| `admin/configuracoes.blade.php:165` | *"Meta description padrão"* | *"Descrição que aparece nas pesquisas"* |
| `ContentFormController.php:149` | *"Tags"* (placeholder: "separadas por vírgula") | *"Etiquetas"* (placeholder: "separadas por vírgulas, ex.: mineração, diamantes") |
| `ContentFormController.php:150` | *"SEO — meta title"* | *"Título nas pesquisas (Google)"* |
| `ContentFormController.php:151` | *"SEO — meta description"* | *"Descrição nas pesquisas (Google)"* |
| `ContentFormController.php:335` | *"Slug (URL)"* | *"Endereço da página"* (hint: *"Deixe como está, a menos que saiba o que está a mudar"*) |
| `ContentFormController.php:340` | *"Agendamento"* | *"Data e hora de publicação"* |
| `ContentFormController.php:342` | *"Excerto"* | *"Resumo curto"* |
| `livewire/admin/documents-table.blade.php:13` e `admin/documento-form.blade.php:51` | *"Link de Download"* | *"Ficheiro para descarregar (PDF/artigo)"* |

**Modelo a seguir:** o editor de notícias já está correto — usa *"Endereço (/noticias/…)"* em vez de slug, *"Etiquetas"* em vez de tags, e esconde o SEO num expansor com linguagem simples. A proposta é levar o resto do painel ao mesmo nível.

---

## 4. Erros de apresentação encontrados

| # | Local | Problema | Correção |
|---|---|---|---|
| 4.1 | `livewire/admin/banners-table.blade.php:48` | `</div>` onde devia ser `</nav>` — HTML mal formado | Trocar por `</nav>` |
| 4.2 | `livewire/admin/banners-table.blade.php:26` | `ucfirst($banner->posicao)` mostra **"Rodape"** sem acento (e "Topo/Sidebar/Lateral" em inglês/misturado) | Mapa de rótulos: `topo` → **Topo**, `sidebar` → **Grelha lateral**, `lateral` → **Lateral (vertical)**, `rodape` → **Rodapé** |
| 4.3 | 7 tabelas (notícias, banners, conteúdo, empresas, documentos, profissionais, utilizadores) | Paginação desenha **todas** as páginas: `@for ($pagina = 1; $pagina <= lastPage(); $pagina++)` — com 50 páginas aparecem 50 botões | Janela deslizante: `1 … 3 4 5 … 12` (padrão profissional; página atual sempre visível) |
| 4.4 | `admin/index.blade.php:84` (opcional) | `chart.umd.js` bloqueia o primeiro desenho do dashboard | Adicionar `defer` para o gráfico não atrasar a página |

---

## 5. O que já está bem (não mexer)

- **Velocidade do servidor:** TTFB 267–307 ms em todas as páginas ✓
- **Consultas à base de dados:** tabelas Livewire com carregamento antecipado das relações (`with(['category','author'])`), paginação a 8 registos, ordenação com lista branca de colunas ✓
- **Índices:** `page_views.date` tem índice único ✓
- **Segurança das ações:** confirmação por modal antes de eliminar, verificação de permissões em cada ação ✓
- **Acessibilidade estrutural:** labels nos filtros, `aria-current` na paginação, link "saltar para o conteúdo" ✓
- **Editor de notícias:** já usa linguagem simples — é o padrão a replicar no resto ✓
- **Visual:** layout limpo, sidebar organizada em grupos, cartões consistentes ✓

---

## 6. Proposta de implementação (3 fases independentes)

### FASE A — Desempenho: painel carrega só o que usa ⚡ *(recomendada primeiro — maior ganho)*

| Item | Detalhe |
|---|---|
| **O quê** | Criar `public/css/admin.css` = `dashboard.css` (46 KB) + subconjunto de ícones FontAwesome (~10 KB) + `.sr-only` + `@font-face`. No layout admin: **retirar** `style.css`, `plugins.css`, `color.css`, `plugins.js`; **manter** jquery, dashboard.js, banner.js, fonts.css, article.css |
| **Porquê** | Remove ~590 KB de peso morto por página. Transferido cai de **~1 013 KB → ~350 KB** nas listas (−65%) e de 1 214 → ~380 KB no dashboard. Menos trabalho do browser → navegação mais fluida, menos "engasgos" |
| **Segurança** | Antes de aplicar: varrimento automático de **todas** as classes usadas nas vistas do admin, confirmando que cada uma existe no `admin.css` (as que faltarem entram). Depois: comparação visual (screenshots) página a página antes/depois, suite de testes e `view:cache` |
| **Risco** | Baixo-médio (alguma classe escondida do tema pode faltar → detetado no varrimento + QA visual). Reversível em 1 ficheiro |
| **Esforço** | ~½ dia de trabalho + QA |

### FASE B — Linguagem para não-técnicos 💬 *(rápida e sem risco)*

| Item | Detalhe |
|---|---|
| **O quê** | Reescrever os 14 rótulos/hints do inventário da secção 3 (6 vistas + 1 controlador). Só texto — **zero alterações de lógica, campos ou base de dados** |
| **Porquê** | É a queixa principal: o utilizador final precisa de saber *o que é* cada campo sem formação prévia |
| **Risco** | Nulo (texto apenas) |
| **Esforço** | ~1 hora + QA |

### FASE C — Correções de apresentação 🧹

| Item | Detalhe |
|---|---|
| **O quê** | (1) Bug `</nav>` na tabela de banners; (2) acentos/rótulos das posições de banner; (3) paginação com janela deslizante nas 7 tabelas; (4) `defer` no gráfico do dashboard |
| **Porquê** | Acabar com o visual descuidado ("Rodape" sem acento) e com listas de 50 botões quando a tabela cresce |
| **Risco** | Baixo (paginação é o maior item — mesmo padrão repetido nas 7 tabelas) |
| **Esforço** | ~2–3 horas + QA |

### Fora do âmbito (proposta consciente)

- **TinyMCE:** mantém-se (editor de texto rico é necessário e já carrega só onde é preciso).
- **jQuery:** mantém-se (os scripts do painel dependem dele).
- **Visual/cores/layout:** não muda nada — apenas o que os ficheiros carregam e o que os rótulos dizem.
- **Base de dados e permissões:** sem alterações.

---

## 7. Como validamos cada fase (critério de aceitação)

1. `php artisan view:cache` passa;
2. Suite de testes verde (atual: **213 testes / 729 asserções**);
3. **QA visual com screenshots** página a página (dashboard, notícias, editor, banners, empresas, documentos, utilizadores) — antes e depois da Fase A;
4. Re-medição com o mesmo script `cdp-admin-perf.mjs` — o relatório mostra a comparação antes/depois em números;
5. Verificação de acessibilidade (link "saltar", ícones com texto alternativo continuam a funcionar).

## 8. Ganho esperado (números)

| Página | Antes | Depois da Fase A | Redução |
|---|---|---|---|
| Dashboard | 1 214 KB | ~380 KB | −69% |
| Listas (notícias/empresas/documentos/utilizadores) | 1 013 KB | ~350 KB | −65% |
| Editor de notícias | 2 366 KB | ~1 770 KB | −25% (TinyMCE mantém-se) |

Em internet de 3 Mbps (realidade comum no país): lista de notícias passa de **~3 s** a **~1 s** de descarregamento por página aberta.

---

## 9. Perguntas para aprovação

1. **Aprova as 3 fases** (A → B → C, cada uma com o seu commit e QA)?
2. **Ordem preferida?** Recomendo A primeiro (ganho maior), mas B pode vir primeiro se preferir ver já a linguagem corrigida.
3. Este documento (`melhoria.md`) fica **fora do repositório** (como está) ou querem que seja commitado para registo?

---

*Documento produzido a partir de análise de código e medições reais no servidor de desenvolvimento. Ficheiros citados com linha para reprodutibilidade.*
