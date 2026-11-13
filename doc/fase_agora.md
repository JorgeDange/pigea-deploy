# 📋 fase_agora.md — Estado Técnico Actual do PIGEA

> Documento de arquitectura e estado real (não vista de wishlist). Todas as
> afirmações foram verificadas no repositório em `updates` (HEAD `6ea8647`).
> Última actualização: 2026-11-13. Suite green: **261 testes / 885 assertions**.

---

## 1. Visão geral

**PIGEA** é um portal de conteúdo editorial (notícias, projetos, formação,
diretório, oportunidades, investigação) em **Angola**, com administração CMS
própria. Stack:

| Camada | Tecnologia |
|---|---|
| Backend | **Laravel 12.69.2** / PHP 8.2.12 |
| Front-end público | **Blade** (server-side) + CSS própria |
| Admin | Blade + **Livewire 3** + tema design system nativo (sem framework CSS) |
| Persistência | **MySQL** (`pigea` em dev, `pigea_test` nos testes) |
| Sessão | DB (`sessions`, driver `database`) |
| Autenticação | Laravel **Fortify 1.x** (login com 2FA + passkeys) |
| Autorização | **Spatie Laravel-Permission** (roles/permissoes) |
| Busca | Laravel Scout `database` driver (MySQL FULLTEXT) |
| Imagem fallback | `images/logo.png` (helper `Setting::imagem()`) |

- Prefixo do admin: configurado via `ADMIN_PATH` mas o `.env` local usa `gestao-pigea`.
- Ambiente de desenvolvimento: `APP_ENV=local`; `MAIL_MAILER=log` (emails
  vão para `storage/logs/laravel.log`).
- `PROJECTS_PUBLICO=false` em `.env` (a área pública de Projetos está
  temporariamente excluída — ver secção 4).

---

## 2. Modelo de dados (37 tabelas)

Migrações executadas: **41**. Modelos do app: **21**. Controladores:
**30**.

### 2.1 Núcleo de conteúdo (módulos públicos)

|Tabela|Função|
|---|---|
| `news` | Notícias — com `views_count`, `destaque_editorial` (pins/)— editorial; author (interno ou convidado) |
| `projects` | Projetos mineiros — excl. público (ver secção 4), admin activo |
| `technologies` | Inovação tecnológica |
| `suppliers` | Fornecedores |
| `directory_companies` (companies) | Empresas do directório |
| `directory_professionals` | Profissionais do directório |
| `directory_documents` | Documentos/Legislação/Decretos |
| `ideas` | `ideas` (interno) |
| `categories` | Categorias multi-tipo (news/projects/events/...) com `menu_group` e `parent_id` para menus e descoberta |
| `page_views` | 1 linha por dia (migrations: visits aggregate) |
| `banners` | Publicidade (`topo`,`sidebar`,`rodape`,`lateral`,`largo`) |
| `destaques` | Escolha da Equipa (rotação 4s) |
| `settings` | Definidades do portal (cache 5min): logo, favicon, rodapé, SEO, redes |
| `users` | Utilizadores autenticados |
| `media` | Biblioteca de media (anexos) |

### 2.2 Tabelas de suporte / CMS

|Tabela|Função|
|---|---|
| `newsletter_subscribers` | Subscritores da newsletter (módulo N1) |
| `notifications` | Notificações persistidas (módulo N — opção B) |
| `videos` | Vídeos curtos exibidos no front (listagem e featured) |
| `jobs` | Jobs em fila (Laravel queue) |
| `cache` | Cache de aplicação |

---

## 3. Autenticação e autorização

- **Laravel Fortify** gere `/login`, `/register`, 2FA, passkeys.
- **Spatie Permission**: roles `admin` e `editor`; ~18 permissoes.
- Grupos de rotas admin: `Route::middleware(['auth', 'role:admin,editor'])`.
- Permissões usadas nas rotas de conteúdo:

| Recurso | Permissão |
|---|---|
| Notícias | `news.manage` |
| Projetos | `project.manage` |
| Diretório | `company.moderate` |
| Documentos/Legislação | `document.manage` |
| Vídeos | `video.manage` |
| Banners/Destaques | `banner.manage` |
| Utilizadores | `user.manage` |
| Configurações | `settings.manage` |

`admin.projects` e `projects.novo` (formulário de criação) mantêm-se
acessíveis com `project.manage` — o público assiste aos dados mas a área
pública de Projetos está oculta (secção 4).

---

## 4. Decisões estruturais recentes (2026-11-12 → 2026-11-13)

### 4.1 Área pública de Projetos — **excluída até ordem do cliente**

Decisão registada do cliente (doc `fases_e.md`): "inicialmente a área de projeto
fica excluída, futuramente está a se pensar em implementar".

Consequências:
- `/projetos`, `/projetos/{slug}` → **404 público** (verificado).
- `phpunit.xml` define `PROJECTS_PUBLICO=true` para os testes continuarem a
  cobrir as páginas (suite: 261/885 verde).
