# 🔍 Prompt de Auditoria — Mapeamento da Home (Áreas de Destaque)

## 🎯 1. Objetivo
Analisar o código atual da página inicial (`resources/views/home.blade.php` ou equivalente) e do seu respetivo CSS/JS para identificar todas as "zonas" onde as notícias são exibidas. O objetivo é criar um mapa (wireframe) para sabermos onde alocar as notícias no CMS.

## 📋 2. O que deve ser analisado
1. **Topo (Hero):** Existe um grande carrossel/slider? Quantas notícias ele exibe simultaneamente? (Normalmente 1 grande + 2/3 pequenas ao lado).
2. **Secção de Destaques (Top Stories):** Há uma zona logo abaixo do Hero com 2 ou 3 blocos?
3. **Secção de Vídeos:** Há uma área dedicada a vídeos (incorporados do YouTube)?
4. **Zona Intermédia (Grid):** Existe uma grelha de notícias mais antigas?
5. **Widgets Laterais (Sidebar):** Existe uma barra lateral na Home? O que tem nela? (Últimas notícias? Categorias?).
6. **Empresas em Destaque:** Existe alguma área na Home que mostra as empresas do diretório?

## 📝 3. O que a IA deve entregar
- Uma lista exata de cada bloco visual na Home.
- Para cada bloco, indicar qual é o código HTML atual (classes do template) e qual é a variável PHP que está a carregar os dados hoje (ex: `$latestNews`).
- Uma sugestão de quantas notícias devem ser puxadas para cada bloco (ex: "O carrossel precisa de 4 notícias").

## 🛑 4. Regras para a IA
- **NÃO alterar o código** nesta fase. Apenas ler, mapear e reportar.
- **NÃO criar novos campos na base de dados** ainda.
- O relatório deve ser apresentado em formato de tabela ou lista, para debatermos com o cliente antes de implementar o sistema de gestão de destaques.

---

# 🤖 RELATÓRIO DA IA — Mapeamento da Home (Zonas de Destaque)

Regra cumprida: **nada foi alterado no código** nesta análise; **nenhum campo novo na BD**.
Análise feita em 13/11/2026 sobre `resources/views/home.blade.php` + `AppServiceProvider` + `FragmentController` + componentes referenciados.

## 1. Resumo do estado atual
- A Home está **maioritariamente dinâmica** (listNews/widgetNews/modelos News/Video/Company).
- **Ordenação problemática:** `$listNews` e `$widgetNews` usam `orderBy('id')` **ascendente** — as secções mostram as notícias **MAIS ANTIGAS primeiro** (hoje a Home abre com artigos de 18/05/2022).
- **Hero** convertido em 13/11/2026 para `heroNews` (4 mais recentes, ordem desc).

## 2. Dados de apoio (oriundos da BD)
| Variável | Origem | Ordem | Nº |
|---|---|---|---|
| `$listNews` | `News::published()->with(['category','author'])->orderBy('id')->take(12)` | **ASC** (antigas primeiro) | 12 |
| `$widgetNews` | `News::published()->with(['category','author'])->orderBy('id')->take(8)` | **ASC** | 8 |
| `$heroNews` | `News::published()->with(['category','author'])->orderByDesc('id')->take(4)` | **DESC** | 4 |
| `$videosHome` | `Video::orderByDesc('published_at')->orderByDesc('id')->take(6)` | DESC | 6 |
| `$listProjects` | `Project::published()->orderBy('id')->take(12)` | ASC | 12 |

