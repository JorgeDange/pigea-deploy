# 📋 Plano de Implementação — Fases E1..E3

> Instrução: 3 melhorias pedidas pelo cliente, implementadas por fases sem commitar de uma vez.
> Documento vivo — atualizar a cada avanço.

## FASE E1 — Provincias (18 + "Outros" com texto livre) ✅ (implementada 2026-11-13)

**Objecto:** todas as **18 províncias oficiais de Angola** + opção «Outros» (texto livre, máx. 100 caracteres) — para notícias/projetos internacionais ou sem província.

### Implementação real (2026-11-13)

- **Helper único** `App\Support\Provincias` (`LISTA` = 18 + `nacional`; `resolve()` para «Outros»→texto livre; `normalizar()` aceita os slugs legados `cuanza-sul`→`kwanza-sul`, `quando-cubango`→`cuando-cubango`; `rotulo()` para apresentação).
- **Componente partilhado** `components/provincia-select.blade.php` (select + campo «Outros» que só aparece nesse caso + `provincia_outro` + JS próprio); modos: completo (rótulo/ajuda/erros próprios) e `bare` (formulário genérico `admin/conteudo-form`).
- **Editor de notícias** e **Novo projeto**: mesmo componente (o projeto tinha só 6 províncias e slugs legados; agora as 18 + Nacional + Outros).
- **Formulário genérico** (editar notícia/projeto noutros campos): passou a tipo `provincia` com o mesmo componente.
- **Validação** (noticia-editor, projeto-novo, autosave, conteudo-form): `provincia` = `nullable|string|max:100`; `provincia_outro` = `nullable|string|max:100` + `required_if:provincia,outros` (exceto no autosave, que não bloqueia).
- **`province` guarda o texto livre directamente** (decisão do cliente, sem coluna extra). Fora de «Outros», o campo `provincia_outro` é ignorado.
- **Migração** `2026_11_13_000200_widen_projects_province.php`: `projects.province` VARCHAR(64) → VARCHAR(120) (o texto livre de até 100 caracteres não cabia). Aplicada em dev. Sem tocar em dados.
- **Dashboard** (`Grafico por província`): rótulos oficiais para slugs conhecidos; texto livre aparece tal como está; os dados ficam os mesmos.
- **Admin `Projetos`** (Livewire): mapa de rótulos passou a espelhar o helper (18 + nacional + legados).
- **Default de hábitos**: clientes antigos com `province` de texto livre continuam a mostrar-se (o componente abre em «Outros» com o texto pré-preenchido).

### CA (critério de aceitação) — resultados
- [x] 18 províncias + «Nacional» + «Outros» aparecem no editor de notícias e no formulário de projeto.
- [x] Seleccionar «Outros» revela o campo de texto (recetável, `qa-e1-editor-outros.png`).
- [x] Seleccionar uma província canónica esconde o texto extra.
- [x] Submit sem «Outros» não mantém lixo (o campo auxiliar é ignorado fora de «Outros»).
- [x] «Outros» sem texto → erro de validação `provincia_outro`; >100 caracteres → erro; fora de «Outros» → ignorado; texto livre «Holanda» grava «Holanda». (4 testes em `NoticiaEditorExtrasTest`)
- [x] Slot «Província» no painel/lista de projetos mostra os rótulos oficiais (18 + nacional + legados).
- [x] Suite: **233 tests / 793 assertions** verdes (+3 face a 230/784); `view:cache` limpo.

### Risco
Baixo. Nenhuma remoção de dados; slugs legados normalizados na escrita (cuanza-sul vira kwanza-sul só quando alguém voltar a gravar o registo). A tabela `users.province` ficou VARCHAR(64) — fora do âmbito desta fase.

---

## FASE E2 — Views em notícias ✅ (implementada 2026-11-13)

**Objecto (decidido pelo cliente, 2026-11-13):**
- **Anti-bot** no `NewsController@show` — os contadores deixam de inflar com crawlers e F5.
- **«Top 5 mais lidas» no Dashboard** — KPI do painel com as 5 notícias publicadas mais vistas.
- Abordagem rejeitada pelo cliente: tabela de tendência diária / filtros completos por IP. O contador actual mantém-se como referência.

### Implementação real
- **`NewsController@show`** (`contarVista()`):
  1. **Ignorar bots**: user-agent vazio OU correspondente a Googlebot, Bing/Yandex/Baidu, DuckDuckBot, Facebook ExternalHit, Twitter/LinkedIn/WhatsApp/Telegram/Discord/Slack, Applebot, Pinterest, previews e monitores/validators (regex case-insensitive).
  2. **Arrefecimento por sessão**: a mesma sessão não conta a mesma notícia duas vezes em 5 minutos (chave `vista_noticia_{id}` com timestamp no store de sessão; sem coluna nova na BD).