- Com `.env` com `PROJECTS_PUBLICO=false` a área fica invisível no frontend.
- `/projetos` e `/projetos/novo` continuam no admin (com `project.manage`).
- Menus/rodapé não ligam mais para `/projetos`; "Oportunidades" aponta para
  `/noticias?cat=oportunidades`.

### 4.2 Newsletter módulo N1 — **completo**

Mecanismo "duplo-opt-in" de notícias do site num modelo próprio (fora do
Mailchimp/Brevo):

- **Modal** `x-newsletter-modal` aparece em todas as páginas públicas após ~3s
  e desaparece se o utilizador subscrever (localStorage flag).
- **Só email** — sem nome; sem duplicação; confirma por link único.
- **Controladores admin**: `NewsletterAdminController` (listagem, confirm,
  cancel, reabilitar, reenviar, exportar CSV, remover). Apenas role `admin`.
- **Gestão**: UI com KPIs simples, acções por linha, acções em massa;
  confirmação via modal própria (sem `alert()` nativo).
- Testes: 13 novos. Route names `admin.newsletter*`.

### 4.3 Notificações — **módulo completo**

- Tabela `notifications` (UUID, morph).
- Triggers via `App\Support\Notificador::enviarParaEquipa()` (admin+editor):
  empresa pendente, profissional pendente, novos utilizadores da equipa,
  notícia publicada, notícia em revisão (se destacada manualmente).
- Sino do painel mostra contagem real; modal própria com "Marcar todas lido".

---

## 5. Editor de notícias — características

- **Editor Blade** (`admin/noticia-editor.blade.php`), não Livewire — TinyMCE
  self-hosted.
- **Componente `<x-provincia-select>`** partilhado por:
  `Notícia editor`, `Novo projeto`, `Formulário genérico`.
  - `App\Support\Provincias::LISTA` — **18 províncias de Angola (+ Nacional)**.
  - «Outros» guarda o texto livre directamente no campo (sem coluna extra).
- **Toggle «★ Destacar na Página Inicial»** — `destaque_editorial` (boolean em
  `news`); é a fonte de pins no hero da home e na página inicial.
- **Estado pedido**: rascunho / em revisão / publicado / rejeitado.
- **Imagem de capa**: ficheiro ou URL — ambas suportadas. Fallback global:
  `images/logo.png` (17 ficheiros).
- **Autosave** por JS (2 s debounce). Status não se altera para published só
  com autosave.

---

## 6. Home — "Bloco hero com pins editoriais + fallback de recentes"

- Hero consome `AppServiceProvider::heroNews()`: 4 notícias,
  `destaque_editorial = true` primeiro, depois recentes. Sem pins → 4 recentes.
- Zona "Destaques" consome `destaqueNews()`: pinned a partir do 5º pin; se
  não há 4 pinnados, completa com recentes.
- `latest('published_at')->orderByDesc('id')` — ordenado por mais novo primeiro
  (correção fase S).
- Tabs AJAX (Mineração, Água, Ambiente, Investigação) resolvem as abas por
  slug do pai `type='news'` via `FragmentController` + `parent_id`.
- Tags populares — componente `<x-sidebar-tags>` calcula as 6 tags mais
  frequentes no site e gera links clicáveis.

---

## 7. Gestão de banners (publicidade) — comportamento atual

- Posições: `topo` · `sidebar` · `rodape` · `lateral` · `largo`.
- `->posicao IN (…, 'largo')` (migração 2026_11_13_000300).
- 2 banners Lateral ativos → rotação em slide, 5s, `banner-lateral.js`.
- Sem banners `Largo` ativos → espaço simplesmente não aparece
  (não há placeholder fixo). O cliente já configurou um Largo em dev.
- Período opcional (`data_inicio`, `data_fim`); limites vazios = sempre válido.

---

## 8. Página "Acompanhamento" (control center)

Rota `/gestao-pigea/acompanhamento` — visível para admin e editor. Mostra:

| Bloco | Conteúdo |
|---|---|
| Hero KPI | Estado geral: "Tudo em ordem" / "N pontos a rever" |
| Redes sociais | 5 redes configuradas (FB/IG/LI/YT/X); só aparecem as configuradas |
| Banners «Largo» | KPIs (no ar · criados · a expirar ≤30d) + anel do inventário por posição |
| Províncias | Ranking de notícias por província (publicadas) |
| Artigos | Destaque editorial pins; imagem em falta; tags em falta |
| Vistas reais | Top 5 mais lidas (anti-bot); coluna `destaque_editorial` na lista |

Fase E4 — a sala de controlo (documento vivo `doc/fases_e.md` secção FASE E4).

---

## 9. Actualização de CSS e temas

- Pública usa `public/css/style.css` + `topo.css` (CSS built in repo; não
  há PostCSS em produção).
