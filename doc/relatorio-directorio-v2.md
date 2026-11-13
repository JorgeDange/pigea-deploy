# Relatório — /directorio · Redesign Visual v2

**Projeto:** PIGEA (pigea-deploy) · **Data:** 2026-11-12 · **Âmbito:** redesign visual da área `/directorio`
**Stack:** Laravel 12 · Livewire 3 · Bootstrap 3 (grid do tema Gmag) · tema Gmag (`style.css`, `color.css`)
**Sem commits.** Apenas views + CSS aditivo (bloco novo no fim de `public/css/style.css`, Fase 17).

## Antes / Depois

| | Ficheiro (na raiz do `pigea-deploy`) |
|---|---|
| Antes (Empresas, com sobras inline) | `qa-directorio-v2-antes.png` |
| Depois (Empresas) | `qa-directorio-v2-depois-empresas.png` |
| Depois (Artigos/Documentos) | `qa-directorio-v2-depois-documentos.png` |
| Depois (mobile 390px) | `qa-directorio-v2-mobile.png` |

### O que mudou

**Sidebar (coluna esquerda, `col-md-4`)**
- Dois CTAs de registo (**Registar Empresa** / **Profissional independente**) deixaram de ficar colados: cada um passou a ser um `box-widget fl-wrap` (margem inferior do tema, 24px) com `box-widget-content` e `banner-widget` — exatamente o padrão das sidebars do tema (`/noticias`, `/legislacao`).
- Substituído o recuo `padding-left:30px` do `.box-widget` (pensado para sidebars verticais à direita) por 0 dentro de `.directorio-sidebar`.
- **Banner publicitário público colocado abaixo dos CTAs:** `<x-banner-publicitario posicao="sidebar" wrapper />` — mesma estrutura visual das sidebars de `/noticias` e `/noticias/{slug}`. Renderiza o banner com `posicao=sidebar` mais recente (`Banner::ativos()` → `posicao(sidebar)`), com rótulo "Publicidade", `alt` acessível e `lazy-loading`. Existe 1 banner sidebar ativo na base: *"Banner Sidebar — Hidrotec"* (id=2).
- Texto intro e botões sem `style=""` inline; texto dos dois banners passa a branco (`.banner-widget_content p`) para legibilidade sobre o gradiente escuro do `banner-widget`.

**Conteúdo (coluna direita, `col-md-8`)**
- Cards de **Empresas** e de **Documentos** mantêm o agrupamento card a card (mantido conforme pedido), agora com:
  - mesma altura por linha (linha = flex-wrap de 2 cartões, `stretch` força alturas iguais);
  - fundo branco, borda 1px `#eee`, raio 4px, **zero sombras** (antes: `box-shadow: 0 8px 22px rgba(16,24,40,.06)` e avatar `0 8px 18px rgba(200,16,46,.30)` — ambos removidos);
  - avatar vermelho da empresa mantido (inicial em Playfair Display 700 sobre `#C8102E`);
  - ícone de documento por tipo (lei→`fa-gavel`, artigo→`fa-newspaper`, documento→`fa-file-contract`) com **tratamento unificado ouro-sobre-escuro**: caixa `#1A1A1A` + ícone `#F2A900`; chip de tipo (`post-category-marker`) passa a escuro com texto dourado (via `:first-child`, sem classes novas).
- Título dos cards: Poppins 600 / 15px / `#292929` (tipografia do tema — `body`/`.h4` do Gmag).
- Linha de estado "Empresa aprovada": 10px maiúsculas, cinza `#666666`, `✓` dourado `#F2A900`.
- Chips de áreas: `.post-category-marker` (vermelho `#C8102E`, branco) como no resto do site.
- Contacto: ícone vermelho + Poppins 12px, alinhado com `.post-date`/`list-post` do tema.
- Rodapé de cada card (redes sociais ou botão Download): filete superior 1px `#eee` e colado ao fundo via `margin-top:auto`; `.footer-social` sem respiro extra dentro do card.
- Botão **Download**: `btn float-btn color-bg` puro — mesma estilização dos botões do site (vermelho, Poppins 600, maiúsculas, sem `padding/font-size` inline).
- Paginação: markup do tema inalterado (`.pagination`), apenas ganhou `role="navigation" aria-label="…"` para a11y.
- Aba/filtro: estilos inline do `.tabs-widget` (`margin-bottom`), do `filter-search` (largura, altura, borda, raio, foco) e dos ícones das abas foram movidos para o stylesheet com escopo `.directorio-listing`.

