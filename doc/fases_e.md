# 📋 Plano de Implementação — Fases E1..E3

> Instrução: 3 melhorias pedidas pelo cliente, implementadas por fases sem commitar de uma vez.
> Documento vivo — atualizar a cada avanço.

## FASE E1 — Provincias (18 + "Outros" com texto livre)

**Objectivo:** antes as províncias eram apenas 5 (`luanda,benguela,huila,namibe,nacional`) — expandir para todas as **18 províncias de Angola** + opção `Outros` que revela um input de texto (para notícias de outros países ou registos sem província).

### Scope
- **18 províncias** (slugs): `luanda,benguela,huila,namibe,cabinda,malanje,uige,zaire,kwanza-norte,kwanza-sul,lunda-norte,lunda-sul,bie,mocimiboi,cunene,huambo,moxico`.
- **Opção «Outros»**: sempre no fim da lista de opções, revela campo de texto `provincia_outro` (máx. 100 chars) livre para texto (`Internacional`, `Açores`, ...).

### O que muda
- Helper `App\Support\Provincias` exportando lista de 18 + label; usada por:
  - `NewsEditorController@atributos()` (select de Província no editor);
  - `ContentFormController` (formulário genérico de conteúdo que também listas as províncias);
  - `NewsListTable` (filtro categorias no admin — sem mudar tags);
  - `NewsFilter` (filtros públicos `*_create/provecto`).
- Migration: nenhuma (`province` é `string`; aceitação de valor livre = `Outros` com string `outro:texto livre` ou `Internacional` codificado conforme escolha — ver decisão E1.1 no final).
- Editor de notícias (`resources/views/admin/noticia-editor.blade.php`):
  - select com 19 opções (18 + `Outros`);
  - input `provincia_outro` (style inline visível só com `.dashboard-form-group-outra` com `hidden` por default; JS toggle pela sessão; o form pode submeter ambos — validação ignora o input se `provincia` ≠ `outros`);
- Admin validação: `provincia` = `nullable|in:18slugs,outros`; `provincia_outro` = `nullable|required_if:provincia,outros|string|max:100`;
- Armazenamento: `province` = `outros` + `provincia_outro` na coluna actual `province`? Não — campo à parte para não usar coluna como propriedade múltipla? Decidir.

### Implementação visual do select (proposta)
```html
<select id="provincia" name="provincia">
  <option value="">Sem província</option>
  <option value="luanda">Luanda</option>
  ...18 opções...
  <option value="outros">Outros — escrever</option>
</select>
<div id="provincia_outro_wrap">
  <input type="text" id="provincia_outro" name="provincia_outro" placeholder="ex.: Internacional, National Holiday etc." class="dashboard-form-input" maxlength="100" hidden>
</div>
<script>
  provincia.addEventListener('change', function () {
    document.getElementById('provincia_outro').hidden = this.value !== 'outros';
  });
</script>
```

**Decisões pendentes (do cliente):**
- **E1.1 Armazenamento** — armazenar como string livre em `province` quando `outros` (ex. `Holanda`), ou 2 colunas: `province` = `outros` (+ `provincia_outro`). Recomendo: `province` recebe o texto livre directamente (depois de sanitizar), default NULL.100Chars.
- **E1.2 Limpeza limpa** — aceitar qualquer string ≤ 100Chars? Recomendo: aceite livre de texto, validação apenas `max:100` (sanitização extra) Liberdade editorial sem dicionário de países.

### CA (critério de aceitação)
- [ ] 18 províncias + "Outros" aparecem no editor de notícias.
- [ ] Selecionar "Outros" revela campo de texto à hora.
- [ ] Selecionar uma província canónica esconde o texto extra.
- [ ] Submit sem "Outros" não mantém lixo no campo `provincia_outro`.
- [ ] Validação rejeita províncias fora da lista + texto vazio com "outros".
- [ ] Filtro de notícias no admin mostra as 19 opções.

### Risco
MFA positivo: nenhum. Remoção de outras províncias antigos em DEV — não mexer (dados dos seed são apelativos). Validar que não são dados de outros países hardcoded.

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
1. ✅ **E3** — rodapé + redes sociais implementada e verificada (aguarda commit/push).
2. ⏳ **E1** — províncias (18 + «Outros» com texto livre) — próximo passo.
3. ⏳ **E2** — views com anti-bot + «Top 5 mais lidas» no dashboard.
4. QA completo após cada fase (screenshot + suite) + atualização deste documento.