- Admin usa `public/css/admin.css` — **reconstruído** a partir de
  `public/css/dashboard.css` via `node build-admin-css.mjs`. Nunca escrever
  CSS no admin directamente sem refazer o `admin.css`.
- Dentro do admin, dicas/idiomas de acesso e os rótulos/hints foram traduzidos
  de jargão técnico para linguagem não-técnica (FASE B — `doc/melhoria.md`).
- O separador do breadcrumb do painel («Admin › Dashboard») foi normalizado:
  em `dashboard.css`/`admin.css` `content: "\203A"` (›) (corrigido, 2026-11-13).

---

## 10. Onde está tudo documentado na pasta `doc/`

| Ficheiro | Conteúdo |
|---|---|
| `newsletter.md` | Decisões, fluxos N1 (subscrição pública / painel / segurança), N2 pendente |
| `notificacoes.md` | Módulo de notificações (opção B) — gatilhos, tabela, testes |
| `fases_e.md` | Todo o planeamento e fases (E1, E2, E3, E4, P) + decisões — documento vivo |
| `editoria_cms.md` | Nota técnica da Home editorial |
| `melhoria.md` | Análise original + Fases A/B/C (performance, linguagem, correção visual) |
| `autor.md` | Módulo de autores (assinatura editorial, conversa convidado) |

---

## 11. Localização no repositório (mapa do código)

| O quê | Onde está |
|---|---|
| App direction | `/pigea-deploy` |
| Modelos | `app/Models/*.php` |
| Controllers | `app/Http/Controllers/Admin/*` e `/Frontend/*` |
| Livewire components | `app/Livewire/Admin/*` (tabelas de lista) |
| Serviços/Supports | `app/Support/`: `Provincias`, `Notificador`, `NotificacoesPainel`, `ContadorDeVisitas`, `MenuCategorias`, `Autor`, `AreasAtuacao` |
| DB migrations | `database/migrations/` (41 migrations) |
| Seeders | `database/seeders/`: `ContentSeeder`, `MenuSeeder`, `DatabaseSeeder`, `RoleSeeder`, `DirectorySeeder` |
| Testes | `tests/Feature/` (~30 files; suite actual: **261/885 assertions** verde) |
| admin-css builder | `build-admin-css.mjs` (`node build-admin-css.mjs`) |

---

## 12. Roda como? — engenharia local e de testes

- Serve: `php -S 127.0.0.1:8000 -t public`
- Migrations no dev: `php artisan migrate`
- Cache de views: `php artisan view:cache`
- Suite de testes: `php artisan test` (DB `pigea_test`, isolada)
- Logs de app: `storage/logs/laravel.log`
- Sessão (ricorda que 2FA está activo): `DB users` + `personal_access_tokens` /
  `passkeys`

`.env` local — vars relevantes:
- `APP_ENV=local`
- `ADMIN_PATH=gestao-pigea`
- `PROJECTS_PUBLICO=false`
- `MAIL_MAILER=log`

---

## 13. Produção — checklist obrigatório (go-live)

1. `php artisan migrate --force` no servidor — migrações `2026_11_13_*`
   (destaque_editorial, projects.province width 64→120, banners largo,
   newsletter_subscribers, notifications).
2. `php artisan view:cache` limpo / reconstruído.
3. `php artisan config:cache` + `route:cache` (opcional; ão rápido
   one-way-rapidez).
4. `PROJECTS_PUBLICO=false` no `.env` de prod (default).
5. `MAIL_MAILER=smtp` + credenciais reais → N2 (envio de edições) quando ativo.
6. `QUEUE_CONNECTION` + `queue:work` running (jobs filhos em `jobs`).
7. Permissão 666 a `storage/` e `public/` (blockwrites) — standard Laravel.
8. Monitorizar `ERRORS` no `storage/logs/laravel.log` após o primeiro deploy.

---

## 14. Afrontamento step-by-step de um deploy incremental (ex. um bugfix)

1. `git pull` → branch `updates`
2. `composer install` (se `composer.lock` mudou)
3. `php artisan migrate` (se alguma migração nova)
4. `php artisan view:cache` se houver alterações de views/blade
5. `php artisan test` — suite verde
6. `php artisan cache:clear` (opcional)

---

## 15. Últimas ações (estado local HEAD `6ea8647`)

- **Newsletter**: admin só com KPIs e gestão (sem gráficos); ações por linha/estado; export CSV correcto.
- **Notificações**: tabela persistente; Observers na `AppServiceProvider`; sino funcional.
- **Exclusão de projetos** temporária (404 no público; admin continua).
- **Breadcrumb** — separador corrigido (`\203A`).
- **Notificações** — spacing via `.notification-list gap`, sem sublinhado, sem borda esquerda.

**Estado confirmado (2026-11-13):** tudo o que estava pendente de autorização
na ramificação foi commitado (HEAD `6ea8647`). Suite **261/885** verde;
`view:cache` limpo.