## 3. Tabela Zona → Variável → Quantidade
| Zona (bloco visual) | Linha aprox. | Fonte de dados hoje | Dinâmico? | Nº de notícias |
|---|---|---|---|---|
| Hero slider (principal) | 8-170 | `$heroNews` (4 mais recentes) | Sim (após 13/11) | **4** |
| Hero caption thumbnails | 98-162 | `$heroNews` | Sim | 4 |
| "Últimas Notícias" cabeçalho | 188-199 | Estático (título + 4 links AJAX `ajax/noticias-*.html`) | Híbrido | — |
| AJAX tabs (conteúdo) | 200-203 | `<ajax-content>` — vazio até clique; preenchido via `scripts.js` + `FragmentController::show('noticias-*.html')` | Híbrido (BD, seleção por **slugs hardcoded**) | 17 slugs fixos |
| Banner ad (`images/all/banner.jpg`) | 205 | Estático (tema) | **Não** | — |
| Zona Destaques (2 cards + swiper) | 208-290 | `$listNews->take(1)`, `skip(1)->take(1)`, `take(3)` | Sim (ordem errada) | **5** |
| Sidebar — banner lateral | 390 | Modelo `Banner` (posição `lateral`) | Sim (rotativo) | — |
| Sidebar — Notícias Populares (tab) | 406-425 | `$widgetNews->take(4)` | Sim | 4 |
| Sidebar — Notícias Recentes (tab) | 429-452 | `$widgetNews->take(3)` | Sim | 3 |
| Sidebar — PIGEA em números | 460 | BD real (News/Project/Company/Research) | Sim | — |
| Sidebar — Redes Sociais | 463 | `components.redes-sociais` (settings) | Sim | — |
| Sidebar — Tags Populares | 467 | **Hardcoded** (6 tags fixas, links `#` vazios) | **Não** | — |
| Sidebar — Destaque ("Escolha da Equipa") | 481 | `components.destaque` (rotativo) | Sim | — |
| Vídeo em Destaque (grande) | 502-537 | `$videosHome` (módulo Vídeos) | Sim | 1 ativo |
| Vídeo picker (thumbnails) | 538-565 | `$videosHome->skip(1)->take(4)` | Sim | 4 |
| Vídeos Populares (carousel) | 566-665 | `$videosHome` | Sim | 6 |
| Melhores por Categoria | 578-818 | `$listNews->skip(6..10)->take(1)` | Sim | 5 |
| Notícias em Alta (carousel) | 733-735 | `$listNews->take(4)` | Sim | 4 |
| CTA Registe a sua empresa | 777 | Estático | Não | — |
| Banner ad após CTA | 881 | Estático | Não | — |

## 4. Análise detalhada Zona → Variável → Nota

### 4.1 Hero (principal) e thumbnails
- HTML: `.hero-slider-wrap > .swiper-container > 4 × .swiper-slide` (um por notícia recente).
- Variável: `$heroNews` (View::share registado em `AppServiceProvider::boot()`).
- Recomendação: **4 publicações** com capa; exige `status = published` e capa (`image`).
- Fallback quando não houver notícias: atualmente não existe (a secção fica vazia — confirmar com cliente se deve ter slide genérico estático como rodapê).

### 4.2 "Últimas Notícias" (cabeçalho) com AJAX tabs
- HTML: `.section-title` + `<div id="ajax-content">` — preenchido por AJAX após clique numa das 4 tabs.
- Tabs: `Notícias em Destaque categories: Mineração | Água | Ambiente | Investigação`.
- Controller: `FragmentController::show(string $arquivo)` — **whitelist de 4 ficheiros**; cada fragment selecciona notícias **por slugs hardcoded** (ex.: `noticias-mineracao.html` = 5 slugs).
- ⚠️ Fragilidade: se uma notícia publicada não estiver nos slugs da whitelist, não aparece na tab correspondente. Inverso: se o cliente alterar o slug, a tab fica vazia silenciosamente.
- Sugestão: substituir a whitelist por `News::whereHas('category', fn($q) => $q->where('menu_group', 'investigacao'))->latest()->take(5)` (o campo `categories.menu_group` já existe).

### 4.3 Banner ad `images/all/banner.jpg` (2 sítios na home, 1 no artigo)
- HTML: `<div class="content-banner-wrap cbw_mar"><img src="…/images/all/banner.jpg"></div>`.
- Estático do tema — nunca foi ligado a nenhum módulo (Banners, Destaques).
- Recomendação: substituir por `<x-banner-publicitario posicao="sidebar" wrapper />` ou eliminar se não houver anúncio real para esta zona. Hoje é peso morto.

### 4.4 Zona "Destaques" (2 cards + swiper)
- HTML: `.grid-post-wrap > .list-post-wrap > .list-post × 2` + `.single-grid-slider > .swiper-container > 3 slides`.
- Variável: `$listNews->take(1)`, `$listNews->skip(1)->take(1)` (cards principais), `take(3)` (swiper).
- Ordenação: hoje `orderBy('id') ASC` (mais antigas primeiro).
- Recomendação: **5 notícias** com capa e imagem; idealmente seleccionar por recência/destaque editorial.

