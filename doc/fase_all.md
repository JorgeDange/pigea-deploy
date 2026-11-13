# 🗺️ FASE_ALL — Plano Completo de Reestruturação do PIGEA

**Documento-mestre de fases** — baseado em `restrutura.md`  
**Data:** 10/11/2026 (última atualização 11/11/2026 — Fase 10 concluída)  
**Objetivo:** Transformar o PIGEA em Portal de Notícias + Diretório (sem login de empresas/profissionais)  
**Status geral:** ✅ CONCLUÍDO — fases 0–10 implementadas; gate final reality-checker executado (PASS COM RESERVAS — UAT)

> Cada fase deve ser documentada em `memoria.md` quando iniciada e quando concluída. Alterações de código vão para `modificar.md`.

---

## 📊 Visão Geral das Fases

| Fase | Nome | Dependências | Status |
|------|------|--------------|--------|
| 0 | Preparação e Backup | — | 🟢 CONCLUÍDA (10/11/2026) |
| 1 | Limpeza do Sistema (Vagas/Candidaturas/Painel Empresa) | Fase 0 | 🟢 CONCLUÍDA (10/11/2026) |
| 2 | Base de Dados e Modelos novos | Fase 1 | 🟢 CONCLUÍDA (10/11/2026) |
| 3 | Formulários Públicos (Livewire) | Fase 2 | 🟢 CONCLUÍDA (10/11/2026) |
| 4 | CMS Admin (Equipa PIGEA) | Fase 2 | 🟢 CONCLUÍDA (10/11/2026) |
| 5 | Frontend Público (Menu + Diretório) | Fase 2 | 🟢 CONCLUÍDA (10/11/2026) |
| 5.1 | Mega Menu (menu.md) | Fase 5 | 🟢 CONCLUÍDA (11/11/2026) |
| 6 | Publicidade (Banners) | Fase 2 | 🟢 CONCLUÍDA (11/11/2026) |
| 7 | Testes e Validação | Fases 3–6 | 🟢 CONCLUÍDA (11/11/2026) |
| 8 | Documentação e Limpeza Final | Fase 7 | 🟢 CONCLUÍDA (11/11/2026) |
| 9 | Remoção completa do sistema de comentários | Fase 8 | 🟢 CONCLUÍDA (11/11/2026) |
| 10 | Interface profissional do CMS (`cms_pro.md`) | Fase 9 | 🟢 CONCLUÍDA (11/11/2026) |

---

## FASE 0 — Preparação e Backup

**Objetivo:** Garantir segurança antes de destruir/apagar código e dados.

### Tarefas
- [x] Backup do banco SQLite atual (copiar `database/database.sqlite` para `backups/`).
- [x] Backup dos diretórios que serão afetados (`app/Models`, `app/Livewire`, `routes`, `resources/views`) — criar `backups/2026-11-10/`.
- [x] Documentar no `memoria.md` o estado de partida da reestruturação.
- [x] Instalar `spatie/laravel-medialibrary` via Composer (necessário na Fase 2).
- [x] Publicar a migration/config do Media Library.

### Critério de conclusão
Backups existentes e `spatie/laravel-medialibrary` instalado sem erros.

### ✅ Registro de execução (10/11/2026)
- Backup do banco: `backups/2026-11-10/database.sqlite` (909 KB).
- Backups de código: `app_Http`, `app_Livewire`, `app_Models`, `database_migrations`, `database_seeders`, `resources_views`, `routes` em `backups/2026-11-10/`.
- `spatie/laravel-medialibrary` v11.23.12 instalado (6 pacotes: spatie/image, spatie/image-optimizer, spatie/package-tools, spatie/temporary-directory, zipstream-php).
- PHP tem extensões necessárias: `fileinfo` e `gd` (conversão WebP OK).
- Migration publicada: `2026_11_10_104620_create_media_table.php` e executada (tabela `media` criada, 20 migrations no total).
- Config publicada: `config/media-library.php`.
- **Nota técnica:** o comando `vendor:publish` falha via `cmd /c` (problema de parsing de aspas aninhadas) — executar diretamente no PowerShell com `& "C:\xampp\php\php.exe" artisan ...`.
- **Nota:** a migration antiga `2026_09_29_000001_add_fulltext_indexes_to_content_tables` foi pulada no SQLite (não suporta FULLTEXT via schema builder) — não afeta a reestruturação.
- Site validado: `GET /` → 200 OK após as alterações.

---

## FASE 1 — Limpeza do Sistema

**Objetivo:** Apagar tudo relacionado a Vagas, Candidaturas e Painel de Empresa. Roles apenas `admin` e `editor`.
**Status:** ✅ CONCLUÍDA em 10/11/2026 — detalhes completos em `modificar.md` §8.

### 1.1 Modelos apagados
- [x] `app/Models/Job.php` (vagas — tabela `vacancies`)
- [x] `app/Models/Application.php` (candidaturas)
- [x] `app/Models/SavedJob.php`
- [x] `app/Models/CompanyProfile.php` (ficha privada da empresa)
- [x] `app/Models/Opportunity.php`
- [x] `app/Models/Notification.php` (notificações do painel de utilizador)

### 1.2 Controllers apagados
- [x] `Frontend/JobController.php` + `Painel/EmpresaController.php` + `Painel/UtilizadorController.php`
- [x] `Auth/CompanyRegisterController.php` + `Actions/Fortify/CreateNewUser.php` (sem registo público)

### 1.3 Componentes Livewire apagados
- [x] `app/Livewire/Painel/` (VagasEmpresa, MinhasCandidaturas, CandidaturasRecebidas)

### 1.4 Views apagadas
- [x] `jobs/`, `painel-empresa/`, `painel-utilizador/`, `admin/vagas.blade.php`, `auth/company-register.blade.php`, `auth/register.blade.php`
- [x] Componentes `sidebar-company.blade.php`, `sidebar-user.blade.php`

### 1.5 Rotas
- [x] Removidas `jobs.*`, `company.register*`, grupo `painel` (user), grupo `painel-empresa` (company), `admin.vagas`
- [x] Mantidas: home, news.*, search, about, contacts, auth (login/reset apenas), admin.*, /formacao, /diretorio, demais módulos de conteúdo

### 1.6 Roles e Utilizadores (Spatie)
- [x] RoleSeeder: apenas `admin` e `editor` (removidas `company`, `user` + permissões job/application/notification)
- [x] Fortify sem `Features::registration()`; auth-modal só com login; RoleBasedHomeResponse simplificado

### 1.7 Migrations
- [x] Migrations de painel_user/painel_empresa/company_type apagadas; `vacancies` removida das migrations de conteúdo
- [x] `migrate:fresh --seed` executado sem erros (17 migrations); FULLTEXT pula-se em SQLite e mantém-se em MySQL

### Critério de conclusão
✅ 16 páginas públicas 200 OK; rotas removidas 404; sem referências órfãs (grep verificado); roles corretas no banco.