## Ficheiros editados (linhas na versão final)

| Ficheiro | Linhas | O que mudou |
|---|---|---|
| `resources\views\directory\index.blade.php` | 28–63 | Sidebar na coluna esquerda: invólucro `directorio-sidebar`; separado intro / CTAs / banner; remoção de `style="padding:"`, `style="margin:"`, `small-btn`, `color2-bg` (classe inexistente); intro `<p>` sem estilo inline; botões CTA com classes válidas; `<x-banner-publicitario posicao=\"sidebar\" wrapper />` na linha 62 |
| `resources\views\livewire\frontend\directory-listing.blade.php` | 1–134 | Reescrita de cards/filtro/abas sem qualquer `style=""` (0 ocorrências). Abas (L3-10), filtro `filter-search` (L11), cards Empresas (L23-58), cards Documentos (L87-114), paginação com `role="navigation"` (L65-78, L122-133) |
| `public\css\style.css` | 5692–5887 | **Novo bloco Fase 17** — contém apenas reforços/overrides escopados a `.directorio-sidebar` / `.directorio-listing` |

Sem toque em: `app/`, models, migrations, rotas, testes, outros templates, `config/`.
Backup pré-edição: `backups\directorio-v2-20261112-1043\` (3 ficheiros).

## Mapeamento "sobras removidas → onde vive agora"

Legenda: **inline** = classe/style inline pegajoso removido · **tema** = trata-se agora com classe/regra existente do Gmag.

### `directory/index.blade.php`
| Removido | Substituído por |
|---|---|
| `<p style="padding: 16px 20px;">` (intro) | `<p>` puro — tema já dá `font-size:11px`/`line-height`/`text-align`; respiro do bloco em `.directorio-sidebar > .box-widget-content` (style.css:5717) |
| `<p style="margin: 6px 0 12px;">` (no banner-widget) | regra `.directorio-sidebar .banner-widget_content p` (style.css:5718) — texto branco sobre gradiente |
| botão `small-btn` (inexistente no tema) | removido — dimensiona pelo `.btn` base do tema (`padding:12px 40px`, Poppins 600, maiúsculas) |
| botão `color2-bg` (inexistente no tema) | `.btn float-btn` puro → fundo `#292929` do tema (coerente com `.load-more_btn`, `.pagination`) |
| caixa intro `<div class="box-widget-content fl-wrap">` sem margens | `.box-widget-content` + respiro `.directorio-sidebar > .box-widget-content { margin-bottom: 28px }` |
| 2 CTAs colados (2× `box-widget-content` sem margem) | 2× `.box-widget fl-wrap` (tema: `margin-bottom:24px`, style.css:5712-5716) com `box-widget-content` interno |
| *(novo)* sem banner publicitário | `<x-banner-publicitario posicao="sidebar" wrapper />` — componente já existente (`components/banner-publicitario.blade.php`), mesmo uso que `news/index.blade.php:45` e `news/show.blade.php:247` |