- **Views por página**: o público continua a ver o contador em `news/show` (linha com `far fa-eye`), agora com números mais honestos.
- **Dashboard → novo cartão «Mais lidas — Top 5»** (`admin/index.blade.php`, antes de «Atividade recente»): pos. 1-5 (nº 1 destacado a vermelho), título clicável para o artigo, visitas à direita; estado vazio quando ainda não há leituras (e a nota explicativa do comportamento anti-bot).
- **`DashboardController@index`**: `maisLidas` = 5 notícias publicadas ordenadas por `views_count` desc (desempate `published_at`).
- **Estilos** `.top5-list` em `dashboard.css` → `admin.css` reconstruído.

### CA (critério de aceitação) — resultados
- [x] Bots conhecidos não incrementam o contador (Googlebot, Facebook ExternalHit — 2 testes)
- [x] User-agent vazio não incrementa (1 teste)
- [x] A mesma sessão não conta a mesma notícia 2× em 5 min (1 teste)
- [x] Visita real conta uma vez (1 teste)
- [x] Dashboard mostra o Top 5 com números reais (`qa-e2-dashboard-top5.png`)
- [x] Sem sessões «infláveis» por defeito; ordenção por views mantém-se no filtro de notícias do admin
- [x] Suite: **238 tests / 804 assertions** verdes (+5); `view:cache` limpo

### Risco
Baixo. Nenhuma migração. Visitas as antigas continuam na coluna tal como estão (a contagem correcta aplica-se daqui para a frente). Os números do Top 5 refletem o acumulado histórico em dev (inclui visitas antigas já na BD).

### Nota (âmbito)
O mesmo tratamento anti-bot pode, para a frente, aplicar-se a `ProjectController` e restantes contadores do portal — não foi incluído aqui (o pedido do cliente foi as notícias).

---

## FASE E3 — Redes sociais no rodapé + Tamanho da logo ✅ (implementada 2026-11-13)