---

## FASE 2 — Base de Dados e Modelos

**Objetivo:** Criar as novas tabelas e modelos conforme `restrutura.md` secção 3.
**Status:** ✅ CONCLUÍDA em 10/11/2026 — detalhes em `modificar.md` §9.

### 2.1 Migration `companies`
- [x] Campos: `id`, `nome_empresa`, `nif`, `contactos` (string), `areas_atuacao` (json), `redes_sociais` (json, opcional), `status` (enum: pendente/aprovado/rejeitado), `timestamps`

### 2.2 Migration `professionals`
- [x] Campos: `id`, `nome_completo`, `formacao_academica`, `contactos` (string), `areas_atuacao` (json), `status` (enum: pendente/aprovado/rejeitado), `timestamps`

### 2.3 Migration `directory_documents`
- [x] Campos: `id`, `titulo`, `descricao` (curta), `tipo` (enum: artigo/documento/lei), `link_download` (string), `areas_atuacao` (json), `timestamps`

### 2.4 Migration `banners`
- [x] Campos: `id`, `titulo`, `imagem_url` (string — upload ou link; Media Library fica só em News), `link_destino`, `posicao` (topo/sidebar/rodape), `data_inicio`, `data_fim`, `ativo` (boolean)

### 2.5 Atualização da tabela `news`
- [x] Adicionar `video_link` (string, nullable)
- [x] Adicionar `documento_link` (string, nullable)
- [x] Spatie Media Library: coleções `capa` (singleFile) e `contexto`, conversão WebP (testada com upload real)

### 2.6 Modelos
- [x] Criar `Company`, `Professional`, `DirectoryDocument`, `Banner` com `$fillable`, `$casts` (json/date/boolean) e scopes (`aprovado`, `pendente`, `rejeitado`, `ativos`, `posicao`, `porArea`)
- [x] Atualizar `News` com `HasMedia` (Spatie), coleções e conversão WebP
- [x] `DirectorySeeder` com dados de teste dos 3 estados + registado no DatabaseSeeder

### Critério de conclusão
✅ `migrate:fresh --seed` executa sem erros; scopes/casts validados por script; upload WebP real testado; páginas 200 OK.

---

## FASE 3 — Formulários Públicos (Livewire)

**Objetivo:** Dois formulários públicos sem login, com validação e mensagem de sucesso.
**Status:** ✅ CONCLUÍDA em 10/11/2026 — detalhes em `modificar.md` §10.

### 3.1 Registo de Empresa — rota `/registo-empresa`
- [x] Componente Livewire `RegistoEmpresa`
- [x] Campos: Nome da Empresa, NIF, Contacto, Áreas de Atuação (checkboxes → JSON), Redes Sociais (opcional)
- [x] Guardar com `status = 'pendente'`
- [x] Mensagem: "Registo submetido com sucesso. Aguarde a aprovação da nossa equipa."
- [x] View Blade pública (layout app)

### 3.2 Registo de Profissional — rota `/registo-profissional`
- [x] Componente Livewire `RegistoProfissional`
- [x] Campos: Nome Completo, Formação Académica, Contacto, Áreas de Atuação (checkboxes → JSON)
- [x] Guardar com `status = 'pendente'`
- [x] Mensagem de sucesso equivalente

### Critério de conclusão
✅ Formulários validam, gravam com status pendente e exibem mensagem de sucesso — validado via `Livewire::test` (submissão válida, NIF duplicado rejeitado, whitelist de áreas, sem áreas). Páginas 200 OK e 8 CTAs atualizados.

---

## FASE 4 — CMS Admin (Equipa PIGEA)

**Objetivo:** Painel `/admin` completo para a equipa interna.
**Status:** ✅ CONCLUÍDA em 10/11/2026 — detalhes em `modificar.md` §11.

### 4.1 Dashboard
- [x] Contadores: Notícias publicadas, Empresas pendentes, Profissionais pendentes (+ Utilizadores, Conteúdo pendente)

### 4.2 Notícias (CRUD completo)
- [x] Upload de capa (Spatie Media, coleção `capa`, WebP) + coluna legada `image` para compatibilidade
- [x] Upload de imagens de contexto (coleção `contexto`, até 10)
- [x] Campos `video_link` e `documento_link` (apenas link) — criação e edição

### 4.3 Diretório de Empresas
- [x] Tabela com status (pendente/aprovado/rejeitado) — componente CompaniesTable (Company)
- [x] Botões "Aprovar" e "Rejeitar" (+ voltar a pendente, eliminar)
- [x] Aprovadas ficam visíveis na página pública (Fase 5 — scope `aprovado`)

### 4.4 Diretório de Profissionais
- [x] Tabela com botões "Aprovar" e "Rejeitar" — componente ProfessionalsTable

### 4.5 Documentos/Artigos
- [x] CRUD com tipo (artigo/documento/lei), link de download, áreas (whitelist) — DocumentFormController + DocumentsTable

### 4.6 Publicidade
- [x] CRUD de banners: upload ou link de imagem, link destino, posição, datas início/fim, ativo — BannerFormController + BannersTable

### 4.7 Utilizadores
- [x] Gerir equipa interna (apenas admin/editor) — já operacional de fases anteriores

### Critério de conclusão
✅ Todas as secções do admin operacionais com permissões corretas (admin/editor apenas) — validado por script funcional + navegação HTTP autenticada (10 páginas 200 OK).

---

## FASE 5 — Frontend Público

**Objetivo:** Novo menu e página de diretório.

### 5.1 Novo Menu (conforme imagem do cliente)
Ordem: **Início, Notícias, Mineração, Petróleo e Gás, Geociências, Academia, Oportunidades, Directório, Sobre Nós**
- [x] Atualizar `main-menu.blade.php`
- [x] Definir destino de cada item (páginas/categorias)
- [x] Ajustar lógica de item ativo

### 5.2 Página do Diretório `/directorio`
- [x] Duas abas: **"Empresas"** e **"Artigos/Documentos"**
- [x] Aba Empresas: apenas `status = 'aprovado'` — Nome, Áreas de Atuação, Contactos públicos, Redes Sociais. **Ocultar NIF**
- [x] Aba Artigos: documentos da equipa com botão "Download"
- [x] Barra de pesquisa (filtrar por nome ou área de atuação)
- [x] Links para os formulários públicos de registo

### 5.3 Links dos formulários
- [x] Colocar `/registo-empresa` e `/registo-profissional` acessíveis no site (menu/rodapé/página diretório)

### Critério de conclusão
✅ **CONCLUÍDA (10/11/2026)** — Menu novo funcional (9 itens, act-link validado); diretório com abas, busca e apenas aprovados visíveis (NIF ausente do HTML; pendente/rejeitada invisíveis; 15+ páginas 200; /diretorio antigo → 301). Detalhes em `modificar.md` §12.

---