### `directory-listing.blade.php`
| Removido (inline) | Substituído por |
|---|---|
| `.tabs-widget` `style="margin-bottom: 14px;"` | `.directorio-listing .tabs-widget { margin-bottom: 18px }` (style.css:5836) |
| `<i style="margin-right: 8px;">` (ícones abas) | `.directorio-listing .tabs-menu a i { margin-right: 8px }` (style.css:5837) |
| `<input class="filter-search" style="width/height/border/radius/background/padding/font/color/margin">` | `.directorio-listing .filter-search` (style.css:5838-5852) — mesmo valor/espírito, agora centralizado; foco vermelho (`filter-search:focus`) mantido da Fase 15 |
| Card `<div class="box-widget-content fl-wrap" style="height:100%; margin-bottom:22px; background:#FFF; border:#ECECEC; border-radius:6px; box-shadow:0 8px 22px rgba(16,24,40,.06)">` | `.directorio-listing .row > .col-md-6 > .box-widget-content` (style.css:5732-5744): fundo #FFF, borda 1px `#eee`, raio 4px, margem 22px, **sem** box-shadow; altura via flex-wrap da `.row` |
| `<div class="author-widget fl-wrap" style="display:flex; gap:14px; align-items:flex-start; padding:18px; height:100%">` | `.directorio-listing .author-widget` (style.css:5748-5754) + `.author-widget > div:first-child` (5755) · `> div:last-child` (5787-5792) |
| Avatar `<div style="flex:0 0 58px"> <div style="width:58px;height:58px;border-radius:6px;background:#C8102E;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 18px rgba(200,16,46,.30)">` | 56px box, raio 4px, `#C8102E`, **sem** sombra — `.author-widget > div:first-child > div` (style.css:5756-5763) |
| Iniciais `<span style="font-family:'Playfair Display'; font-size:19px; font-weight:700; color:#FFF; letter-spacing:.03em">` | `.author-widget > div:first-child > div > span` (style.css:5764-5771) — mesmo visual, centralizado |
| Ícone documento `<div style="width:58px;height:58px;border-radius:6px;background:#1A1A1A;display:flex;…;box-shadow:0 8px 18px rgba(0,0,0,.25)"><i style="font-size:24px;color:#F2A900">` | `.author-widget > div:first-child > div.directorio-icone` + `.directorio-icone > i` (style.css:5780-5785) — mesmo unificado ouro-sobre-escuro, sem sombra |
| `<h4 style="margin:1px 0 5px; font-size:15px; line-height:1.4; font-weight:600">` | `.author-widget h4` (style.css:5795-5801) — Poppins 600 15px, `#292929` |
| Estado «Empresa aprovada» `<span style="display:inline-flex; gap:6px; font-size:10.5px; font-weight:600; text-transform:uppercase; letter-spacing:.07em; color:#666"><i style="color:#F2A900">` | `.author-widget > div:last-child > span:not(.post-category-marker)` + `> i` (style.css:5806-5822) — mesmo visual; o `:not(.post-category-marker)` protege os chips de área/tipo |
| Chip tipo `<span style="background:#F2A900; color:#1A1A1A; border-radius:2px; padding:4px 10px; font-size:10px; …">` | `<span class="post-category-marker">` + `:first-child` → fundo `#1A1A1A` / texto `#F2A900` (style.css:5827-5832). Laço visual com a caixa do ícone |
| `<div style="margin:12px 0 10px">` (wrapper chips) | wrapper `<div>` simples; espaçamento vem da margem dos próprios chips (`.directorio-listing .post-category-marker` — Fase 15) e do `align-self:flex-start` + `margin-bottom:10px` (style.css:5823-5826) |
| `<p style="margin:0 0 4px; font-size:12.5px; color:#494949; line-height:1.5"><i style="color:#C8102E; width:15px">` | `.author-widget p` + `p > i` (style.css:5853-5864) — 12px, `#494949` |
| Rodapé redes `<div style="margin-top:auto; padding-top:10px; border-top:1px solid #F0F0F0"><div class="footer-social fl-wrap" style="margin-top:0">` | `.author-widget > div:last-child > div:last-child` (filete 1px `#eee`, `margin-top:auto`) + `.directorio-listing .footer-social { margin-top: 0 }` (style.css:5865-5874) |
| Rodapé Download `<div style="margin-top:auto; padding-top:12px; border-top:1px solid #F0F0F0">` + `style="padding:11px 18px; font-size:10.5px"` no botão | mesmo seletor de rodape; botão `btn float-btn color-bg` puro (ícone após texto → herda `.btn i { margin-left:10px }` do tema) |
| *(novo)* sem a11y na paginação | `role="navigation"` + `aria-label="Paginação de empresas/documentos"` (views, L70/L129) |