> **Correcção de 2026-11-13:** a informação no documento inicial estava errada (a logo estava com ~210px).
> A inspecção real (CDP + DOM + ficheiro `logo2.png` 1047×441) mostrou `.footer-logo img { height: 24px }` — a logo estava **pequena demais**, não grande.
> O cliente confirmou (2026-11-13): **aumentar** a logo no rodapé. A barra de copyright já tinha fundo escuro (#191919), apenas o texto foi ajustado.

### Decisões do cliente (2026-11-13)
- **A. Logo no rodapé:** hoje com **24px** de altura → **aumentar para 44px** (≈105px de largura, proporcional à coluna do rodapé).
- **B. Copyright bar:** fundo **#1A1A1A**, texto **branco** (consistência com o rodapé escuro) — era `1991919` com texto `#999` + `© PIGEA` vermelho (`.copyright span` no color.css — removido).
- **C. Redes sociais:** apenas **5 redes** — Facebook, Instagram, LinkedIn, YouTube e **X (Twitter)**.
  - **X (Twitter) aparece APENAS quando configurado no admin** (link OU nome público); nunca como ícone fantasma/apagado.
  - Na prática, a mesma regra passou a aplicar-se a TODAS: uma rede só aparece quando há link ou nome configurado; sem nada configurado, a secção não mostra ícones.
- **D. Outras redes removidas:** o ícone **VK** (Vkontakte) dos 2 cartões «Seguir:» (entrevistas e projeto) foi removido; os `href="#"` mortos do topo do site e dos mesmos cartões passaram a usar as redes configuradas.
- **E. E2:** abordagem Anti-bot + KPI (ver FASE E2) — decisão 2026-11-13: sim filtrar bots + sim trazer «Top 5 mais lidas» no dashboard.

### Estado verificado (2026-11-13)
- **Rodapé:** logo a 44px (`qa-footer-crop.png`, `qa-footer-bottom.png`), 5 ícones vermelhos visíveis (FB, IG, LI, YT, X).
- **Barra de copyright:** `#1A1A1A`, texto branco integral (`qa-footer-bottom.png`).
- **Topbar do site:** ícones dinâmicos a partir das Configurações (nada de `href="#"`).
- **Contactos / «Siga-nos» / cartões «Seguir:»:** mesmos 5 ícones, dinâmicos; sem NENHUMA configurada, não aparece nada (nada de placeholders).
- **Admin → Configurações → Redes sociais:** novo par de campos «X / Twitter (link)» + «Nome público da conta (X / Twitter)» com validação (`twitter`, `twitter_nome` em `SettingsController::REGRAS['redes']`) e cache invalidado no guardar.
- **Base de dados de dev:** 5 redes preenchidas com valores de demonstração (`facebook.com/pigea.ao`, etc.) — o cliente altera/remove no admin a qualquer momento.
- **Nota Font Awesome:** o site usa **Font Awesome Pro 5.12.0** — o X é representado por `fab fa-twitter` (o glifo oficial «X» só existe no FA6+); fica com a etiqueta «X (Twitter)».

### Ficheiros alterados (E3)
- `app/Http/Controllers/Admin/SettingsController.php` — validação `twitter`/`twitter_nome`.
- `resources/views/admin/configuracoes.blade.php` — novos inputs X (link + nome público).
- `resources/views/components/social-icons.blade.php` — reescrito: 5 redes, só configuradas (link ou nome), sem placeholders; handle-only mostra ícone não clicável com o nome.
- `resources/views/components/redes-sociais.blade.php` — reescrito: 5ª rede X + sem bloco fantasma quando nada configurado.
- `resources/views/components/header.blade.php` — topbar desligada dos ícones mortos; usa `components.social-icons`.
- `resources/views/interviews/index.blade.php` e `resources/views/projects/show.blade.php` — cartões «Seguir:» usam as redes configuradas (VK removido, `href="#"` removido).
- `public/css/style.css` — `.footer-logo img` 24px→44px; `.footer-bottom` #191919→#1A1A1A; `.copyright` #999→#FFF; spans das redes com o mesmo bloco dos links.
- `public/css/color.css` — `.footer-social li span`/`.topbar-social li span` no grupo vermelho; `.copyright span` removido (é branco por herança).

### CA (critério de aceitação) — resultados
- [x] Rodapé mostra os ícones das redes configuradas (Facebook, Instagram, LinkedIn, YouTube, X) com inspecção visual.
- [x] Ícones com fundo #C8102E quando configurados (settings). Sem X configurado, o X simplesmente não aparece.
- [x] Configurações admin têm input para URL + nome público do X/Twitter.
- [x] Logo do PIGEA **aumentada** de 24px para 44px (o cliente confirmou que estava pequena demais — não «110px»).
- [x] Copyright bar: fundo #1A1A1A e texto branco integral.
- [x] Suite: **230 tests / 784 assertions** verdes; `view:cache` limpo.

---

## Tabela de decisões (2026-11-13)
| # | Pergunta | Fase | Resposta do cliente |
|---|----------|------|---------------------|
| E1.1 | Armazenamento `province` para «Outros»: `Outros:texto livre` (`Holanda`) diretamente no campo vs. 2 colunas (`province='outros'` + `provincia_outro='Holanda'`). Recomendo: string direta. | E1 | ✅ Texto livre directo no `province` (ex.: «Holanda») — sem coluna extra |
| E1.2 | Permissoes de input livre ≤ max:100 chars sem dicionário? Recomendo: sim. | E1 | ✅ (por omissão das respostas anteriores) |
| E2.1 | Contadores (unique view por sessão)? Recomendo simp. | E2 | ✅ Anti-bot + KPI (`NewsController@show` filtra bots; contagem por sessão) |
| E2.2 | Dashboard Top 5 mais lidas este mês? Recomendo simp. | E2 | ✅ Sim, trazer «Top 5 mais lidas» no dashboard |
| E3.1 | Logo do footer: tamanho? | E3 | ✅ **Aumentar** — hoje 24px estava pequena demais → **44px** (a leitura anterior «reduzir para 110px» estava errada) |
| E3.2 | Copyright bar: fundo escuro/branco? (confirmar cor) | E3 | ✅ Fundo #1A1A1A + texto branco |
| E3.3 | Twitter/X — adicionar campo no Settings? | E3 | ✅ Sim — inputs `twitter` + `twitter_nome`; X aparece APENAS quando configurado (não mostra greyed) |

## Ordem de execução (definida pelo cliente, 2026-11-13)
1. ✅ **E3** — rodapé + redes sociais implementada e verificada
2. ✅ **E1** — províncias (18 + «Outros» com texto livre) implementada e verificada
3. ✅ **E2** — views anti-bot + KPI «Top 5 mais lidas» no dashboard implementada e verificada (238/804)
4. QA completo após cada fase (screenshot + suite) + atualização deste documento.

---

## FASE E4 — «Acompanhamento» (a sala de controlo) ✅ (implementada 2026-11-13)

**Pedido do cliente:** "pode se criar ou implementar area para companhamento alem da dashboard para acompanhamento dessas novas funcionalidade para ter uma boa gestão" — opção escolhida (2026-11-13): **Opção 2 — área própria** (não cartões no Dashboard, nem feature flags).

**O que é:** página `gestao-pigea/acompanhamento` (sidebar principal, admin+editor) — uma única vista com o estado real de TODAS as funcionalidades novas:

| Cartão | Mostra | Botão |
|--------|--------|-------|
| Redes sociais (E3) | As 5 redes configuradas (link OU nome público), contador x/5 | Configurações |
| Banners «Largo» (editoria_cms.md) | nº de ativos e criados; aviso quando não há «Largo» ativo | Publicidade |
| Províncias dos artigos (E1) | publicados com/sem província, texto livre «Outros», top 5 | Notícias |
| Destaque editorial (editoria_cms.md FASE 2) | pins; aviso se Hero não completo | Notícias (coluna ★) |
| Qualidade dos artigos | publicados sem imagem / sem etiquetas (sinais de alerta) | Notícias |
| Vistas reais & Top 5 (E2) | total de vistas; Top 5 mais lidas; explica o anti-bot | Notícias |

**Topo de página:** banner de resumo — «Tudo em ordem» (verde) ou «N pontos a rever» (amarelo), contando quantos cartões têm algo por configurar.

**Implementação:**
- `app/Http/Controllers/Admin/AcompanhamentoController.php` (rota `admin.acompanhamento`, middleware admin+editor — mesma barreira das restantes páginas do painel)
- `resources/views/admin/acompanhamento.blade.php` — grelha 2 colunas, responsiva; contadores reais a cada visita (sem cache)
- Sidebar (`Principal`): «Acompanhamento» com ícone `fa-chart-line` (admin+editor)
- CSS: `.acomp-*` em `dashboard.css` → `admin.css` reconstruído
- 3 testes (`AcompanhamentoTest`): carrega 200 para admin; anónimo é redireccionado; sem redes configuradas a página não rebenta (mostrando «Por configurar»)

**E2 estendido aos projetos (2026-11-13):** o mesmo filtro anti-bot + arrefecimento de 5 min por sessão aplicou-se a `ProjectController@show` via o helper central `App\Support\ContadorDeVisitas::contar()` (única fonte da lista de bots; `NewsController` migrado para o mesmo helper; `CountPublicVisits` passa a usar a mesma regex). 3 testes novos (`ProjectViewsTest`).

**Acompanhamento — tipografia única + gráficos (2026-11-13):** toda a página ficou em **Poppins** (mesma família do painel) — removidas as variações (Playfair Display, monospace). Acrescentados dois gráficos reais (Chart.js auto-hospedado):
- **Artigos publicados — HISTÓRICO** (barras, todo o período): cadência de publicação mês a mês.
- **Prontidão** (doughnut): 6 verificações — Redes, Banner «Largo», Províncias, Destaques (pins≥4), Imagem, Etiquetas — com contagem central "N de 6" e legenda ✓/✗.

**Redesenho profissional da página Acompanhamento (2026-11-13):**
- Hero de estado geral: ícone, «Tudo em ordem» / «N pontos a rever», 4 KPIs (Redes · Pins · Vistas · Alertas) com Playfair Display.
- Cada cartão com ícone quadrado, «kicker» de fase (FASE E3 / FASE E1 / FASE E2 / FASE EDITORIAL / QUALIDADE) e badge de estado à direita.
- Redes com ponto de estado (verde/âmbar) + handle/url visível (máx. 34 chars).
- Províncias com barras de progresso proporcionais (vermelho) e contadores.
- Qualidade com 2 barras de progresso (Imagem de capa · Etiquetas) e cor semântica (verde ≥60%, âmbar, vermelho).
- Destaque editorial com callout de estado do Hero; Vistas reais & Top 5 com a lista intacta.
- suite: **244 testes / 821 verificações**; `view:cache` limpo; `qa-faseb-acompanhamento-v2.png`.

**CA:**
- [x] Página abre no sidebar para admin (3 testes; `qa-faseb-acompanhamento.png` com dados reais — incl. o alerta «1 ponto a rever»: 10 artigos sem etiquetas)
- [x] Todos os contadores reais (sem inventar dados)
- [x] Cada cartão liga ao sítio certo para corrigir
- [x] Suite: **244 tests / 821 assertions** verdes (+6); `view:cache` limpo
- [x] Anti-bot aplicado a notícias **e projetos** (contador global mantém semântica própria; só a lista de bots é partilhada)

**Nota:** o resumo «N pontos a rever» conta: redes a menos de 5, zero «Largo» ativo, artigos sem província, zero pins editoriais, artigos sem imagem, artigos sem etiquetas — qualquer um liga-se diretamente ao botão de correção do seu cartão.

### Estado global das fases E
| Fase | Estado | Commit |
|------|--------|--------|
| E3 — Rodapé/redes | ✅ feita | `52bd4cf` |
| E1 — Províncias | ✅ feita | `0515b37` |
| E2 — Views/KPI | ✅ feita | `9358f84` |
| Banners «Largo» (migração) | ✅ feita | `17cf884` |
| E4 — Acompanhamento (control center) | ✅ feita | aguarda autorização do cliente (ver git) |