## FASE 5.1 — Mega Menu (de `menu.md`, enquadramento aprovado pelo cliente — 11/11/2026)

**Objetivo:** Substituir o menu plano por um Mega Menu estruturado (barra branca, 9 itens centrados, painéis de subcategorias) e criar as categorias de menu na BD.

- [x] Migration: `categories` + `parent_id` (nullable), `menu_group` (nullable, index), `order` (unsignedInteger nullable)
- [x] `MenuSeeder`: 44 subcategorias com os nomes EXATOS do cliente nas 9 secções (Início 4, Notícias 4, Mineração 5, Petróleo e Gás 5, Geociências 7, Academia 5, Oportunidades 5, Directório 4, Sobre Nós 5)
- [x] Grupos Mineração/Petróleo/Geociências → `type='news'` (filtráveis via `?cat=` e no select de notícias do admin; `parent_id` → categoria de notícias do grupo); restantes → `type='menu'` (inertes nos selects)
- [x] `App\Support\MenuCategorias`: 9 secções + sublinks + mapa de destinos (documentado como ajustável) + `grupoAtivo()`
- [x] `main-menu.blade.php` reescrito: barra branca, chevron-down, item ativo `#e6f0ff`, painel full-width com grelha 3 colunas de links (SEM cabeçalho descritivo nem badge — removidos a pedido do cliente)
- [x] CSS novo (`public/css/menu.css`) com 3 regimes: barra única ≥1280px, segunda linha 1065-1279px, acordeão ≤1064px (hamburger do tema mantido, clone `.menusb` escondido)
- [x] JS próprio (`public/js/menu.js`, `defer`): acordeão mobile por delegação; desktop 100% CSS (hover + focus-within)
- [x] Testes: 15 novos (`MegaMenuTest`) — seeder (44/grupos/nomes/ordem/pai/tipos/idempotência), destinos, renderização (9 painéis, 44 links, ativo), subcategoria acende o grupo, select de notícias do admin recebe type=news e ignora type=menu

### Ajustes pedidos pelo cliente (11/11/2026, após validação visual)
- [x] Removida a descrição do menu (cabeçalho do painel: título + link "Subcategorias") — `mm-panel-cab`/`mm-panel-titulo`/`mm-panel-subcat`
- [x] Removido o ícone de utilizador do `header.blade.php` (variantes autenticado e visitante) — o login leva a equipa direto a `/admin` (RoleBasedHomeResponse), logo ninguém fica preso ao painel
- [x] Removida a badge "Portal de Geociências" (`mm-badge`) — menu.md §2 badge abandonado por decisão do cliente
- [x] **Top-bar**: ícone de pesquisa (não visível, tapado pela barra full-width do menu) removido do `header-inner` → recolocado na top-bar à **direita**, junto à data/hora (`topbar-direita`); o **centro** passa a ser a **área de publicidade (banner horizontal)** (`.top-banner-area` envolvendo o componente `<x-banner-publicitario posicao="topo" />`, limitada à altura da barra); **redes sociais à esquerda** (alinhadas com o rodapé: Facebook/X/Instagram/LinkedIn/YouTube; links `#` à espera dos URLs do cliente — gestão já existe nas Configurações do admin)
- [x] Novo `public/css/topo.css` (carregado depois de `menu.css` no layout): banner centrado ignorando floats, grupo direito flex, ícone visível em fundo escuro, breakpoints 1279/1064/700/630px
- [x] `MegaMenuTest` atualizado (painel = grelha pura; sem badge)

### Critério de conclusão
✅ **CONCLUÍDA (11/11/2026)** — Suíte REAL verde e revalidada: **145 testes / 534 asserções** (`phpunit` contra MySQL `pigea_test`). Durante a revalidação a BD de teste mostrou-se desalinhada (seed antiga/parcial: pais `petroleo-e-gas`/`geociencias` type=news em falta; permissões `document.manage`/`banner.manage` ausentes) → BD reconstruída com `migrate:fresh --seed` e `tests/bootstrap.php` corrigido para detetar estas permissões pelo nome (contagem antiga `<18` não apanhava a BD velha). MySQL do XAMPP avariado no arranque (privilégios `mysql.db`/`global_priv`/`tables_priv`/`columns_priv`/`procs_priv` marcados crashed — `"Can't open and lock privilege tables"`) reparado offline com `aria_chk` (`columns_priv` exigiu `-o -f`); mysqld arranca e valida privilégios; MySQL parado no fim. 18 páginas públicas 200 com menu presente (login tem layout próprio, sem menu — esperado). `/noticias?cat=geologia` acende "Geociências". Top-bar validada via HTTP (ordem social→banner→data/hora+pesquisa, ícone único, overlay intacto). Screenshots não capturados (sem tooling de browser) — passagem visual manual recomendada. Detalhes em `modificar.md` §15.

---

## FASE 6 — Publicidade (Banners)

**Objetivo:** Renderizar banners geridos no admin, integrando com o trabalho já feito no header.

- [x] Componente/render helper que lê banners ativos por posição e período (data_inicio/data_fim)
- [x] Posições: `topo`, `sidebar`, `rodape`
- [x] Substituir o `banner-placeholder` do `header.blade.php` pelo banner real (posição `topo`)
- [x] Banner estático (imagem + link) — slider fica para depois (ver `pub.md` para slider futuro)
- [x] Remover/corrigir JS órfão do easyTicker em `scripts.js`

### Critério de conclusão
✅ **CONCLUÍDA (11/11/2026)** — Componente Blade `<x-banner-publicitario posicao="…" />` renderiza o banner exibível mais recente por posição/período; header topo com banner real (placeholder removido); sidebar nas notícias; rodapé pronto; sem link → não clicável; easyTicker órfão removido (`.picker-wrap` mantido). Validação: 13/13 script + 10 páginas 200. Detalhes em `modificar.md` §13.

---

## FASE 7 — Testes e Validação

- [x] Testes das novas migrations/modelos
- [x] Testes dos formulários públicos (validação, gravação pendente)
- [x] Testes de aprovação/rejeição no admin
- [x] Teste de visibilidade pública (apenas aprovados, NIF oculto)
- [x] Teste dos banners (período ativo/expirado, posição)
- [x] Navegação completa no frontend sem erros (todas as rotas 200)
- [ ] Screenshots de evidência (evidence-collector) — ⚠️ pendente manual: sem tooling de browser no ambiente; evidência substituída por HTML renderizado + asserções

### Critério de conclusão
✅ **CONCLUÍDA (11/11/2026)** — Suíte REAL verde: **130 testes, 447 asserções** (phpunit contra MySQL `pigea_test`; MySQL do XAMPP arrancado ad hoc e parado no fim). 43 testes novos da reestruturação (modelos/scopes/SoftDeletes, formulários públicos, moderação+permissões, visibilidade/NIF, banners período/posição). 3 bugs reais corrigidos (banner sem imagem; JSON escapado → busca por área; editor bloqueado de /admin). 14 páginas públicas 200 + redirect 301. Screenshots não capturados (sem tooling de browser — evidência = HTML renderizado + asserções; captura manual recomendada). Detalhes em `modificar.md` §14.