**Total de `style=""` removidos: 34** (3 no `index.blade.php` + 31 no `directory-listing.blade.php`). Os únicos `style=""` remanescentes na página (4) são do componente partilhado `<x-banner-publicitario>` (label "Publicidade" e `<img>` responsivo) — igual ao padrão já em produção em `/noticias`; tocá-los não fazia parte do âmbito.

## Critérios de aceite — verificação

1. ✅ **Sidebar**: intro + dois CTAs separados (`box-widget`, 24px gap) + `<x-banner-publicitario posicao="sidebar" wrapper />` abaixo, mesmo padrão de `/noticias` (`qa-directorio-v2-depois-empresas.png`, `…-mobile.png`).
2. ✅ **Cards Empresas**: avatar vermelho mantido; `style=""` eliminados; classes do tema (`box-widget-content`, `author-widget`, `post-category-marker`, `footer-social`, `btn`) + seletores de estrutura com escopo `.directorio-listing`. **Sem classes novas de UI** (apenas `.directorio-sidebar` na sidebar — ver "Hooks novos").
3. ✅ **Cards Documentos**: ícones por tipo (lei→`fa-gavel`, artigo→`fa-newspaper`, documento→`fa-file-contract`, default→`fa-file-alt`) com tratamento unificado ouro-sobre-escuro (`#F2A900` sobre `#1A1A1A`); tipo mostrado como chip escuro/dourado; botão Download = `btn float-btn color-bg` (igual aos botões do site).
4. ✅ **Altura uniforme por linha** (flex-wrap + stretch), respiro 20px, filete `#eee`, **zero sombras novas**; borda 1px `#eee` em harmonia com o tema.
5. ✅ **Tipologia**: Poppins (corpo/botões) + Playfair Display (avatar) + sans do tema; tamanhos 15/12/10px coerentes com `.post-date`/`.post-widget-item-content`.
6. ✅ **Mobile** (<992px): cards empilham a largura total (`flex-basis:100%`); screenshot 390px confirma (`qa-directorio-v2-mobile.png`).
7. ✅ **Sem NIF** no markup (verificado no HTML renderizado); sem overflow horizontal próprio (o clipping horizontal a 390px é o comportamento global do tema, idêntico em `/noticias`); ícones `aria-hidden`, paginação com `role`/`aria-label`.
8. ✅ **Suites**: `php vendor/bin/phpunit` → **OK (204 tests, 694 assertions)**. `php artisan view:cache` → **Blade templates cached successfully.** (cache limpo depois com `view:clear` para o ambiente de desenvolvimento).

## Hooks novos (mínimo necessário)
1. `.directorio-sidebar` — classe de escopo na coluna `col-md-4` (`index.blade.php:28`), análoga à já existente `.directorio-listing` da Fase 15; permite neutralizar o `padding-left:30px` do `.box-widget` e controlar os 24px de separação entre os CTAs **só** nesta página.
2. `.directorio-icone` — marca a caixa escura+dourada dos ícones de documento dentro de `.author-widget > div:first-child` (`directory-listing.blade.php:91, L103`). Zero novas classes de componente/badge — o chip de tipo usa `.post-category-marker` puro com `:first-child`.

## Notas / limites
- O banner sidebar exibido é o único ativo com `posicao=sidebar` na base (*"Banner Sidebar — Hidrotec"*, id=2, sem datas). Quando for criado outro, o mais recente ganha (regra do componente).
- Nenhuma alteração a models, rotas, admin, migrations ou testes. Sem commits. Working tree modificado apenas nos 3 ficheiros acima + este relatório + screenshots `qa-directorio-v2-*.png`.
- A paginação do tema (`.pagination`) e as cores institucionais (`#C8102E`/`#F2A900`) foram mantidas; removeram-se apenas os "reforços" pegajosos (sombras pesadas, gradientes inline, 6px radius, hex "novos" fora da paleta: `#ECECEC`, `#1A1A1A`/`#F0F0F0`/`#A0A0A0` — esses últimos já existiam em `empty-state`/Fase 15, portanto herdados, não inventados).
