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

## FASE E2 — Views em notícias (proposta)

**Objectivo:** explicitar com o cliente como contabilizar «views» nas notícias e se vai usar isto para ordenação / KPIs (notícias populares / mais lidas / KPI no dashboard).

### Estado actual
- Tabela `news` tem `views_count`; o frontend incrementa por pedido `news.show` (via `newsController@show`; ver `NewsController@show` ou similar).
- Dashboard mostra «Visitas no Site» em agregado (`page_views` da BD de dev).

### Proposta (bloco de decisão pelo cliente, não implementar sem OK)
**Opção A (recomendada) — Counter actual + filtros анти-b parcial:**
- Continuar a incrementar `views_count` em página de leitura.
- Filtrar bots: não incrementar para crawlers conhecidos (Googlebot/Bingbot/FacebookExternalHit), para não inflar número.
- Contar únicos por sessão: hashit de `session_key` em última vista; mesma sessão não incrementa novamente nas 5 minutos seguintes.
- (Dica: coluna `last_visit_at` para evitar repetir na mesma sessão sem re-gravar nada na BD.)

**Opção B — Vista por dia (trend):**
- Nova tabela `page_views_daily (date, article_id, views)` (upsert por dia).
- Vantagens: dashboard pode mostrar tendência, top artigos por período; mede anomalias.
- Desvantagens: mais 1 tabela; mais 2 queries por pedido de página.
- Recommend skip para MVP.

**Opção C — Externo (Plausible / Umami / Google Analytics)**:
- Sete o cliente não precisa disso; overkill.

### Perguntas ao cliente
1. Contagem actual (real) é suficiente ou quer filtragem anti-bot? (recomendo anti-b)
2. Precisa de KPI disponível no dashboard (top 5 notícias no último mês)? (recomendo)
3. Precisa de relatório diário/semanal? (geralmente não — é extra). Atencão: E2 é decisão do cliente antes de implementar; nada fica em produção sem OK.

### CA (critério de aceitação)
- [ ] Definição de «unique view» acordada com cliente (sessão ounão).
- [ ] Implementação acordada (A escolhida ou outra).
- [ ] Dashboard mostra total acumulado *e* Top 5 mais lidas (нов.) com números reais não random.
- [ ] Sem sessões infláveis (bots e r&r omitidos por defeito).

### Risco
Baixo. A tabela `page_views` (dev) tem `date` unique — suficiente como backup para KPIs mensais; para trends por artigo é necessário schema extra.

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
2. ✅ **E1** — províncias (18 + «Outros» com texto livre) implementada e verificada (suite 233/793)
3. ⏳ **E2** — views com anti-bot + «Top 5 mais lidas» no dashboard — próximo passo
4. QA completo após cada fase (screenshot + suite) + atualização deste documento.