---

## FASE 8 — Documentação e Limpeza Final

- [x] Atualizar `memoria.md` (contexto final) — v2.3 (11/11/2026); v2.4 no fecho do gate
- [x] Atualizar `resumo.md` (novo paradigma do sistema) — reescrito 11/11/2026
- [x] Atualizar `modificar.md` (todas as alterações de código) — §15 + §16 (Fase 8)
- [x] Atualizar/eliminar `pub.md` — reescrito para o sistema implementado (banners únicos; spec antigo com `banner_slides`/Livewire era obsoleto)
- [x] Remover código morto restante — componente `auth-modal` (modal de login do tema sem trigger; porta única `/login`) + CSS/JS do modal; bloco CSS do ticker `.header_news-ticker-wrap`/`.hnt_title`/`.n_contr-wrap`/`.n_contr`/`.p_btn` (57 linhas); seletores `.srf_btn`; `scripts.js` (`show-reg-form`/`showModal`/`hideModal`/`close-reg-form`); `.header_news-ticker-wrap` removido do seletor ≤1064 (`.aside-panel` mantido); suíte revalidada GREEN 145/534 após remoção
- [x] `reality-checker` como gate final de qualidade — 1.ª passagem: NEEDS WORK (P0 `dashboard.js`); correções; 2.ª passagem: **PASS COM RESERVAS** (UAT, nunca produção nesta passagem)

### Critério de conclusão
✅ **CONCLUÍDA (11/11/2026)** — Documentação coerente com o novo sistema (`resumo.md`, `pub.md` reescritos; `memoria.md` v2.4; `fase_all.md` cabeçalho/tabela corrigidos); **código morto removido** (auth-modal, ticker CSS, `.srf_btn`, **P0 `dashboard.js`** reescrito; blocos `#contactform`/`#registoform`/Mailchimp removidos na 2.ª passagem); anti-spam de servidor nos registos públicos (`RateLimiter`); suíte **OK 149/545**; PHPUnit cache de resultado limpo; MySQL parado no fim. **Gate final: PASS COM RESERVAS** — a entrega é **UAT, nunca produção nesta passagem**: falta validação visual em browser (screenshots), SMTP funcional, endurecer CSP para enforcement, `ADMIN_PATH` não-óbvio e decisões do cliente listadas em `modificar.md` §16. Lista completa de reservas e próximos passos em `modificar.md` §16.

---

## FASE 9 — Remoção completa do sistema de comentários (`comentarios.md`, 11/11/2026)

- [x] Levantamento: não existia tabela `comments`/Model `Comment`/componente Livewire nem UI no admin — apenas contador `comments_count` + HTML estático do tema
- [x] Frontend: 38 linhas `fa-comments-alt` removidas de 19 views; secções estáticas removidas em `news/show` e `projects/show` (incluindo tooltip "Escrever um comentário"/"Reportar" morto); opção "Mais Comentados" removida dos filtros
- [x] Backend/BD: migration `2026_11_11_000001_drop_comments_count_from_content_tables` (10 tabelas) + remoção da coluna na criação; 10 modelos sem `comments_count`; seeder/filtros/ContentFormController atualizados; JSON de seed limpo (104 entradas); `lang/pt/actions.php` sem chave `comment`
- [x] Validação: HTTP 200 em 10 rotas dev; páginas show sem secção de comentários; **suíte OK 149/545**; MySQL reparado/parado no fim; dev SQLite sem a coluna

### Critério de conclusão
✅ **CONCLUÍDA (11/11/2026)** — sistema de comentários removido por completo (frontend, BD, CMS). Duas ocorrências documentadas em `modificar.md` §17: (1) BOM acrescentado a 39 ficheiros pelo fluxo de escrita automática — corrigido e verificado (`php -l`, JSON válido, 0 BOM); (2) tabelas de privilégios MySQL danificadas de novo pelo crash cíclico desta máquina — restauradas server-side; **atenção: o MySQL do XAMPP desta máquina falha sozinho periodicamente**. Detalhes: `modificar.md` §17.

---

## FASE 10 — Interface profissional do CMS (`cms_pro.md`, 11/11/2026)

- [x] TinyMCE self-hosted via npm → `public/js/tinymce/` (7.6MB; CSP `script-src 'self'` respeitado)
- [x] Contador real de visitas: migration `2026_11_11_000200` + model `PageView` + middleware Terminable `CountPublicVisits` (grupo web; ignora `/admin` via `ADMIN_PATH` dinâmico, ≠200, bots e método ≠GET; sem dados pessoais; corre após a resposta)
- [x] Dashboard redesenhado: 4 cartões (Notícias Publicadas · Empresas Pendentes→aprovação · Visitas no Site · Banners Ativos) + ação rápida "+ Nova Notícia" + gráfico central linha das visitas dos últimos 7 dias (Chart.js)
- [x] Sidebar reorganizada em 5 grupos (PRINCIPAL/PRODUÇÃO/DIRETÓRIO/COMERCIAL/SISTEMA) com **Sistema só para `admin`**; ocultação por papel (cosmética — proteção real continua no middleware)
- [x] Topbar: botão **"Ver site"** (`target=_blank`) + sino com **badge real** (Empresas+Profissionais pendentes; oculto a 0)
- [x] Editor de Notícias como componente Livewire full-page (criar/editar): layout 2 colunas 70/30, TinyMCE (Negrito/Itálico/Listas/Links/Imagem) + Drop Cap/Blockquote/Duas Colunas, ações Pré-visualizar (modal sem gravar)/Guardar rascunho/Publicar, **autosave com debounce 5s**, pré-visualização renderiza o estado atual sem persistir
- [x] Help texts de imagem: capa 1200x675px (16:9) 2MB · contexto 800px · banners 970x250px (máx 4MB) · logótipo 400x100px transparente
- [x] Help total: suíte **OK 159/572** (10 testes novos/migrados: NewsEditorTest, VisitsCounterTest, AdminFormsTest, AdminContentFormTest) — MySQL limpo e parado no fim

### Critério de conclusão
✅ **CONCLUÍDA (11/11/2026)** — CMS profissional implementado e validado: HTTP ao vivo (login real → todas as páginas admin 200 com conteúdo correto); suíte 159/572 verde; nenhuma regressão no frontend público. Nota: `ADMIN_PATH` foi alterado pelo cliente para `gestao-pigea` em algum momento do dia (a app funciona com o novo prefixo sem intervenção). Decisões do spec registadas em `modificar.md` §18 — confirmar com a equipa a exclusão de Projetos/Eventos/Legislação/Investigação/Entrevistas do menu (saiu pela decisão "Exatamente como no spec (9 itens)" — as páginas continuam reachable por URL). Detalhes e validações em `modificar.md` §18.

