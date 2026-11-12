# verificar_cms.md — Lista de verificação manual do CMS PIGEA (para o Jorge)

**Data:** 11/11/2026 · **Contexto:** `cms_v.md` concluído (Fases 1–8). Os pontos marcados como **NÃO TESTÁVEL SEM BROWSER** só tu os podes confirmar interativamente — eu não tenho browser.

Tudo o que é verificável por máquina foi verificado por suíte (`php artisan test` **OK, 181 testes, 631 assertions**) ou por navegação HTTP (200 em todas as rotas públicas/admin ensaiadas). **A conformidade AA do ecrã só se declara após esta checklist ser validada em browser.**

---

## A. Editor de notícias (`/gestao-pigea/noticias/nova` ou `/{id}/editar`)

| # | Teste | Esperado |
|---|---|---|
| A1 | Abrir o editor com uma notícia publicada | O TinyMCE carrega como iframe com barra de ferramentas (undo redo \| blocks \| Bold/Italic/… \| \| link image table \| menu «Estilos do PIGEA») |
| A2 | Escolher «Estilos do PIGEA → Letra capitular» num parágrafo | O parágrafo ganha `class="drop-cap"` e a primeira letra fica grande e vermelha (#C8102E, Playfair) |
| A3 | «Citação em destaque» | `<blockquote class="pull-quote">` com faixa vermelha à esquerda, fundo escuro claro, aspas |
| A4 | «Duas colunas» | Texto a duas colunas, colapsa para uma em ≤768px |
| A5 | «Imagem à esquerda/direita» numa `<figure>` | Flutua com `figcaption` (crédito) abaixo |
| A6 | Colar texto de um navegador com `style="color:red"` | O estilo não sobrevive ao guardar (sanitizado ao guardar) |
| A7 | Colar uma imagem de clipboard (Print Screen) | Nada acontece / imagem não entra automaticamente (só pela aba Imagem) |
| A8 | Inserir imagem pelo diálogo → escolher ficheiro HD | A imagem sobe para `storage/noticias/corpo/AAAA/MM/`, converte para **WebP** (<=1200px) e aparece no corpo |
| A9 | Alt vazio no diálogo de imagem | Avisa que o texto alternativo está vazio (image_description/type `required` na prática) |
| A10 | Guardar → sair → reabrir a mesma notícia | Classes e conteúdo intactos; `id="conteudo-editor"` mantém o corpo |
| A11 | Escrever texto, esperar ≥15s sem tocar | Indicador «Guardado às HH:MM» aparece |
| A12 | Fechar a aba com alterações por guardar | Browser pede confirmação («Tens alterações por guardar nesta notícia.») |
| A13 | Título com 191+ caracteres | O contador fica vermelho e o servidor devolve erro `max:190` |
| A14 | Publicar sem título | Erro visível + foco vai para o campo título; o corpo do editor **não** se perde (old() + redirect back) |
| A15 | Publicar com título + categoria, sem corpo | Erro `conteudo.required` visível + foco no editor |
| A16 | Editor (sem role admin) vê «Submeter para revisão» em vez de «Publicar» — **apenas** se `EDITOR_PODE_PUBLICAR=false`; default é **true** (todos com `news.manage` podem publicar) | Confirmar papel do utilizador no topbar («Admin» / «Editor») e botões coerentes |
| A17 | Admin: depois de publicado, «Devolver a rascunho» / «Despublicar» aparecem no cartão Publicar | Dois botões extra (admin only); devolver muda estado para rascunho; despublicar idem e fecha artigo do site |
| A18 | Autosave para artigo novo | Abrir «Nova notícia», escrever sem publicar, esperar 15s. Indicador passa para «A guardar…» → «Guardado às HH:MM»; `data-id` + `_method=PUT` ficam definidos; fechar sem guardar e abrir «Nova notícia» outra vez não traz rascunho autosave (rascunhos autosave criados por esta sessão abrem em edição). |
| A19 | Agendamento via «Publicar em» | Preencher título/categoria/corpo/artigo, desligar «Publicar em» para um horário futuro, clicar «Publicar». Artigos ficam invisíveis no site público até à hora. `date_label` vazio. Ao fim da hora, o agendador (Windows Task Scheduler chamando `php artisan schedule:run` a cada minuto) atribui `date_label` e o artigo fica visível. |

## B. Pré-visualização

## B. Pré-visualização

| # | Teste | Esperado |
|---|---|---|
| B1 | Carregar estado «Corpo vazio», clicar «Pré-visualizar» | Abre modal com mesmo header ©meta e corpo vazio com aviso «Corpo ainda vazio»; nada é guardado |
| B2 | Alterar título/categoria/corpo sem guardar, Pré-visualizar | Mostra **o estado atual**, inclusive o que não foi guardado |
| B3 | Fechar por ×, Escape, clique no backdrop, botão «Fechar pré-visualização» | Devolução de foco ao botão que abriu; URL do preview revogado do Object URL |
| B4 | Pré-visualização e site público: comparar a mesma notícia | **Mesma** letra capitular, duas colunas, citação, figuras, tabelas — usa-se o mesmo `.article-body` e `/css/article.css` |

## C. Site público

| # | Teste | Esperado |
|---|---|---|
| C1 | Abrir artigo com `<h2>`, duas colunas, letra capitular, pull-quote, tabela, lista | Tudo visualmente idêntico à pré-visualização; sem consola mostrando erros CSS |
| C2 | `<img>` com `/storage/noticias/corpo/...` | Carrega; `<img>` externo (https) também carrega |
| C3 | Link `target="_blank"` | Chega com `rel="noopener noreferrer"` (não cai em tabnabbing) |
| C4 | Notícia «Em revisão» (status `em_revisao`) | **Não** aparece no público (via `scopePublished`) |
| C5 | Notícia publicada com data **futura** | **Não** aparece no público até à data; barra lateral/news index não mostra |
| C6 | Após a data fazer efeito (comando `news:publish-scheduled`) | Artigos agendados tornam-se visíveis com `date_label` preenchido |

## D. Painel (topbar, sidebar, conteúdo)

| # | Teste | Esperado |
|---|---|---|
| D1 | Topbar: nome + **papel** (Admin/Editor) ao lado do avatar | Só gémeos visuais com papel visível |
| D2 | Logout **só** no topbar; sidebar não tem «Sair» duplicado | Sidebar sem a última linha Sair |
| D3 | Badge do sino | Aparece só com pendências (≥2) removíveis; some com 0; link abre a fila de moderação |
| D4 | Breadcrumb | Uma linha, sem quebra, sempre `<nav aria-label="Caminho">` + `ol` com `aria-current` |
| D5 | **Skip link** («Saltar para o conteúdo») | Tab logo na página: aparece focado; ativa salta para `<main id="conteudo-principal">` |
| D6 | Breadcrumb > longas categorias | Ellipsis `…` se exceder largura |
| D7 | Ação de apagar um banner (ou similar) | Abre **modal** (não `confirm()` nativo); confirmação remove |
| D8 | Tabela de notícias: pesquisa, filtro Estado/Categoria/Autor, ordenar por coluna (↑↓) | Comportamento correto em cada combinação; `aria-sort` coerente |
| D9 | «Duplicar» numa notícia | Nova linha rascunho com título «(cópia)», slug único |
| D10 | Eliminar notícia → «Só apagados» | Vai para o lixo; «Restaurar» devolve |
| D11 | Estado vazio em filtragem sem resultados | Mensagem PT + botão «Limpar filtros» |
| D12 | Teclado: Tab por toda a área e Enter em todos os botões | Foco visível; não fica preso em lugar nenhum; skip-link e modals respondem a Escape |

## E. Acessibilidade (WCAG AA — só tu podes confirmar)

| # | Teste | Critério |
|---|---|---|
| E1 | Zoom 400% (Ctrl+= 3–4x): editor e pública sem overflow horizontal | 1.4.10 Reflow AA |
| E2 | Contraste real de texto pequeno (placeholders, breadcrumb `›`, badges) no teu monitor | 1.4.3 AA — verificar `#666`, `#767676`, `#A0A0A0` nos states medidos (medidos no código, não no ecrã real) |
| E3 | Navegação só teclado completa (Tab → Enter → Escape) em todas as páginas admin | 2.1.1/2.4.7 AA |
| E4 | Zoom auditável de um leitor de ecrã (NVDA/JAWS/VoiceOver) na página de edição | 2.4.3 AA — foco ao abrir modal, Tab wrap, devolução ao trigger |
| E5 | `aria-labelledby` do título do modal; `role=dialog`; `aria-hidden` sincronizado quando aberto/fechado | 4.1.2 AA — ver dashboard.js |

## F. Automáticos (máquina — tudo verde; repetível)

| # | Teste | Resultado medido |
|---|---|---|
| F1 | `php artisan test` | **OK (181 tests, 631 assertions)** |
| F2 | `php -l` em todos os PHP alterados | sem erros de sintaxe |
| F3 | `node --check public/js/admin/news-editor.js` e `node --check public/js/dashboard.js` | exit 0 |
| F4 | JSON seed `database/data/news.json` | válido; `drop-cap`/`two-cols` |
| F5 | HTML render: `.article-body` presente no show e na pré-visualização | confirmado por código e HTTP 200 |
| F6 | Sanitizer: script/on*/style/classe desconhecida/javascript:/iframe | testado em `ArticleSanitizerTest` |

## O que **não** foi possível confirmar (honestamente)

- **Browser ou screen reader**: A/B/C/D/E (a interação visual e o comportamento do iframe do TinyMCE em runtime).
- **Scheduler**: não configurei cron/Task Scheduler — o comando `news:publish-scheduled` precisa dele para correr sozinho (corre manualmente).
- **Real-time do upload**: `Storage::fake()` nos testes, não o pica-a-real com GD num upload de browser.
- **Zoom AA real**: `#666`/`#767676` são métricas do código; em ecrã real podem depender do gamma/brilho do teu monitor.
- **Nenhum browser tool instalado**: nada foi capturado em imagens nem comparado visualmente; usa o que os testes dizem sobre HTML real + a tua validação visual.
- **Notícia pública com sessão em browser**: não pude verificar que a transição está mesmo invisível na página pública da notícia (ex.: uma `em_revisao`) sem interagir — confirma em browser (F5) e com a suíte (181 testes passando, incluindo `scopePublished`).

---

**Assinatura:** ______________________ (Jorge)
**Data:** ____/____/________
**Notas/incidentes:**