### 4.5 Sidebar
- 6 blocos cognitivamente densos na vertical:
  1. Banner vertical `lateral` — 1:1, slide rotativo (módulo Banners, OK).
  2. Notícias Populares + Recentes (tabs `tabs-act`) — `$widgetNews->take(4)/take(3)`.
  3. PIGEA em números — dinâmico, bem feito.
  4. Redes Sociais — dinâmico (settings da BD).
  5. Tags Populares — **hardcoded** — devia ser gerado de `News.tags` (top 6, ordenado por frequência) com link real `/pesquisa?se=tag`.
  6. Destaque "Escolha da Equipa" — sistema rotativo (bem feito).

### 4.6 Vídeo em Destaque + Vídeos Populares
- Variável: `$videosHome` (View composer para `home`), até 6 vídeos ordenados por `published_at` desc.
- Recomendação: 6 vídeos, thumbnail automática via `Video::thumb()` (YouTube `img.youtube.com` / placeholder PIGEA). Vídeos *não são artigos* — módulo Vídeos próprio.

### 4.7 Melhores por Categoria
- Variável: `$listNews->skip(6..10)->take(1)` → 5 cartões com obrigatoriedade de capa + imagem.
- Recomendação: 5 notícias (idealmente 1 por categoria de editor: Mineração, Água, Ambiente, Investigação, Geociências).

### 4.8 Notícias em Alta (carousel final)
- Variável: `$listNews->take(4)`.
- Recomendação: 4 notícias com capa.

## 5. Sugestão de alocação no CMS (debate com o cliente)

| Zona | Notícias necessárias | Regras |
|---|---|---|
| Hero | 4 recentes | `status = published`, capa obrigatória |
| Últimas Notícias (tabs AJAX) | 5 por tab × 4 = 20 | Atribuídas a `menu_group`: `mineracao`, `agua`, `ambiente`, `investigacao`; hoje a whitelist usa slugs hardcoded |
| Destaques (2 cards + swiper) | 5 | Automático por recência ou flag editorial |
| Sidebar Pop/Recent | 8 | Ordering `published_at` desc (hoje ASC por `id`) |
| Vídeos Populares | 6 | Módulo Vídeos com `published_at` preenchido |
| Melhores por Categoria | 5 | Top recentes com capa |
| Notícias em Alta | 4 | Mais visualizados (aguarda contadores de views reais) |
| Tags Populares | n/a | Derivado de `news.tags` (top 6 por frequência) |
| CTA da newsletter/registo | n/a | Estático de marcação — manter |

## 6. Problemas identificados para correção (AGUARDANDO aprovação; nada foi alterado)
1. **Ordenação errada** de `$listNews`/`$widgetNews` — notícias mais antigas em primeiro nas zonas recentes.
2. **FragmentAJAX por whitelist de slugs** — manutenção manual; devia ser `whereHas('category', grupo)->latest()->take(5)`.
3. **Tags Populares hardcoded** — 6 links "#" sem destino.
4. **Banner `images/all/banner.jpg`** estático em 2 sítios da Home e 1 no artigo.
5. **Sem controlo editorial** para o cliente decidir quais notícias entram no hero/destaques — tudo automático via ID. Se o cliente quiser controlo, precisa de uma flag na tabela `news` (ex.: `destaque_homepage`) ou usar o módulo Destaques para substituir.
6. **Fallback de capa ausente** — se uma notícia não tiver imagem, o card mostra placeholder; o cliente ainda não viu isto.

## 7. Pontos confirmados positivos
- Sidebar "PIGEA em números" — dinâmico, real, bem feito.
- Sistema rotativo de Destaques ("Escolha da Equipa") — ok.
- Sistema de Banners (lateral) — ok (rotativo, settings admin, placeholder PIGEA).
- Módulo Vídeos — ok (dinâmico, `published_at` desc, thumbnail YouTube automático).
- Hero convertido 13/11/2026 — 4 mais recentes.

## 8. Recomendação final
Antes de implementar qualquer sistema de destaques manuais, **propor ao cliente escolher uma das duas abordagens**:
A. **Tudo automático** (recomendo): hero = últimas 4; destaques = próximas por recência; tabs AJAX = últimas 5 por `menu_group`; tags = top 6 frequências; notícias/recentes = `published_at` desc. Mantém-se sem novos campos na BD.
B. **Controlo editorial** (marca governada): adicionar flag `news.destaque_homepage` (boolean). As zonas Hero/Destaques mostram primeiro marcadas, depois as mais recentes; conteúdo dinâmico mas com pin.