---

## FASE 10.1 — Editor de notícias em Blade tradicional (11/11/2026)

**Motivação:** o componente Livewire full-page `NewsEditor` destruía o DOM do TinyMCE (iframe/toolbar própria) por causa do morph incremental do Livewire — editor apagava-se no clique. Decisão: **retroceder o editor para Blade tradicional com `<form method="POST">`** (sem Livewire), mantendo o TinyMCE self-hosted e a pré-visualização em modal vanilla.

- [x] `NewsEditor.php` (Livewire) e `NewsEditorTest.php` apagados; `NewsEditorController.php` criado (create/edit/store/update; validação PT-PT; uploads capa/contexto via Spatie Media; slug único; `published_at`/`date_label` preservados em edição parcial)
- [x] Rotas: `/gestao-pigea/noticias/nova` (GET create / POST store) e `/gestao-pigea/noticias/{noticia}/editar` (GET) + PUT update — nomes com prefixo `admin.` (`admin.noticia-editor.store/.update`); editor próprio ganha por precedência de rota sobre `{tipo}/{id}` genérico
- [x] Vista `admin/noticia-editor.blade.php` reescrita em Blade puro (`<form>`, CSRF + `@method('PUT')`, `old()`/`@error`, pré-visualização em modal HTML/JS sem gravar)
- [x] Guarda obrigatória para POSTs parciais em edição: campo ausente mantém o valor; presente-mas-vazio limpa (excerto/conteúdo/categoria/links/data preservados)
- [x] Fluxo de sucesso: **Guardar rascunho → fica no editor** já aberto no registo recém-criado (flash `estado`); **Publicar → lista** com flash "Notícia publicada!" (a lista renderiza-a)
- [x] Acessibilidade WCAG (auditor): foco no contentor do modal coberto no trap de Tab (2.4.3); foco no primeiro campo com erro (TinyMCE incluído via `ed.focus()`); ícones `aria-hidden`; `*` do título via span; `aria-describedby` erros+hints; alvo × ≥24px; reflow da capa (`max-width`); contraste corrigido (`.news-preview-meta` #666, placeholder #6E6E6E, breadcrumb `›` #767676, `.status-badge.active` #256628)
- [x] UI Finish Gate: **PASS estático** (após correções): modal migrado para a convenção do painel (`.dashboard-modal`/`.open`/`js-modal-open/close`, foco devolvido ao trigger pelos 4 caminhos de fecho); classes mortas removidas; `capa-atual` por classe CSS; cópia de uploads unificada; 'Letra capitular'; object URL revogado no fecho; duplo-clique bloqueado; flash vivo num único sítio (lista para publicar, editor para rascunho)
- [x] Testes migrados para POST tradicional (`AdminFormsTest`, `AdminContentFormTest`); cobertura nova: rascunho redireciona ao editor + flash; publicar mostra flash na lista; **suíte 156/156 OK, 567 assertions**
- [ ] **Pendente (requer browser real — bloqueante do release, não do merge):** publicar/guardar com cliques reais; modal completo pelos 4 caminhos (×, rodapé, Escape, backdrop) com devolução de foco; erro de validação com foco no 1.º campo; 390px e 400% zoom; iframe do TinyMCE anunciado; skip link "Saltar para o conteúdo" no layout admin (2.4.1, vale para TODO o painel); landmarks `complementary` nomeados na sidebar
- Nota de rejeição fundamentada: remover `@livewireScripts` do layout (finish-gate #17) **não se fez** — o painel admin continua a renderizar componentes Livewire (ContentTable, UsersTable, CompaniesTable, ProfessionalsTable, DocumentsTable, BannersTable); removê-lo partiria essas páginas.
- MySQL do XAMPP continua a crashar sozinho (privileged Aria tables) — reparos/offline com `aria_chk` ou `ALTER TABLE … FORCE` server-side.

### Critério de conclusão
Fase concluída a 11/11/2026 no plano estático (suíte verde + gates PASS em código). A conformidade AA do ecrã só se declara após a verificação em browser listada acima; a entrega ao cliente acontece em UAT, nunca em produção nesta fase.

### Regra de trabalho (11/11/2026)
**Commit e push apenas com permissão explícita do utilizador, caso a caso.** Nunca commit nem push sem pedido do utilizador. O estado em curso fica no working tree.

### Ops — MySQL Server 8.0.46 (a instância atual do sistema; 11/11/2026)

**Serviço e instalação**
- `MySQL80` (Windows Service, `Running`, auto-start), pid 7624, porta **3306**, bind `::` (todas), instalação em `C:\Program Files\MySQL\MySQL Server 8.0\` (inclui cliente oficial + Workbench 8.0 em `C:\Program Files\MySQL\MySQL Workbench 8.0\`).
- Charset: bases criadas `utf8mb4` (`pigea`, `pigea_test`, `utf8mb4_unicode_ci`).
- Instalado pelo cliente em 11/11/2026; a porta foi mudada pelo cliente de 3307 → **3306** ainda no mesmo dia; a instância anterior do XAMPP (MariaDB, 3306 partilhado) deixou de servir a porta.
- Backup do sqlite antigo: `%TEMP%\opencode\database-sqlite-backup-20261111.sqlite` — **local temporário, pode ser apagado pelo sistema**; copia para destino definitivo se quiseres manter o histórico (ex.: `pigea-deploy/database/backups/` ou disco externo).

**Contas**
- `root@localhost` — senha fraca `devs` (escolha do cliente, risco registado). Apenas administração: criar/manter utilizadores, grants, reparar/fazer refresh de schemas. **Não é usado pela app nem pela suíte.**
- `pigea@localhost` e `pigea@127.0.0.1` — conta dedicada para app/testes. Grants: `ALL PRIVILEGES` **apenas** em `pigea.*` e `pigea_test.*`; sem acesso global (verificado: `SELECT` em `mysql.user` negado). Password forte gerada localmente (48 hex), **apenas** no `.env` local (gitignored).
- `mysql.infoschema`, `mysql.session`, `mysql.sys` — contas de sistema (default do instalador).

**Uso por ambiente**
- Dev (`artisan serve`/porta 8000 e `php artisan` em geral): ligação via `.env` LOCAL — `DB_CONNECTION=mysql`, `DB_DATABASE`… = `pigea`.
- Suíte (`php vendor/bin/phpunit`): `phpunit.xml` define `DB_CONNECTION=mysql`, `DB_DATABASE=pigea_test`, `DB_PORT=3306` (**esta última linha está no working tree, não comitada — para o PR/UAT**); credenciais lidas pela suíte do `.env` local como fallback (`DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_USERNAME=pigea`). **Os testes nunca tocarão `pigea` dev** (fail-closed em `tests/bootstrap.php` exige `pigea_test`).
- `.env` continua **gitignored**; nunca versionar credenciais. `phpunit.xml` é commitável porque não tem password.

**Como operar (cheatsheet)**
- Estado/restart do serviço: `Get-Service MySQL80` · `Restart-Service MySQL80` (pede admin). Se o serviço parar, **a app e a suíte caem** (dev `pigea` / testes `pigea_test` no mesmo processo).
- Cliente oficial para qualquer SQL manual: `& "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u root -p` (nunca `--password=` inline; nunca o cliente MariaDB do XAMPP — não carrega `caching_sha2_password`).
- Workbench: `C:\Program Files\MySQL\MySQL Workbench 8.0\MySQLWorkbench.exe`.
- Backup (sugestão, não implementada): `mysqldump --single-transaction pigea > pigea_YYYYMMDD.sql` + tarefa agendada. Não foi feito por falta de OK explícito; se quiseres, peço a aprovação e configuro Task Scheduler.
- Ver contas/grants: `SHOW GRANTS FOR 'pigea'@'localhost';` (antes de mexer em nada).
- Índices/checks de saúde: `SHOW GLOBAL STATUS; SHOW ENGINE INNODB STATUS\G` no cliente oficial.

**O que NÃO fazer**
- Não mudar a porta sem coordenar com app+testes (atualizar `phpunit.xml`/`.env` no mesmo instante; e verificar `%TEMP%` backup do sqlite não se perde se algo correr mal).
- Não commitar `.env` nem substituir a password do `pigea` por algo trivial.
- Não apontar a app para `pigea_test` (os testes usam transações com rollback; dados de dev seriam perdidos a cada run em caso de falha/higiene do bootstrap).
- Não desinstalar o XAMPP antes de ter PHP próprio: `php` ainda vem de `C:\xampp\php\php.exe`; o dev-server depende dele.

### Ambiente de testes migrado para MySQL 8.0.46 (11/11/2026, tarde)
- O cliente instalou o **MySQL Server 8.0.46** como serviço `MySQL80` (charset utf8mb4). Porta final: **3306** (inicialmente 3307; o cliente trocou).
- XAMPP/MariaDB (antigo 3306) já não é usado pela suíte — foi substituído por completo (a instância XAMPP estava instável, com crashes periódicos de tabelas Aria privilegiadas).
- `pigea_test` criada na 3306 (utf8mb4/unicode_ci); suíte completa **OK (156 testes, 567 assertions)** contra a instância nova (migrações+seed corridos pelo bootstrap do `tests/`).
- Alterações no working tree (não commitadas): `phpunit.xml` ganha `<env name="DB_PORT" value="3306"/>` (para o teu PR/UAT) e `.env` (local, gitignored) ganha `DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD` **apenas como fallback de credenciais da suíte** — a app dev continua em **sqlite** (DB_CONNECTION intacto).
- A password do `root` da nova instância está apenas no `.env` local (gitignored); phpunit.xml **não** tem password.
- **Utilizador dedicado `pigea`@localhost e `pigea`@127.0.0.1 criado em 11/11/2026** (password forte de 48 hex, gerada localmente, persistida apenas no `.env` local gitignored): `GRANT ALL PRIVILEGES ON pigea.*` + `ON pigea_test.*` — sem qualquer acesso ao resto (`mysql.*` incluído foi verificado como negado). `.env` atualizado (`DB_USERNAME=pigea`); phpunit.xml continua sem credenciais. **Suíte OK (156 tests / 567 assertions)** com a conta nova. `root/devs` continua para administração do servidor (criar utilizadores, grants, operações de manutenção) — não é usado pela app nem pelos testes.
- Cliente oficial MySQL 8.0 (`C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe`) é necessário para comandos SQL — o cliente MariaDB do XAMPP não carrega o plugin `caching_sha2_password`.
- **Aplicação dev também migrada para MySQL (11/11/2026, noite):** `.env` passa a `DB_CONNECTION=mysql`, `DB_DATABASE=pigea` (a instância nova; sqlite mantido só como backup local e intacto — cópia em `…\Temp\opencode\database-sqlite-backup-20261111.sqlite`). `pigea` migrada+semeada via `migrate:fresh --seed`; `page_views` real (2026-11-11, 1 visita) restaurada. App e suíte partilham a instância, **bases separadas** (dev `pigea` / testes `pigea_test`).

---

## FASE 11 — Melhoria do Editor de Notícias e do CMS (`cms_v.md`, 11/11/2026)

**Status:** concluída a nível de código (Fases 1–8 do spec) e de suíte de testes — **NÃO validada visualmente nem em browser**; entrega a UAT, nunca a produção.

- [x] **Fase 1:** `public/css/article.css` com escopo `.article-body`; parcial `news/partials/article-body.blade.php`; show público + preview do admin usam o mesmo parcial.
- [x] **Fase 2:** TinyMCE completo (toolbar agrupada, `styles` «Estilos do PIGEA»: drop-cap / pull-quote / narrow / two-cols / media-left / media-right; `block_formats` P/H2/H3; `menubar` reduzido; `promotion:false`; height 520+autoresize; `content_css` + `body_class`); `config/article.php` (lista única usada pelo TinyMCE e pelo servidor); JS próprio `public/js/admin/news-editor.js`.
- [x] **Fase 3:** `ezyang/htmlpurifier` instalado (v4.19); `ArticleSanitizer::clean()` aplicado ao guardar + na pré-visualização; whitelist via `config/article.php`; `target=_blank` com `rel="noopener noreferrer"`; testes cobrindo remoção de `<script>`/on*/`style`/`javascript:`/`iframe` e preservação de classes PIGEA e `alt`.
- [x] **Fase 4:** POST `{ADMIN_PATH}/noticias/imagem` — upload local (GD), resize ≤1200px, WebP q85, throttle 20/min; JSON `{location}`; reda direção progressiva de colagem (nada de imagens incorporadas na spec: apenas via upload).
- [x] **Fase 5:** botões hierárquicos (Publicar primary / Guardar secondary / Pré-visualizar ghost); sticky; breadcrumb 1 linha; skip link; campo de ficheiro em PT (drop-zone + preview + remover); toast global (substituída o flash simples); confirm-modal genérico; empty-state; loading em submits; nome+papel no topbar; logout único no topbar.
- [x] **Fase 6:** slug editável único (check AJAX); resumo com contador; etiquetas; **painel SEO recolhível** (counters 60/160); alt+crédito da capa (migration aditiva `image_alt`); fluxos **Rascunho → Em revisão → Publicado** com config `EDITOR_PODE_PUBLICAR` (default **true**); «Publicar» vs «Submeter para revisão» coerente por papel; badge de estado no editor; card-actions.
- [x] **Fase 7:** `NewsListTable` — título/categoria/autor/estado/data/visitas/ações; filtros estado/categoria/autor + busca; ordenação por coluna; **duplicar** (rascunho + slug único); **apagar para o lixo** e **restaurar** (SoftDeletes); empty-state; paginação rubricada.
- [x] **Fase 8 (opcionais):** 8.1 **autosave** (debounce 15s, fetch POST JSON) sem Livewire; 8.2 **agendamento** — `published_at` `date`→`datetime` (SQL direto, sem doutrine/dbal); comando `news:publish-scheduled` + agendado no Kernel; campo «Publicar em» (datetime-local); artigos futuros ficam invisíveis até à data.
- [x] **Migrations seguras**: `image_alt` (aditiva), `published_at` datetime (SQL direto). Backups pré-migration: `backups/pigea_20261111_200133.sql` (992 KB) e `pigea_test_20261111_200133.sql` (968 KB).
- [x] **Ambiente:** dev passou de sqlite para **MySQL 8.0.46** (instância de sistema, porta 3306); suíte 181/181 contra `pigea_test`; conta `pigea`@localhost/127.0.0.1 com ALL só em `pigea.*`/`pigea_test.*`; password forte no `.env` local (gitignored); phpunit.xml ganha `DB_PORT=3306` (working tree, sem commit).
- [x] **Testes:** OK (181 tests, 631 assertions) — substituiu a suite anterior (156/567), com ~25 novos testes das fases 3-8.

### Critério de conclusão
"Concluída" **NO CÓDIGO e NA SUITE** — **NÃO** no browser. Validação manual do Jorge é obrigatória em `verificar_cms.md` (inclui E1–E5 de AA) antes de qualquer deploy. 8.1 (autosave) desativado apenas em criação (registo inexistente); 8.2 (agendamento) exige cron/Task Scheduler externo (`php artisan schedule:run` a cada minuto) — não configurado sem OK.

---

## FASE 11B — Recursos extras no editor de notícias (11/11/2026)

Extensão do cms_v.md a pedido do cliente:

- [x] **Link genérico** além do vídeo/documento: novo campo `link` (URL externa) — migration `add_link_to_news_table`; gravado no `NewsEditorController` e no fillable; beneficiado pelo mesmo broadcast de sanitização (URL validada `url:http,https`).
- [x] **Capa por link** (em vez de upload): radio «Ficheiro (upload)» vs «Link (URL)»; se o utilizador não tem o ficheiro, cola o URL direto (JPG/PNG/WebP); o URL absoluto é guardado como `image`; mutual exclusive (ficheiro OU link — validação server-side explícita em `validar()`).
- [x] **Imagens por URL no site público**: helper global `image_url()` (carregado via composer autoload `app/helpers.php`), substituídas as 56 ocorrências de `asset($item->image)`/`asset($noticia->image)` etc. em 16 views por `image_url()` — URLs absolutas são servidas como estão; locais via `asset()`.
- [x] **Categorias por hierarquia** (select): optgroup por pai (Mineração / Petróleo e Gás / Geociências) com opção «nome (geral)» + subcategorias; mesmos grupos e filhos derivados de `parent_id` — proibido agora engolir tudo como «notícias» (opção plana na UI). Também aplicável ao filtro da lista de notícias.
- [x] **Categoria «Outros»**: migration `add_outros_category_to_news` (type=news, slug=outros, order=999) — aparece automaticamente na lista hierárquica; reserva para artigos que não cabem em nenhuma subcategoria.
- [x] **Testes extras** (`NoticiaEditorExtrasTest`): link genérico gravado; capa via URL; ficheiro+link juntos → erro validação; categoria Outros gravada — suíte **OK (185 tests, 643 assertions)**.

### Critério de conclusão
"Concluída" no nível de código e da suíte; **não validada visualmente** (browser) — a pré-visualização com capa URL não testada em interação real; o select de categorias com optgroups tem fallback adequado (todos os `type=news` ainda aparecem). Ver `verificar_cms.md`.

---

## FASE 12 — Continuação do roadmap de agendamento/autosave (11/11/2026)

**Motivação:** a Fase 11 tinha ficado com `news:publish-scheduled` criado mas não agendado no sistema, e autosave só funcionava em registos existentes.

- [x] **PublishScheduled command test** — `PublishScheduledTest` — 2 cenários (publicada já vencida recebe `date_label`; futura não recebe). Suite permanece verde.
- [x] **Publicação agendada ao minuto** — Scheduled Task Windows `PIGEA-schedule-run` criada (`schtasks /create /sc minute /mo 1`) → wrapper `storage\schedule-run.cmd` (apontado em `storage/schedule-run.cmd` via .gitignore). Verificação: `Last Result: 0` (sucesso); `schedule:list` confirma `*/1 * * * * news:publish-scheduled`. Burn-first test `--dry-run` devolve 0 quando não há publicação pendente.
- [x] **Autosave para artigos novos** — `POST /noticias/autosave` agora aceita ausência de `id`: cria rascunho, devolve `{ok:true, id:N}` e o JS faz:
  - guarda o ID no atributo `data-id` do form,
  - muda `form.action` para PUT update,
  - injeta hidden `_method=PUT` automaticamente.
- [x] **Writer editor preserva `id` durante autosave-allocation** — comportamento verificado via `NoticiaAutosaveTest` (4 testes, incluindo criação sem ID).
- [ ] Ainda por desenvolver: validação automática da cache/chute `cache: clear` antes da cada execução (a lógica `withoutOverlapping` usa cache lock — já funciona via `CACHE_STORE=database`); cross-browser human-check; screenshots pendentes.

### Critério de conclusão
"Implementado em código + suíte verde + scheduled task ativa. Não verificado visualmente em browser (ver `verificar_cms.md`). Pendente: agendamento em produção usa outro runner (cron Linux) — documentado em `modificar.md §20.8`.

---

## FASE 13 — Dashboard admin: gráficos quebram, sino vira modal, atividade clicável (11/11/2026)

**Pedido do cliente:** gráficos vazando do card; sino sem modal; atividade recente toda estática.

- [x] **Gráficos não vazam mais** de `.chart-box` — canvas agora tem `display:block; width:100%; height:220px/240px`; grid em 2 colunas; principal spans full-width (`grid-column: 1/-1`); `overflow:hidden` nas caixas; quick-action title sizing. Nenhuma regressão visual assumida sem `browser-verify`.
- [x] **Sino do topbar → modal** — `dashboard-header.blade.php` passa de `<a href="/empresas">` para `<button class="js-modal-open" data-modal="notificacoes-modal">`; modal larga-se numa única zona na layout (`layouts/dashboard.blade.php`) com a mesma lista de `\App\Support\NotificacoesPainel::obter()` usada também no dashboard.
- [x] **"Atividade recente" passou a ser clicável** — cada item aponta para a página de gestão correspondente: empresa → `admin.empresas`; notícia em revisão → `admin.noticia-editar`; notícia publicada → público `news.show`; utilizador novo → `admin.utilizadores`; projeto publicado → `admin.projetos`.
- [x] **Fonte única** — `App\Support\NotificacoesPainel::obter()` fornece os dados ao modal E ao card "Atividade recente". Nenhuma duplicação de lógica.
- [x] Suite permanece **OK (188 tests, 654 assertions)**; `php -l` limpo; imports prontos.

### Critério de conclusão
Implementado em código + suíte verde; layout fixus de CSS aplicados sem browser. **AA do modal e dos links (focus outline vermelho/aria-label) prontos; verificação visual pendente.**

### Acessibilidade — botões vermelhos de marca (11/11/2026, fix defesa + cascade specificity)
Auditoria: nenhuma regra colocava simultaneamente fundo vermelho + texto vermelho. **A causa real reportada pelo cliente (botões «Nova Notícia» com texto quase invisível) era especificidade**: `.dashboard-wrap a { color: #C8102E }` (0,1,1) vencia `.btn-primary` (0,1,0), tornando o texto vermelho sobre vermelho dentro do dashboard. Remediação:

- `public/css/dashboard.css` — `.dashboard-wrap a` e `:hover` voltam à regra **original** (`color: #C8102E`). Remediação anterior (`:not([class*="btn"]):not(.header-icon)`) venceu por especificidade (0,3,1) sobre `.sidebar-item a`, a cores do sidebar ficaram vermelhas e quebravam o design — **revertida**. Em vez disso, botões/anchors com `class` contendo `btn` receberam override `color: inherit` (especificidade 0,2,1 vence 0,1,1 do wrap mas fica abaixo de `.sidebar-item a`). Resultado: sidebar intacta, botões com cores AA preservadas, links de navegação-text-only com o vermelho da marca.
- Adicionada cobertura explícita `color:#fff` aos principais containers red da marca em `style.css` e `color.css` (defesa em profundidade, redundante com o tema, vai ajudar futuros rebrands para vermelho).
- `.btn-primary` .btn-secondary etc já mantinham `color:#fff` (`btn-primary`) / `#1A1A1A` (`btn-secondary`) — verificado.

STATUS: **não verificado visualmente** (sem browser; cliente deve confirmar). Suite verde (**OK 188/654**). Blade compiles (OK).

Auditoria a TODOS os CSS do frontend (`style.css`, `color.css`, `plugins.css`, `topo.css`, `menu.css`): nenhum regra de contraste AA falhou ou público vermelho-sobre-vermelho identificada. Reforçado por **defesa em profundidade** (rules explícitas `color:#FFF` em todos os elementos com fundo vermelho, inclusive herdanças invisíveis do tema Gmag):

- `public/css/color.css` — bloco único de brand-red agora inclui `color:#FFF`.
- `public/css/style.css` — regra explícita `.btn-primary, .btn-danger, .gs_button, .cc_btn, …, .dark-btn:hover, .qty_btn .btn:hover, .search-submit*, .subcribe-form .subscribe-button, .custom-form .log-submit-btn` ⇒ `color: #fff` em força AA. Ícones dentro de botões herdam.
- `dashboard.css` — admin já era AA (todas as regras red-bg white-text verificadas).



---

## FASE 16 - Painel admin: publicidade com layout pro + upload com progresso + modais próprios (12/11/2026)

### Âmbito

Até à Fase 15, os forms de Publicidade (banners) e Destaques estavam orientados para o protótipo original: input de ficheiro cru, sem pré-visualização, sem estados de carregamento, e os botões «eliminar» usavam `window.confirm` (prompt nativa) ou `wire:confirm` (que dispara a mesma). A partir de agora, ambos os fluxos passam por padrões idênticos aos do editor de notícias.

### O que mudou

**1. Banner (`admin/banners.novo|editar`) e Destaque (`admin/destaques.novo|editar`)**
- Layout `.editor-grid` em duas colunas: principal (título, imagem, link destino, posição/escopo, datas, ativo) + sidebar com card sticky «Ações» e card «Pré-visualização».
- Imagem: **upload assíncrono com barra de progresso** (4MB, JPG, PNG ou WebP); conversão para WebP via GD (máx. 1600px, q85); anterior substituída apaga do disco; «Remover» esvazia/apaga a referência; imagem actual = «storage/publicidade/AAAA/MM/…» persistida no hidden `imagem_link`.
- **Toggle «Ficheiro (upload)» / «Link externo (URL)»** com pré-visualização viva.
- Posição & Escopo com rótulos intuitivos (Topo 970×90, Sidebar coluna, Rodapé largura total; «Só página inicial» / «Só páginas interiores»).

**2. Endpoints XHR novos**
- `POST {admin_path}/publicidade/imagem` → `App\Http\Controllers\Admin\PublicidadeImagemController`. Valida mimetype real, máx. 4MB, redimensiona (máx 1600px), grava em `storage/publicidade/YYYY/MM/`, devolve JSON `{ location, url }`. `can:banner.manage`.

**3. Modal próprio para confirmação (sem prompts nativos)**
- `window.PIGEAConfirm(title, message, confirmText, onConfirm)` em dashboard.js — abre o `#confirm-modal`. `.js-confirm` é o atalho para formulários; `.js-confirm-livewire` chama o `Livewire.find(id).call(action, ...args)`.
- `wire:confirm` eliminado de todos os componentes (Banners, Empresas, Documentos, Profissionais, Utilizadores, Destaques).
- `onsubmit="return confirm()"` eliminado do Blade `destaques.blade.php`.

### Verificado
- Suite completa: OK (204 tests / 694 assertions).
- Blade compile: OK (`view:cache`).
- End-to-end: /gestao-pigea/banners/novo → upload dispara XHR → progresso 100% → hidden preenchido com `storage/publicidade/…` → preview actualiza → submit → registo na listagem → botão eliminar → modal aberto → «Eliminar» → some da lista.

### Notas honestas
- Upload assíncrono só funciona com JS activo; sem JS o formulário navega como multipart mas o servidor não processa `imagem_upload_temp`; opção «Link externo» sempre funciona.
- Suite não cobre progresso (eventos XHR); comprovação manual por screenshots (100% + preview presente).


## 📌 Regras de Trabalho (para todas as fases)

1. **Verificar antes de remover:** sempre procurar referências (grep) em rotas, controllers, views, JS e CSS antes de apagar qualquer ficheiro ou bloco.
2. **Documentar em `modificar.md`** toda alteração de código.
3. **Documentar em `memoria.md`** início/fim de cada fase e decisões.
4. **Não escrever tags `<livewire:...>` dentro de comentários HTML** (causa ComponentNotFoundException).
5. **Testar após cada fase** (`artisan test` + navegação nas rotas).
6. Usar agentes recomendados: `senior-project-manager`, `frontend-developer`, `senior-developer`, `evidence-collector`, `reality-checker`.
7. Skill de orquestração: `agency` (NEXUS).

---

*Documento criado a partir de `restrutura.md`. Fases detalhadas; implementação aguarda autorização do cliente.*