# 📝 Registro de Modificações — Projeto PIGEA

**Data:** 10/11/2026  
**Projeto:** PIGEA (`pigea-deploy/`)  

---

## 1. Alteração no Header (`header.blade.php`)

### Arquivo: `resources/views/components/header.blade.php`

#### ✅ O que foi feito
- **Removido:** bloco completo do news ticker ("Últimas Notícias") da top bar.
  - Título "Últimas Notícias :"
  - Lista rolante de 4 notícias (`@foreach ($widgetNews->take(4) as $item)`)
  - Botões de navegação (seta esquerda/direita)
- **Inserido:** `div.top-banner-area` com `banner-placeholder` no mesmo local (centro da top bar).
  - Espaço reservado para futuro banner publicitário.
  - Conteúdo atual: texto estático "Espaço Publicitário".
- **Mantidos:** data (esquerda) e redes sociais (direita).
- **Não foi usado:** nenhum componente Livewire (`<livewire:frontend.banner />`) pois o componente não existe. Apenas comentário HTML indicando onde será inserido no futuro.

#### ⚠️ Risco identificado (não corrigido ainda)
- `public/js/scripts.js` (linha 15) contém `$('.header_news-ticker').easyTicker({...})` — pode gerar erro no console do navegador porque o elemento `.header_news-ticker` não existe mais no HTML.
- Arquivos CSS (`style.css`) possuem regras para `.header_news-ticker-wrap` que agora são órfãs (não causam erro, apenas CSS não utilizado).

---

## 2. Alteração no Menu Principal (`main-menu.blade.php`)

### Arquivo: `resources/views/components/main-menu.blade.php`

#### ✅ O que foi feito
- **Removido:** item "Projectos" (link direto).
- **Removido:** dropdown "Sobre" (continha "Sobre o PIGEA" e "Contactos").
- **Simplificado:** dropdown "Notícias" mantém apenas "Todas as Notícias".
- **Expandido:** dropdown "Recursos" agora inclui as ex-subcategorias de Notícias:
  - Mineração, Água, Ambiente, Investigação (Notícias), Internacional.
- **Mantidos em Recursos (já existentes):** Legislação, Eventos, Investigação, Entrevistas, Diretório.
- **Ajustada:** lógica de item ativo (`$menuAtivo`). Rotas `news.index` com parâmetro `cat` agora marcam `recursos` como ativo (em vez de `noticias`).

---

## 3. Erro Ocorrido e Correção Definitiva

### Erro
```
Livewire\Exceptions\ComponentNotFoundException
Unable to find component: [frontend.banner]
```
Ocorrido na rota `home` (GET /).

### Causa raiz (confirmada no log)
No `header.blade.php`, eu havia escrito a tag `<livewire:frontend.banner ... />` **dentro de um comentário HTML** (`<!-- ... -->`).

**O compilador Blade/Livewire processa tags `<livewire:...>` mesmo dentro de comentários HTML**, porque a conversão acontece em tempo de compilação por regex, sem saber que está dentro de `<!-- -->`. Resultado: o Laravel tentava montar o componente inexistente em toda renderização.

Evidência: stack trace apontava para view compilada `storage/framework/views/4335789a5f0406631230342373c65091.php` linha 24 chamando `LivewireManager->mount('frontend.banner', ...)`.

### Correção aplicada
1. Removida a sintaxe `<livewire:...>` do comentário no header (comentário reescrito sem tags).
2. Cache de views limpo (`php artisan view:clear`).
3. Servidor PHP reiniciado (processos antigos parados, `artisan serve` reiniciado).

### Validação
| Página | Status |
|--------|--------|
| `/` (home) | 200 OK |
| `/noticias` | 200 OK |
| `/empregos` | 200 OK |
| `/tecnologias` | 200 OK |
| `/sobre` | 200 OK |
| `/contactos` | 200 OK |

### Lição aprendida
**Nunca escrever tags `<livewire:...>` (ou outras diretivas Blade) dentro de comentários HTML em views Blade.** Elas são processadas mesmo comentadas. Para documentar sintaxe futura em comentários, escrever sem os delimitadores (ex: `livewire:frontend.banner` sem `<` e `/>`).

---

## 4. Arquivos Afetados

| Arquivo | Tipo de alteração |
|---------|------------------|
| `resources/views/components/header.blade.php` | Remoção news ticker, inserção área de banner |
| `resources/views/components/main-menu.blade.php` | Remoção Projectos/Sobre, simplificação Notícias, expansão Recursos |
| `modificar.md` | Este documento (registro de alterações) |
| `memoria.md` | Atualizado com versão 1.1 |

---

## 5. O Que NÃO Foi Implementado (conforme decisão)

- Nenhuma migration (`banners`, `banner_slides`).
- Nenhum modelo Eloquent (`Banner`, `BannerSlide`).
- Nenhum componente Livewire (`frontend.banner`, admin banners).
- Nenhuma view Blade de cadastro de banner.
- Nenhuma rota para gerenciamento de banners.

Tudo isso está apenas **documentado em `pub.md`**, aguardando decisão do cliente para implementar.

---

## 6. Próximo Passo Recomendado

Quando o cliente decidir implementar o módulo de publicidade:
1. Criar migrations `banners` e `banner_slides`.
2. Criar modelos `Banner` e `BannerSlide`.
3. Criar componente Livewire `frontend.banner` com posição `'top_bar'`.
4. Substituir o `banner-placeholder` no `header.blade.php` pelo componente Livewire.
5. Ajustar ou remover o código JS do `easyTicker` em `scripts.js`.

> **Nota (10/11/2026):** a secção 6 acima foi **superscrita** pelas instruções definitivas de `restrutura.md` — o novo paradigma usa tabela única `banners` (sem `banner_slides`). Ver secção 7.

---

## 7. FASE 0 da Reestruturação — Preparação e Backup (10/11/2026)

Início da reestruturação conforme `restrutura.md` (plano em `fase_all.md`).

### Ficheiros/diretórios criados
| Item | Localização |
|------|-------------|
| Backup do banco SQLite (909 KB) | `pigea-deploy/backups/2026-11-10/database.sqlite` |
| Backups de código (7 diretórios) | `backups/2026-11-10/`: `app_Http`, `app_Livewire`, `app_Models`, `database_migrations`, `database_seeders`, `resources_views`, `routes` |

### Dependência instalada
- `spatie/laravel-medialibrary` **v11.23.12** via Composer (+5 dependências: spatie/image, spatie/image-optimizer, spatie/package-tools, spatie/temporary-directory, maennchen/zipstream-php).
- Extensões PHP confirmadas: `fileinfo` e `gd` (necessárias para conversão WebP).

### Ficheiros publicados (vendor:publish)
| Ficheiro | Origem |
|----------|--------|
| `database/migrations/2026_11_10_104620_create_media_table.php` | stub do Media Library |
| `config/media-library.php` | config do Media Library |

### Migração executada
- `2026_11_10_104620_create_media_table` → tabela `media` criada (20 migrations no total).
- Nota: `2026_09_29_000001_add_fulltext_indexes_to_content_tables` permanece pulada (SQLite não suporta FULLTEXT via schema builder).

### Nota técnica importante
O comando `vendor:publish` **falha quando executado via `cmd /c`** (problema de parsing de aspas aninhadas no Windows: "No arguments expected for vendor:publish command").
**Solução:** executar diretamente no PowerShell com o operador de chamada `&`:
```powershell
& "C:\xampp\php\php.exe" artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag=medialibrary-migrations
```

### Validação
- Tabela `media` existe e está vazia (0 registos).
- `GET http://127.0.0.1:8000/` → 200 OK.

---

## 8. FASE 1 da Reestruturação — Limpeza do Sistema (10/11/2026)

Remoção de Vagas, Candidaturas, Painel da Empresa e Painel do Utilizador, conforme `restrutura.md` (plano em `fase_all.md`).

### 8.1 Ficheiros APAGADOS
**Modelos:** `Job.php`, `Application.php`, `SavedJob.php`, `CompanyProfile.php`, `Opportunity.php`, `Notification.php` (notificação custom do painel)

**Controllers:** `Painel/EmpresaController.php`, `Painel/UtilizadorController.php`, `Auth/CompanyRegisterController.php`, `Frontend/JobController.php`

**Actions:** `Actions/Fortify/CreateNewUser.php` (sem registo público)

**Livewire:** diretório `app/Livewire/Painel/` (VagasEmpresa, MinhasCandidaturas, CandidaturasRecebidas)

**Views:** diretórios `jobs/`, `painel-empresa/`, `painel-utilizador/`; `auth/company-register.blade.php`, `auth/register.blade.php`, `admin/vagas.blade.php`; componentes `sidebar-company.blade.php`, `sidebar-user.blade.php`

**Seeders:** `PainelSeeder.php`

**Migrations:** `2026_09_30_030002_create_painel_user_tables.php` (applications, saved_jobs, notifications), `2026_09_30_030003_create_painel_empresa_tables.php` (company_profiles, opportunities), `2026_10_06_000002_add_company_type_to_company_profiles.php`

**Testes:** `PainelPagesTest.php`, `RoleAccessTest.php`, `GranularAuthTest.php`, `AuthFlowTest.php` (testavam módulos removidos)

### 8.2 Ficheiros EDITADOS
| Ficheiro | Alteração |
|----------|-----------|
| `routes/web.php` | Removidas rotas `jobs.*` (3), `company.register` (2), grupo `painel` (role:user), grupo `painel-empresa` (role:company), `admin.vagas`; imports dos controllers removidos |
| `AppServiceProvider.php` | Removidos `use Job`, `View::share('featuredJobSlug', …)` e `View::share('listJobs', …)` — verificado que NENHUMA view usava essas variáveis |
| `AuthServiceProvider.php` | Removidos 4 gates de posse (delete-vacancy, delete-opportunity, decide-application, withdraw-application) |
| `Models/User.php` | Removidas relações `applications()`, `savedJobs()`, `notifications()` (custom), `companyProfile()`, `opportunities()` |
| `Providers/FortifyServiceProvider.php` | Removido `Fortify::createUsersUsing(CreateNewUser)`, `Fortify::registerView()`, binding `RegisterResponseContract` |
| `config/fortify.php` | Removido `Features::registration()` |
| `Http/Responses/RoleBasedHomeResponse.php` | Simplificado: só LoginResponse; admin/editor → ADMIN_PATH, restantes → `/` |
| `Concerns/ResolvesContent.php` | Removido `Job::class` da lista de resolução de slugs |
| `Admin/DashboardController.php` | Removido Job de `modelosConteudo`, cartão "Vagas ativas", atividade "Nova vaga", rótulo 'Vaga', tabela `vacancies` nos gráficos de província |
| `Admin/ContentFormController.php` | Removido bloco `'vagas'` do TIPOS, `ESTADOS_VAGA`, rótulo de lista, import Job |
| `Livewire/Admin/ContentTable.php` | Removido bloco `'vagas'` da configuração, import Job |
| `Frontend/SearchController.php` | Removido `Job::class` dos modelos pesquisáveis |
| `seeders/RoleSeeder.php` | Removidas roles `company` e `user` (+permissões job.*, application.*, notification.*); `job.moderate` removida do admin |
| `seeders/DatabaseSeeder.php` | Removida chamada ao `PainelSeeder` |
| `seeders/ContentSeeder.php` | Removida entrada `'jobs'` do mapa MODELS (jobs.json ignorado) |
| `migrations/2026_09_28_220002_create_content_tables.php` | Removida `'vacancies'` da lista de tabelas |
| `migrations/2026_09_28_220004_add_date_label_to_content_tables.php` | Removida `'vacancies'` |
| `migrations/2026_09_29_000001_add_fulltext_indexes…` | Removida `'vacancies'`; agora pula-se em SQLite (antes falhava) |
| `components/main-menu.blade.php` | Removido item "Empregos" e lógica `jobs.` |
| `components/footer.blade.php` | Removido link "Empregos" |
| `components/header.blade.php` | Simplificada lógica do painel no @auth (só ADMIN_PATH) |
| `components/auth-modal.blade.php` | Reescrito: só formulário de login (sem tab "Registar" nem link "Registar a sua empresa") |
| `auth/login.blade.php` | Removidos links "Registe-se" e "Registe a sua empresa" |
| `home.blade.php` | 2× `route('jobs.index')` → `route('news.index')` |
| 8 views com CTA "Registar Empresa" (contacts, home, suppliers, news/index, news/show, search, legislation, events) | `route('company.register')` → `route('contacts')` — **temporário** até a Fase 3 criar `/registo-empresa` |
| `components/sidebar-admin.blade.php` | Removido item "Vagas" |
| `tests/Feature/PublicPagesTest.php` | Removidas páginas `/empregos`, `/register`, `/registo-empresa` e teste de detalhe de vaga |
| `tests/Feature/AdminContentFormTest.php` | Removido tipo 'vagas' |

### 8.3 Banco de dados
- `migrate:fresh --seed` executado com sucesso: 17 migrations, sem tabelas de vagas/candidaturas.
- Migration FULLTEXT agora **pula-se em SQLite** (antes tinha de ser saltada manualmente) e mantém índices em MySQL (produção).
- Roles no banco: apenas `admin` e `editor`. Utilizador demo `test@example.com` com role admin.
- Nota: `phpunit.xml` usa MySQL (`pigea_test`) — a suíte de testes requer MySQL local; validação desta fase feita por navegação HTTP.

### 8.4 Validação (HTTP)
- **16 páginas públicas 200 OK:** `/`, `/noticias`, `/projetos`, `/tecnologias`, `/fornecedores`, `/diretorio`, `/formacao`, `/legislacao`, `/eventos`, `/investigacao`, `/entrevistas`, `/pesquisa`, `/sobre`, `/contactos`, `/login`, `/404`.
- **Rotas removidas corretamente 404:** `/empregos`, `/register`, `/registo-empresa`, `/painel`, `/painel-empresa`.
- `composer dump-autoload` regenerado (8845 classes).
- `route:list` sem rotas órfãs.

---

## 9. FASE 2 da Reestruturação — Base de Dados e Modelos (10/11/2026)

Novas tabelas e modelos conforme `restrutura.md` §3.

### 9.1 Migrations criadas
| Migration | Tabela | Campos |
|-----------|--------|--------|
| `2026_11_10_120000_create_companies_table` | `companies` | nome_empresa, nif, contactos, areas_atuacao (json), redes_sociais (json, nullable), status enum pendente/aprovado/rejeitado (default pendente, index) |
| `2026_11_10_120001_create_professionals_table` | `professionals` | nome_completo, formacao_academica, contactos, areas_atuacao (json), status enum (default pendente) |
| `2026_11_10_120002_create_directory_documents_table` | `directory_documents` | titulo, descricao (string 500), tipo enum artigo/documento/lei, link_download, areas_atuacao (json) |
| `2026_11_10_120003_create_banners_table` | `banners` | titulo, imagem_url, link_destino (nullable), posicao enum topo/sidebar/rodape, data_inicio/data_fim (nullable), ativo (boolean) |
| `2026_11_10_120004_add_video_and_document_links_to_news_table` | `news` | + video_link (nullable), + documento_link (nullable) |

Decisões: imagem do banner é `imagem_url` (upload OU link) porque o Spatie Media é usado APENAS em News (restrutura.md §7.5); `directory_documents` não tem status (só a equipa publica).

### 9.2 Modelos criados
- `app/Models/Company.php` — casts array (areas_atuacao, redes_sociais); scopes `aprovado`, `pendente`, `rejeitado`
- `app/Models/Professional.php` — cast array; mesmos scopes
- `app/Models/DirectoryDocument.php` — cast array; `getTipoLabelAttribute()` (Artigo/Documento/Lei); scope `porArea` (whereJsonContains)
- `app/Models/Banner.php` — casts date/boolean; scope `ativos()` (ativo + dentro de data_inicio..data_fim, limites opcionais); scope `posicao()`

### 9.3 Modelo News atualizado
- Implementa `Spatie\MediaLibrary\HasMedia` + `InteractsWithMedia`
- Coleções: `capa` (singleFile) e `contexto` (galeria)
- Conversão `webp` (qualidade 85, `nonQueued` — não há worker de filas)
- `video_link` e `documento_link` adicionados ao `$fillable`
- Coluna legada `image` mantida (as vistas ainda a usam — migração para `capa` nas Fases 4-5)

### 9.4 Seeder
- `database/seeders/DirectorySeeder.php` registado no `DatabaseSeeder`
- Dados: 5 empresas (3 aprovadas, 1 pendente, 1 rejeitada), 4 profissionais (2 aprovados, 1 pendente, 1 rejeitado), 4 documentos (lei/artigo/documento), 3 banners (topo vigente, sidebar permanente, topo expirado)

### 9.5 Validação (script funcional, apagado após uso)
- Scopes: companies 5 total (3/1/1 por estado); banner expirado excluído corretamente de `ativos()`
- Casts JSON funcionam (areas_atuacao, redes_sociais)
- `porArea('Mineração')` → 2 documentos
- **Upload real testado:** PNG anexado à coleção `capa` de uma notícia → ficheiro guardado + conversão `testing-check-webp.webp` criada (GD OK)
- `storage:link` criado (`public/storage`)
- Páginas /, /noticias, /diretorio, /login → 200 OK

---

## 10. FASE 3 da Reestruturação — Formulários Públicos Livewire (10/11/2026)

Dois formulários públicos sem login (restrutura.md §4); a submissão grava com `status = 'pendente'` para moderação no /admin (Fase 4).

### 10.1 Whitelist de áreas de atuação
- `app/Support/AreasAtuacao.php` — classe final com `LISTA` (Mineração, Petróleo e Gás, Geociências, Água, Ambiente, Energia) e `filtrar()` (intersect + unique).
- Motivo: as áreas chegam por POST (checkboxes), mas o POST é manipulável — nunca gravar texto arbitrário no JSON.

### 10.2 Componentes Livewire (`app/Livewire/Frontend/`)
- `RegistoEmpresa.php` — campos nome_empresa, nif (required, unique:companies,nif), contactos, areas_atuacao (array min:1), facebook/linkedin/instagram (nullable|url). Redes gravadas como JSON (pares rede→URL, vazias → null). Sucesso: `estado = 'ok'` + reset dos campos.
- `RegistoProfissional.php` — campos nome_completo, formacao_academica, contactos, areas_atuacao.
- Convenções do `ContactForm` existente: `Validator::make` com mensagens em português, propriedade `estado` (''/ok/erro), `erro` com a primeira mensagem.

### 10.3 Vistas
- Componentes: `resources/views/livewire/frontend/registo-empresa.blade.php` e `registo-profissional.blade.php` (markup do tema: `custom-form`, checkboxes, bloco de sucesso com a mensagem exata do cliente "Registo submetido com sucesso. Aguarde a aprovação da nossa equipa."; bloco de erro).
- Páginas: `resources/views/registo-empresa.blade.php` e `registo-profissional.blade.php` (extends layouts.app, hero-section + breadcrumbs, box "Como funciona" com 3 passos, link cruzado entre os dois registos).
- View órfã apagada: `resources/views/livewire/painel/` (restos da Fase 1).

### 10.4 Rotas (`routes/web.php`)
- `Route::view('/registo-empresa', 'registo-empresa')->name('registo-empresa')`
- `Route::view('/registo-profissional', 'registo-profissional')->name('registo-profissional')`
- Colocadas após /contactos, antes do grupo admin.

### 10.5 CTAs atualizados (8 vistas)
`route('contacts')` → `route('registo-empresa')` nos CTAs "Registar Empresa" de: contacts, home, suppliers/index, news/index, news/show, search/index, legislation/index, events/index. O link "Luanda, Angola" do footer mantém `route('contacts')` (é localização, não CTA).

### 10.6 Validação (script funcional via Livewire::test, apagado após uso)
- Submissão válida → grava em `companies` com status pendente, áreas corretas, redes JSON; estado 'ok'.
- NIF duplicado → rejeitado com "Já existe um registo com este NIF."
- Áreas manipuladas ('Mineração' + 'Area Falsificada') → grava apenas as da whitelist.
- Sem áreas → erro "Selecione pelo menos uma área de atuação."
- Profissional válido → pendente; whitelist igualmente aplicada.
- Páginas /registo-empresa e /registo-profissional → 200 OK; /, /contactos, /noticias, /fornecedores, /legislacao, /eventos, /pesquisa continuam 200 OK.
- Registos de teste removidos do banco no fim.

---

## 11. FASE 4 da Reestruturação — CMS Admin (10/11/2026)

Painel /admin completo para a equipa interna (restrutura.md §5).

### 11.1 Permissões (RoleSeeder)
- Editor: + `document.manage` e `banner.manage` (documentos do diretório e publicidade são gestão da equipa interna).
- Admin: herda tudo (`*` + sync de todas as permissões). Diretório (empresas/profissionais) mantém `company.moderate`.

### 11.2 Dashboard (§5.1)
- Contadores: Notícias publicadas, **Empresas pendentes** (Company::pendente), **Profissionais pendentes** (Professional::pendente), Utilizadores, Conteúdo pendente.
- Atividades: "Empresa à espera de aprovação" usa Company::pendente (antes: modelo legado Directory).
- **Correção portável:** as consultas de gráficos usavam `DATE_FORMAT` (MySQL) — em SQLite local davam 500 no dashboard. Novo método `expressaoMes()`: `strftime('%Y-%m')` em SQLite, `DATE_FORMAT` em MySQL.

### 11.3 Diretório de Empresas (§5.3)
- Novo componente Livewire `App\Livewire\Admin\CompaniesTable` + view `livewire/admin/companies-table.blade.php` — colunas Nome/NIF/Contactos/Áreas/Estado; ações Aprovar (`aprovado`), Rejeitar (`rejeitado`), Voltar a pendente (`restabelecer`), Eliminar (com `wire:confirm`); filtro por estado + busca por nome; Gate `company.moderate`.
- A página admin.empresas troca o antigo `content-table tipo="empresas"` (modelo legado Directory) pelo componente novo.
- Limpeza: entrada 'empresas' removida de `ContentTable::TABELAS` e de `ContentFormController::TIPOS` (+ ESTADOS_EMPRESA, ROTULOS_LISTA['empresas'], use Directory) — grep antes de remover: o ContentTable era a única referência dos métodos de aprovação, também removidos. O modelo `Directory` mantém-se no FRONTEND (/diretorio e /pesquisa ainda o usam — troca na Fase 5).

### 11.4 Diretório de Profissionais (§5.4)
- `ProfessionalsTable` (mesmo mecanismo do CompaniesTable) + página admin/profissionais + rota `admin.profissionais` (can:company.moderate).

### 11.5 Documentos/Artigos (§5.5)
- `DocumentsTable` (filtro por tipo, busca, editar→redirect, eliminar soft delete) + `DocumentFormController` (create/store/edit/update).
- Campos: titulo, descricao (máx 500), tipo (artigo/documento/lei), link_download (**apenas link**, restrutura.md §1), areas_atuacao (checkboxes → `AreasAtuacao::filtrar`).
- Vista admin/documento-form (mesma linguagem visual do conteudo-form).
- **SoftDeletes adicionados** (migration `2026_11_10_180000`) a `directory_documents` e `banners` — eliminar no painel é reversível, mesma regra das tabelas de conteúdo.

### 11.6 Publicidade (§5.6)
- `BannersTable` (filtro por posição, busca, alternarAtivo, editar, eliminar soft delete) + `BannerFormController`.
- Imagem: **upload OU link** (`imagem_upload` file png/jpg/webp máx 4MB tem prioridade; `imagem_link` url). Upload guardado em `storage/banners/` (disco public) — o Spatie Media fica APENAS em News (restrutura.md §7.5). Ficheiro antigo local é apagado quando substituído.
- Campos: titulo, link_destino (opcional url), posicao (topo/sidebar/rodape), data_inicio/data_fim (data_fim >= data_inicio), ativo.
- Vista admin/banner-form.

### 11.7 Notícias (§5.2)
- `NewsFormController::store` atualizado: upload da imagem → **coleção Spatie `capa`** + coluna legada `image` aponta ao ficheiro (`storage/{id}/{file_name}`) para as vistas atuais; **`contexto[]`** (até 10 uploads) → coleção `contexto`; campos **`video_link`** e **`documento_link`** (apenas url).
- **Correção de ordem:** a notícia precisa de id ANTES de `addMedia` — com modelo por gravar, o Spatie devolve a media sem id e a coluna `image` ficava `storage//capa.png`. Validado: `storage/6/capa.png` + conversão WebP.
- Edição genérica (ContentFormController): extras `video_link`/`documento_link` (tipo novo `url` — regras e case na vista conteudo-form).

### 11.8 Utilizadores (§5.7)
- Já operacional de fases anteriores (admin.utilizadores + UsersTable + UserFormController) — nada a fazer.

### 11.9 Sidebar e rotas
- Sidebar admin: + Profissionais (fa-user-tie), Documentos (fa-file-alt), Publicidade (fa-bullhorn); mapeamento de item ativo para documentos.novo/editar e banners.novo/editar.
- Rotas novas (ANTES das genéricas `/{tipo}/novo`): admin.profissionais (view), admin.documentos (view) + admin.documentos.novo/store/editar/atualizar, admin.banners (view) + admin.banners.novo/store/editar/atualizar.

### 11.10 Validação (script funcional apagado após uso + navegação HTTP)
- CompaniesTable: aprovar/rejeitar/restabelecer OK.
- ProfessionalsTable: aprovar OK.
- Documentos: store OK (whitelist de áreas filtra 'Area Falsificada'), update OK, eliminar soft delete OK, link inválido rejeitado com mensagem pt OK.
- Banners: store com upload real OK (storage/banners/…, ficheiro existe), update com link OK, alternarAtivo OK.
- Notícias: store com upload OK — media capa criada, `image` = storage/{id}/capa.png, video_link/documento_link gravados, status published, conversão WebP OK.
- Navegação HTTP autenticada: /admin, /empresas, /profissionais, /documentos, /documentos/novo, /banners, /banners/novo, /noticias/nova, /utilizadores, /configuracoes → **todas 200 OK**; tabelas renderizam dados do seeder (AngoGeo, Código Mineiro, Minex).
- `migrate:fresh --seed` OK (23 migrations).

---

## 12. FASE 5 da Reestruturação — Frontend Público (10/11/2026)

Novo menu e página de diretório (restrutura.md §6).

### 12.1 Categorias novas (ContentSeeder)
- `Petróleo e Gás` (slug `petroleo-e-gas`) e `Geociências` (slug `geociencias`) garantidas por `Category::firstOrCreate` para TODOS os tipos de conteúdo (eram itens de topo do menu sem categoria nos dados extraídos). O `NewsFilter` filtra por `category.slug` — os links `?cat=` funcionam automaticamente.

### 12.2 Novo menu (§6 — 9 itens do cliente)
- `components/main-menu.blade.php` reescrito: **Início** (home), **Notícias** (news.index), **Mineração** (?cat=mineracao), **Petróleo e Gás** (?cat=petroleo-e-gas), **Geociências** (?cat=geociencias), **Academia** (training.index — Formação), **Oportunidades** (projects.index — Projetos), **Directório** (directory.index), **Sobre Nós** (about). Itens planos, sem dropdown.
- Lógica de item ativo ajustada: news.show acende "Notícias"; listagens ?cat= acendem a área de topo correspondente; training→Academia, projects→Oportunidades, directory→Directório, about→Sobre Nós.
- Destinos documentados: restrutura.md não define destinos — Academia usa a página de Formação existente e Oportunidades a de Projetos (decisões registradas; ajustáveis com o cliente).

### 12.3 Página /directorio (§6)
- URL corrigido para **/directorio** (ortografia do cliente, com duas r); o antigo **/diretorio** devolve 301 para não partir links indexados. Nome da rota mantém `directory.index` (menu/footer/aside continuam válidos).
- `DirectoryController::index` simplificado (só renderiza a página — a listagem é o componente Livewire `DirectoryListing`).
- Componente `App\Livewire\Frontend\DirectoryListing` + view `livewire/frontend/directory-listing.blade.php`:
  - Abas "Empresas" e "Artigos/Documentos" (`$aba` na URL `?aba=` — partilhável; `mostrar()` ignora valores inválidos).
  - Busca única por nome ou área de atuação (like no texto JSON das áreas).
  - Aba Empresas: apenas `Company::aprovado()` — Nome, Áreas de Atuação (chips), Contactos, Redes Sociais (ícones facebook/linkedin/instagram); **NIF NUNCA apresentado** (restrutura.md §6).
  - Aba Artigos/Documentos: título, tipo legível, descrição, áreas e botão **Download** que abre `link_download`.
  - Paginação por aba (8/página, resetPage ao trocar aba/busca).

### 12.4 Links dos formulários públicos (§5.3)
- Footer (coluna "Negócios"): + Registar Empresa, + Registar Profissional (e Oportunidades corrigido para projects.index; "Directório" com a ortografia do cliente).
- Página /directorio: caixas laterais "Registe a sua empresa" e "Profissional independente" com os links dos registos.

### 12.5 Validação (script funcional apagado + navegação HTTP)
- Categorias news/petroleo-e-gas e news/geociencias criadas OK.
- DirectoryListing: 3 empresas listadas todas aprovadas; **NIF ausente do HTML** (5417000001 não aparece); busca 'AngoGeo' → 1; busca por área 'Petróleo e Gás' → 1; 4 documentos com botão Download; aba inválida ignorada; empresa pendente (GeoLab) e rejeitada (MineraSpec) invisíveis.
- Navegação HTTP: /, /noticias, ?cat=mineracao, ?cat=petroleo-e-gas, ?cat=geociencias, ?cat=agua, ?cat=ambiente, /formacao, /projetos, /directorio, /directorio?aba=documentos, /sobre, /registo-empresa, /registo-profissional, /contactos → **todas 200**; /diretorio antigo → 301.
- Menu da home renderiza os 9 itens do cliente; classe act-link presente.

---

## 13. FASE 6 da Reestruturação — Publicidade (Banners no frontend) (11/11/2026)

Renderização dos banners geridos no admin (restrutura.md §3.5; fase_all.md FASE 6). O `pub.md` foi analisado como referência: aproveitou-se a lógica de renderização/desempate/UX (§5.5, §6.2, §7); **não** se adoptaram as duas tabelas (`banners`+`banner_slides`), o campo `tipo` nem as posições antigas (`home_topo`, `vagas_topo`, …) — ultrapassadas pelo restrutura.md (tabela única; posições `topo`/`sidebar`/`rodape`). O `pub.md` continua válido como spec do slider futuro.

### 13.1 Componente de renderização
- **Decisão de engenharia:** componente **Blade anónimo** `resources/views/components/banner-publicitario.blade.php` (uso `<x-banner-publicitario posicao="topo" />`), NÃO Livewire — banner é estático (imagem+link), render server-side por request (período correto), zero JS/hidratação; evita também a restrição de root-tag do Livewire 3 quando não há banner (vista vazia).
- Primeira tentativa foi Livewire `Frontend\Banner` — descartada (erro "missing root tag" na posição vazia + overhead inútil). Ficheiro apagado.
- Lógica: `Banner::ativos()->posicao($posicao)->orderByDesc('id')->first()` — só exibíveis hoje (ativo + período); desempate pub.md §6.2 (mais recente = maior id).
- Regras pub.md §5.5/§7: rótulo "Publicidade", `alt` = título, `loading="lazy"`, `max-width: 100%` sem distorção, `rel="noopener sponsored"`; **sem `link_destino` → imagem não clicável**; sem banner → não gera HTML.
- `imagem_url` aceita as 3 origens: upload (`storage/banners/…` → `asset()`), link externo (`http(s)://…` direto), asset do tema (`images/…`).
- Prop `wrapper` (boolean): embrulha o banner no `box-widget` do tema — usado nas sidebars.

### 13.2 Integração nas views
- **Header (posição `topo`):** `banner-placeholder` ("Espaço Publicitário") substituído por `<x-banner-publicitario posicao="topo" />` em `components/header.blade.php` — fora de comentários HTML (regra v1.3).
- **Sidebar (posição `sidebar`):** componente com `wrapper` após o widget de pesquisa nas sidebars de `news/index.blade.php` e `news/show.blade.php` (antes do widget "Registe a sua empresa").
- Posição `rodape`: suportada pelo componente; sem ponto de inserção definido pelo cliente — fica pronta a usar.

### 13.3 scripts.js — easyTicker órfão
- Chamada `$('.header_news-ticker').easyTicker({…})` REMOVIDA (grep confirmou: zero usos de `.header_news-ticker` nas views — o ticker foi retirado do header na v1.1; agora o banner real o substitui). Comentário no local explica a remoção.
- Chamada `$('.picker-wrap').easyTicker({…})` e handler `.pwc_pause` MANTIDAS (`.picker-wrap` continua em uso na home).
- CSS `.header_news-ticker-wrap` mantido: regra partilhada com `.aside-panel` (style.css:5168) — removeria estilos usados; inofensivo sem HTML.

### 13.4 Erro encontrado e resolvido
- **"Cannot declare class App\Livewire\Frontend\Banner because the name is already in use"** mesmo em `php -l` isolado: causa = `use App\Models\Banner;` + `class Banner` no MESMO ficheiro (colisão import/classe em compile-time, independente de autoload/BOM). Ficou obsoleto com a conversão para Blade anónimo, mas fica registrado: em ficheiros PHP, o import nunca pode ter o mesmo nome curto da classe declarada.

### 13.5 Validação (script funcional apagado + HTTP)
- Script: **13/13 OK** — topo vigente (Minex, link+alt+Publicidade+lazy+responsivo), expirado ausente; sidebar Hidrotec em box-widget; rodapé sem banner → sem HTML; banner sem link → sem `<a>`; ativo=false ignorado; desempate 2 vigentes → mais recente (banners TEMP criados e apagados).
- HTTP: 10 páginas **200** (/; /noticias; /noticias/{slug}; /directorio; /formacao; /projetos; /sobre; /registo-empresa; /registo-profissional; /contactos).
- HTML renderizado: home contém `example.ao/minex` + "Publicidade" + `banner-publicitario--topo` + imagem; `Espa`/`banner-placeholder` ausentes; /noticias e detalhe contém `example.ao/hidrotec` + `banner-publicitario--sidebar`; scripts.js com exatamente 1 chamada `.easyTicker(` (a do `.picker-wrap`).
- Zero referências a `livewire:frontend.banner` no código.

---

## 14. FASE 7 da Reestruturação — Testes e Validação (11/11/2026)

**Suíte REAL verde: 130 testes, 447 asserções (~12-20s)** — `php artisan test` contra MySQL `pigea_test`.

### 14.1 Como os testes voltaram a correr (MySQL arrancado ad hoc)
- `phpunit.xml` exige MySQL `pigea_test` (bootstrap fail-closed protege a BD de dev — design mantido). O MySQL do XAMPP não estava a correr: `mysqld.exe --defaults-file=…` em background + `CREATE DATABASE pigea_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci` + `DB_USERNAME=root/DB_PASSWORD=""` via env do shell (o .env tem as linhas DB_ comentadas). 1.ª execução faz `migrate:fresh --seed` na `pigea_test` (~14s). **Estado da máquina restaurado: MySQL parado no fim** (reiniciar para voltar a correr testes).

### 14.2 Testes existentes corrigidos (obsoletos pela reestruturação)
- `AdminContentFormTest`: dataset `empresas => Directory` REMOVIDO do provider `tipos()` (o tipo saiu de `ContentFormController::TIPOS` por decisão da Fase 2/4) + novo contrato: `admin.conteudo.editar tipo=empresas` → **404**; import `Directory` retirado.
- `PublicPagesTest`: `/diretorio` (URL antigo) → `/directorio` no dataset + novo teste do **redirect 301**.
- Resultado intermédio: 84→86 passados (3 falhados → corrigidos).

### 14.3 NOVA suíte da reestruturação (43 testes em 5 ficheiros)
- `tests/Unit/ModelosRestruturaTest.php` (9): `AreasAtuacao::filtrar` (whitelist/duplicados/não-array); scopes de Company/Professional (aprovado/pendente/rejeitado); casts JSON; **NIF unique** (via Validator, como o form usa); SoftDeletes de DirectoryDocument/Banner (`assertSoftDeleted`); `Banner::ativos` (vigente/sempre/expirado/futuro/inativo) e `posicao`.
- `tests/Feature/FormulariosPublicosTest.php` (7): páginas 200; submissão válida → grava `pendente` + mensagem exata "Aguarde a aprovação da nossa equipa"; **NIF duplicado rejeitado**; campos obrigatórios; **áreas fora da whitelist não gravam** (POST manipulável); URL de rede social inválido; profissional pendente/sem áreas.
- `tests/Feature/ModeracaoDiretorioTest.php` (11): aprovar→aprovado, rejeitar→rejeitado (invisível no público), restabelecer→pendente, eliminar (hard delete da Company); busca/filtro de estado na tabela; aprovar profissional; **sem role → 403**; **editor gere documentos/banners mas NÃO moderação (company.moderate)**; admin vê as 5 páginas (navegação); banner com **link externo** criado; posição inválida (`home_topo`) → erro; **banner sem imagem → erro**.
- `tests/Feature/DirectorioPublicoTest.php` (6): página 200; **apenas aprovados + NIF NUNCA no HTML**; busca por nome; **busca por área**; aba documentos com Download; aba inválida ignorada.
- `tests/Feature/BannersFrontendTest.php` (7): vigente com link+Publicidade+alt+lazy; expirado/inativo ausentes; **sem link → sem `<a>`**; posição vazia → sem HTML; **desempate pelo mais recente**; sidebar com wrapper e imagem externa.

### 14.4 BUGS REAIS apanhados pela Fase 7 (corrigidos)
1. **Banner podia ser criado SEM imagem** — `imagem_link`/`imagem_upload` ambos nullable no `BannerFormController` (quebraria a renderização com `<img src="">`). Fix: na **criação**, `imagem_link` → `required_without:imagem_upload` (mensagem "Envie um ficheiro de imagem OU indique o link da imagem."); na **edição** mantém o comportamento (sem upload/link mantém a imagem atual).
2. **Busca por área do diretório nunca funcionava com acentos** — `json_encode` default gravava `["Petr\u00f3leo e G\u00e1s"]` e o LIKE do `DirectoryListing` nunca batia. O teste da Fase 5 tinha passado **por coincidência** (empresa "Cuanza Petróleo e Gás SA" bateu pelo NOME). Fix: hooks `Attribute::set` com `JSON_UNESCAPED_UNICODE` em `Company` (areas+redes), `Professional` e `DirectoryDocument` (areas); `DirectorySeeder` convertido de `insert()` para `create()` (os hooks tratam do encoding); dev DB re-seeded.
3. **Editor bloqueado de todo o /admin** — o grupo tinha `role:admin`, mas a Fase 4 deu `document.manage`/`banner.manage` ao editor sem haver rota que os deixasse usar. Fix: `role:admin,editor` no grupo (o `can:` granular de cada rota continua a decidir: moderação/utilizadores/configs continuam 403 para o editor).
- Também: teste do NIF unique ajustado à mensagem pt real ("…já se encontra registado.").

### 14.5 Validação no ambiente dev (SQLite re-seeded)
- Script apagado (verificação final): seeder com acentos literais ✓; busca por área bate pela ÁREA (Cuanza) ✓; apenas aprovados ✓; NIF ausente ✓; banner topo na home ✓.
- Navegação HTTP: 14 páginas públicas **200** (/, /noticias, ?cat=petroleo-e-gas, ?cat=geociencias, /projetos, /formacao, /directorio, /directorio?aba=documentos, /sobre, /contactos, /registo-empresa, /registo-profissional, /pesquisa?se=…, /login); /diretorio antigo → **301**; placeholder antigo ausente.
- Navegação admin coberta pela SUITE (boot limpo por teste — o padrão de reutilizar o kernel em script produziu 404/500 falsos, descartado).

### 14.6 Screenshots (evidence-collector)
- **Não capturados**: o ambiente atual não tem ferramentas de browser/screenshot. Evidência visual substituída por: HTML renderizado verificado (banner topo/sidebar, rótulo Publicidade, placeholder ausente, NIF ausente) + 447 asserções da suíte. Captura manual recomendada antes da entrega ao cliente (browser → páginas-chave).

---

## 15. FASE 5.1 da Reestruturação — Mega Menu `menu.md` + ajustes do cliente (11/11/2026)

### 15.1 Implementação (antes dos ajustes)
- Migration `categories` (+ `parent_id`, `menu_group` idx, `order`) — categorias DB-driven; subcategoria sem destino cai no link da secção.
- `MenuSeeder` (idempotente, wired no `DatabaseSeeder` após o ContentSeeder): **44 subcategorias** com os nomes EXATOS do cliente — Início 4, Notícias 4, Mineração 5, Petróleo e Gás 5, Geociências 7, Academia 5, Oportunidades 5, Directório 4, Sobre Nós 5. Grupos Mineração/Petróleo/Geociências → `type='news'` (filtráveis `?cat=` via NewsFilter e no select admin; `parent_id` → categoria pai de notícias); restantes → `type='menu'`.
- `App\Support\MenuCategorias`: grupos + sublinks + mapa de destinos (ajustável, documentado) + `grupoAtivo()`. `main-menu.blade.php`: barra branca, 9 itens com chevron, item ativo `#e6f0ff`, painel full-width com grelha de 3 colunas. `public/css/menu.css` (3 regimes: barra única ≥1280 / segunda linha 1065-1279 / acordeão ≤1064 com hamburger do tema; clone `.menusb` escondido). `public/js/menu.js` (`defer`): só acordeão mobile; desktop 100% CSS (hover + focus-within).
- `tests/bootstrap.php`: aprende a semear o MenuSeeder em BD já migrada (check `menu_group IS NOT NULL` = 0).
- `MegaMenuTest`: 15 testes. Suíte pós-fase: **145/534**.
- Destinos documentados como ajustáveis (Início→noticias, Academia→formacao/events/research, Oportunidades→projects.index, Directório→?aba=…, Equipamentos→/fornecedores, Sobre→about/contacts).

### 15.2 Ajustes pedidos pelo cliente (11/11/2026, validação visual)
- Removida a **descrição no painel** do menu: bloco `mm-panel-cab` (título `mm-panel-titulo` + link "Subcategorias") — painel passa a ser **grelha pura de links**.
- Removido o **ícone de utilizador** (`srf_btn`) do `header.blade.php` — ambas as variantes (autenticado para o painel admin / visitante "Entrar" com `show-reg-form`). O login leva a equipa direto a `/admin` (`RoleBasedHomeResponse`), por isso nada fica inacessível. Binding `show-reg-form` no scripts.js fica no-op inócuo; regras `.srf_btn` no style.css ficam mortas (backup 2026-11-10 intacto); grep antes de remover: sem referências ativas.
- Removida a **badge "Portal de Geociências"** (`mm-badge`) e as respetivas regras CSS; secções renumeradas. O badge do `menu.md` §2 é abandonado por decisão do cliente.
- `MegaMenuTest` atualizado: asserção `mm-panel-cab == 0` e ausência de "Portal de Geociências".
- Nota: remover não dispensa revalidação pela suíte — HTTP só prova HTML.

### 15.3 Top-bar: ícone de pesquisa recolocado + centro publicitário (11/11/2026)
- **Sintoma:** ícone de pesquisa não visível no header junto ao logo. **Causa provável:** `.mega-menu` em ≥1280px é `position:absolute; left:0; right:0` (full-width, z-index 20) — a área do botão fica por baixo/junto da barra e o clique é engolido. Por decisão do cliente o ícone move-se (em vez de fixar a posição antiga).
- **Pedido do cliente (exato):** "o icone de pesquisa não se encontra visivel remova o e implementa na outra sessão onde apresenta a hora no lado direito, e por outra nessa area foi dita que no centro vai se implementar area de publicidade (banner horizontal) e as redes sociais tambem".
- **`header.blade.php` reescrito** (top-bar): à **esquerda** as redes sociais (alinhadas com o rodapé: Facebook `fa-facebook-f`, X `fa-twitter`, Instagram, LinkedIn `fa-linkedin-in`, YouTube — substitui vk/pinterest; links `#` placeholder à espera dos URLs do cliente; Configurações do admin já gerem facebook/instagram/linkedin/youtube); ao **centro** `.top-banner-area` (área de publicidade horizontal — envolve o componente `<x-banner-publicitario posicao="topo" />` já existente; sem banner vigente não gera HTML, centro livre); à **direita** `.topbar-direita` (flex): `.date-holder` + `.search_btn.htact.show_search-btn` (sem tooltip).
- **Removido do header-inner:** o `<div class="search_btn …">`. O overlay `.header-search-wrap` (fadeIn/Out por `.show_search-btn`, `scripts.js`) **mantém-se** — posição independente.
- **Novo `public/css/topo.css`** (carregado no layout DEPOIS de `menu.css`): `.top-bar .container` posicionado (âncora do banner centrado); `.topbar-social` sempre à esquerda; `.top-banner-area` com `left/top:50%; transform:translate(-50%,-50%)`, imagem `max-height` 46px (1279→40px; 1064→32px; 630→26px), rótulo "Publicidade" a branco; `.topbar-direita` float:right + flex; `.date-holder` float:none; `.top-bar .search_btn` branco/bordas rgba(255,255,255,.2) (estava #666 em fundo escuro); ≤700px `.topbar-social` oculta.
- Layout `app.blade.php`: link `topo.css` após o `menu.css`. Validação HTTP: ordem social<banner<direita; `show_search-btn` ×1 (top-bar, antes do header-inner); `header-tooltip` ×0; VK/Pinterest ausentes; overlay intacto; `topo.css` 200; /directorio 200.

### 15.4 MySQL do XAMPP avariado — reparação integral (11/11/2026)
- **Sintoma:** `mysqld` morria no arranque; `mysql.exe`: ERROR 2002. `mysql_error.log`: `Fatal error: Can't open and lock privilege tables: Table '.\mysql\db' is marked as crashed and last (automatic?) repair failed`. (Avisos `adg_test` tablespace 672/174 são de OUTRO projeto — intocáveis.)
- **Reparação offline** (servidor parado) com `aria_chk` a partir de `C:\xampp\mysql\data`: `db` → 3 registos (o medidor reportou lixo "5931049794197352046" pré-fix; índices reconstruídos, State updated); `global_priv` 4; `tables_priv` 0; `procs_priv` 0.
- **`columns_priv`** (0 registos, mas 234 entradas de índice danificadas): `-r` falhou "aria_sort_buffer_size is too small. Current … 16384" — `--sort_buffer_size=64M` ignorado (sufixo M mal parseado; o motor faz clamp ao mínimo); `--sort_buffer_size=268435456` não resolveu o "Fixing index 1"; `--aria_sort_buffer_size` → "unknown variable". **Solução: `aria_chk -o -f`** (safe-recover via keycache, sem sort buffer) → exit 0, State updated.
- Verificação: `-c` nas 5 tabelas — todas limpas; `mysqld` arranca normalmente; `SELECT` em `mysql.user`/`mysql.db` OK; grants (%/test, test\_%, localhost/phpmyadmin) intactos. **MySQL PARADO no fim**.

### 15.5 Revalidação da suíte → 6 problemas → causa-raiz e fix (11/11/2026)
- Primeiro arranque pós-avarias: suíte **não passava**: 1 erro (MegaMenuTest — `ModelNotFound` em `Category::where('type','news')->where('slug', …)->firstOrFail()`: pais `petroleo-e-gas`/`geociencias` em falta) + 5 falhas ModeracaoDiretorioTest (**403** nos `can:document.manage` / `can:banner.manage`, incluindo as páginas admin dos documentos/banners).
- **NÃO era a Fase 5.1 nem os ajustes do cliente.** Causa-raiz: a BD `pigea_test` estava **desalinhada/antiga** (seed parcial/interrompida pela avaria): faltava o bloco do ContentSeeder que garante `Petróleo e Gás`/`Geociências` em todos os módulos, e existiam as **18 permissões ANTIGAS** (sem `document.manage`/`banner.manage`) enquanto o `RoleSeeder` atual define só as 11 novas → `can:` falha → 403.
- **Fix 1 (dados):** `artisan migrate:fresh --seed --force` contra `pigea_test` (`DB_CONNECTION=mysql DB_DATABASE=pigea_test DB_USERNAME=root DB_PASSWORD=""`) → 3 pais news; 44 `menu_group`; permissões document+banner (admin 11 / editor 8).
- **Fix 2 (bootstrap):** `tests/bootstrap.php` — check `COUNT(*) < 18` **substituído** por contagem por NOME: `permissions WHERE name IN ('document.manage','banner.manage') < 2 → db:seed RoleSeeder`. Contagem total não distingue permissões antigas de novas.
- **Resultado final: OK (145 tests, 534 assertions)** — revalidado após os ajustes do cliente (HTTP sozinho NÃO era suficiente: as 5 falhas de autorização passaram despercebidas).
- **Aprendizados** (aplicar a partir de agora): (1) após QUALQUER alteração — mesmo "só views"/remoções — correr a suíte antes de dar o passo como concluído; (2) a BD de teste pode ficar desalinhada se uma seed morrer a meio (a avaria expôs isto); os checks do bootstrap devem olhar para CONTEÚDO, não só contagens; (3) diagnóstico temporário (`diag-403.php`) criado e apagado no fim.
- Screenshots continuam pendentes (sem tooling de browser no ambiente).

### 15.6 Estado ao fim desta secção
- Fase 5.1 (mega menu + ajustes do cliente + top-bar reordenada) **CONCLUÍDA**. Suíte 145/534 verde. MySQL parado.
- Próxima: **Fase 8** — documentação final (`resumo.md`, `pub.md`, dead-code audit) + `reality-checker` como gate. Screenshots manuais pendentes.


---

## 16. FASE 8 da Reestruturação — Documentação e Limpeza Final (11/11/2026)

### 16.1 Documentação reescrita/publicada
- **`memoria.md`** v2.3 (estado) → v2.4 (fecho do gate, esta secção).
- **`resumo.md`** — reescrito do zero para o paradigma atual (Notícias + Diretório, painel admin, banners, menu, 149/545, produção pendente).
- **`pub.md`** — reescrito: descreve o sistema **implementado** (`banners` única; `<x-banner-publicitario />`; admin CRUD; rótulo "Publicidade"; desempate pelo mais recente). O spec antigo (`banner_slides` + Livewire) nunca existiu — agora obsoleto.
- **`fase_all.md`** — cabeçalho de progresso corrigido (0–8/5.1 ✅); tabela geral corrigida (sem duplicados/planeamento falso); secção FASE 5.1 corrigida no fecho da Fase 8 (Critério + Ajustes do cliente).

### 16.2 Remoção sistemática de código morto (grep antes de remover, sempre)
- Apagado **`resources/views/components/auth-modal.blade.php`** + o include `components.auth-modal` no layout `app.blade.php` — modal de login do tema ficou **sem trigger** após a remoção do `.show-reg-form` (Fase 5.1): dead UI em todas as páginas públicas; a porta única de login passou a ser `/login`.
- `public/css/style.css`: seletores `.srf_btn` (5 rules); bloco **do ticker** `.header_news-ticker-wrap` (57 linhas: `:before`, `li`, `li a`, `.hnt_title`, `.n_contr-wrap`, `.n_contr`, `.p_btn`); seletor ≤1064 agora só com `.aside-panel`; chaves equilibradas 955/955.
- `public/js/scripts.js`: bloco `.show-reg-form` + `showModal`/`hideModal`/`.close-reg-form` removido (código morto), comentário explica.
- `public/css/menu.css`: `.mm-panel-cab`/`.mm-panel-titulo`/`.mm-panel-subcat`(+`:hover`) removidos (cabeçalho do painel removido a pedido do cliente).
- Comentários "43" → **44** (main-menu.blade.php, MenuSeeder, MenuCategorias).
- CSS inerte do tema MANTIDO de propósito (`.main-register-*`, `.hnt_title`/`.n_contr` em `color.css` em listas partilhadas de 10KB): zero superfície de ataque sem markup/JS/triggers; edição em ficheiro third-party desproporcional ao risco. Decisão registada.
- View órfã `livewire/frontend/banner.blade.php` (Livewire descartado na Fase 6), `diag_banner.php`, `storage/framework/{diag,testing-check}.png` — apagados (zero referências).
- `storage/framework/views/*.php` (views compiladas) deixadas intactas (cache de runtime; não versionáveis em prática).

### 16.3 Gate final (reality-checker) — VEREDITO INICIAL: NEEDS WORK; corrigido antes de fechar
1. **P0 — `public/js/dashboard.js` intercetava TODOS os forms `form.auth-form, form.dashboard-form`** (login, reset password, 2FA, 11 forms admin — todos recebiam "Demo estático — guardado com sucesso. A integração com o Laravel ficará disponível na Fase 3." sem chegar ao backend). Lógica herdada do protótipo HTML, nunca limpa. **Impossível entrar/gravar via browser.** FIX: ficheiro reescrito — só os 2 handlers com elementos vivos (`.sidebar-toggle` e o modal "Adicionar utilizador"); `layouts/auth.blade.php` (login) deixou de incluir `js/dashboard.js`; `layouts/dashboard.blade.php` mantém (o admin usa sidebar + modal).
   - Restos da mesma família removidos: botão "Registar" (login → `/register` 404) e "Entrar com Google" (sem handler; sem OAuth); `.auth-tabs`/`.auth-divider` removidos; handlers `#registoform`, `auth-tabs`, `.js-mark-all-read`, `.js-remove-item`, `.dashboard-filters` (mensagem demo de "sem resultados" em qualquer mudança) removidos.
   - `UserFormController` atribuía `user` e `company` (herança do protótipo; a reestruturação prevê só `admin`/`editor`) → PAPEIS agora `['editor','admin']`, select igual; comentário explica (os legados continuam na BD de testes/dev, não atribuíveis pela UI).
2. **P1 — `backups/` dentro do repo** (BD SQLite pré-reestruturação com hashes) e `.gitignore` sem `backups/`, `composer`, `composer-setup.php` → `backups/` movido para `pigea/backups-2026-11-10` (fora do VCS); `.gitignore` atualizado (`/backups/`, `/composer`, `/composer-setup.php`, `/diag_*.php`).
3. **P1 — `Directory` legado público** via `/pesquisa` (8 entradas demo visíveis sem aprovação) → `SearchController::MODELOS` perde `Directory::class` (+import); as 8 entradas de demonstração deixam de aparecer em lado nenhum público (artigos Directory continuam apenas no admin).
4. **P2 — NIF uniqueness só na app**: nova migration `2026_11_11_000000_add_unique_index_to_companies_nif` (índice único BD; dev verificado sem duplicados). `RegistoEmpresa` apanha `QueryException` 23000 → mensagem pt (nunca 500).
5. **P2 — anti-spam**: `throttle:30,1` em `/registo-empresa` e `/registo-profissional` (limite por IP; não bloqueia a suíte — ~6-7 posts/min).
6. **P2 — URLs**: `url:http,https` (era `url`) em redes sociais do `registo-empresa`, `link_download` (documento-form) e `imagem_link`/`link_destino` (banner-form) — bloqueia `javascript:`.
7. **P2 — enum `email_template`** sem `nova-candidatura` (herança de vagas) na validação (`SettingsController`) e no select (`configuracoes.blade.php`).
8. **P2 — incoerências documentais** (todas corrigidas): `pub.md` (upload real: `file|image|mimes:png,jpg,jpeg,webp|max:4096`; criação exige `required_without:imagem_upload`), `resumo.md` (passkeys desativadas no Fortify; sem sessão → redirect `/login`, sem role → 403; a referência a §16 existe agora), `fase_all.md` (cabeçalho e tabela corrigidos; FASE 6/7/8 já não dizem "Planeado"), comentários "43" → 44.
9. **Cobertura de login (ar)**: **nenhum teste exercitava POST /login** (por isso a navegação HTTP 200 não apanhou o P0). Novo `tests/Feature/LoginTest.php` (4 testes): página 200; credenciais válidas → 302 + autenticado; password errada → sessão com erros + guest; `/register` 404 (GET e POST).
10. **P1 — trabalho por commitar** (94 ficheiros; remoto GitHub configurado) — registado para o cliente (o assistente não faz commit sem instrução explícita); backend/CI fora do âmbito desta entrega.
11. **CSP** mantém-se **Report-Only** (ver `CspTest`) — decisão de endurecimento para quando a auditoria de inline JS do tema for feita; a regra fica registada.

### 16.4 Resultado ao fim da Fase 8
- **Suíte: OK (149 testes, 545 asserções)** — inclui os 4 testes novos de login.
- Verdições HTTP: home 200; `/login` 200 sem `btn-google`/`.auth-tabs`; POST `/login` alcança o Fortify (respostas próprias, não "demo"); modal eliminado do markup; ícone de pesquisa na top-bar; `auth-modal` ausente.
- **MySQL do XAMPP** necessário para testes: arrancado ad hoc para correr a suíte, **parado no fim**.
- **Re-gate (reality-checker): executado após estas correções** — ver `memoria.md` v2.4 e `fase_all.md` (Critério da Fase 8).
- **Pendente manual** (sem tooling): passagem visual em browser real das jornadas login → painel → criar conteúdo → publicar; painel de registo empresa → aprovação → visível; top-bar; mega menu (3 regimes). E decisões do cliente: aba de **Profissionais** no `/directorio`? URLs das redes sociais (`#`)? `ADMIN_PATH` não óbvio em produção? SMTP para envio de e-mails (o mailer atual é `log`)? Negar/aprovar os registos demo pendentes em produção?

### 16.5 Aditamento — 2.ª passagem do reality-checker (final; veredicto PASS COM RESERVAS)
A segunda passagem confirmou o P0 corrigido e os P1/P2, mas encontrou mais 3 resíduos da mesma família e um problema de máquina:
1. **`scripts.js` intercetava `#contactform`** (o formulário Livewire **vivo** de `/contactos`): mostrava mensagem fake de sucesso e chamava `form.reset()` — **o utilizador perdia a mensagem escrita** mesmo quando o servidor validava com erro (o Livewire chega ao backend na mesma; a perda era de input, não de delivery). Bloco removido. Também removidos: `#registoform` (sem elementos vivos) e o bloco Mailchimp (`ajaxChimp` + traduções `eng`) — sem `#subscribe` no markup, e a URL externa de lista morta. A interceção Livewire agora é 100% da app.
2. **Anti-spam ineficaz** — `throttle:30,1` nas rotas de registo limitava apenas os **GETs**; o envio real vai por `POST /livewire/update`, que não tem throttle. Fix: **`RateLimiter` no servidor dentro dos dois componentes** (`registo-empresa:` / `registo-profissional:`, 10/min por IP e formulário — folgado de propósito; a moderação no /admin + unique NIF são o backstop). O throttle GET mantém-se como camada extra.
3. **`resumo.md` ainda dizia "2FA/passkeys disponíveis"** — corrigido: passkeys **desativadas** (comentário da própria `config/fortify.php`); e "404 fora da equipe" → sem sessão redirect `/login`, autenticado sem role **403**.
4. `.phpunit.result.cache` apagado (acumulava falhas antigas; artefacto de runtime; já está no `.gitignore`).
5. **MySQL do XAMPP instável nesta máquina** (morreu durante a 2.ª verificação com queries presas em `Statistics`): a suíte **revalidada DEPOIS destes fixes** → **OK (149 testes, 545 asserções)**. O volume `pigea_db` (projeto paralelo na mesma instância) intocado. **MySQL parado no fim; `.phpunit.result.cache` eliminado** (o PHPUnit regenera-o limpo).
6. **Veredicto 2.ª passagem: PASS COM RESERVAS** — entrega **UAT apenas, nunca produção nesta passagem**. Reservas: validação visual/browser pendente (screenshots das jornadas login/painel/registo/banner); sem SMTP (`MAIL_MAILER=log`); CSP ainda Report-Only; `ADMIN_PATH=admin` por default documentado mas não-obscurecido; trabalho não commitado (153 entradas — decisão do cliente); decisões do cliente pendentes (ver §16.4).


---

## 17. Remoção completa do sistema de comentários (comentarios.md, 11/11/2026)

### 17.1 Levantamento (não havia sistema vivo — contador + HTML estático)
A instrução `comentarios.md` mandava remover o sistema de comentários do frontend, backend/BD e CMS admin.

- **Não existiam**: tabela `comments`, Model `Comment`, componente Livewire de comentários, nem rotas de submissão/listagem de comentários. Também não havia UI no admin (moderação, contadores) — verificado com grep em views/controllers.
- **Existiam**: (a) coluna `comments_count` nas 10 tabelas de conteúdo (news, projects, suppliers, events, legislation, research, interviews, technologies, directory, training); (b) contadores no markup público (`<li>…fa-comments-alt…comments_count</li>`) em ~20 views; (c) ícone/balão de comentários e secções estáticas de comentários (com comentários fictícios e formulário "Comentar") nas páginas `news/show` e `projects/show`; (d) opção de ordenação "Mais Comentados" nos filtros de notícias/entrevistas; (e) 104 entradas `"comments_count":` no seed JSON.
- O único resíduo mantido de propósito: o campo `comments` do **formulário de contacto** (textarea `name="comments"` do Livewire ContactForm) — outra coisa; e `lang/pt/actions.php` sem a chave `comment` (removida; 0 refs).

### 17.2 Remoções do frontend (views)
- 38 linhas `<li><i class="far fa-comments-alt"></i> {{ $item->comments_count }}</li>` removidas de 19 Blade files (home, fragments/news, news/index, news-filter, interview-filter, projects/index, events/index, legislation/index, research/index, suppliers/index, technologies/index, training/index, search/index, news/show, projects/show, …).
- `resources/views/news/show.blade.php` (491→404 linhas) e `projects/show.blade.php` (530→443 linhas): removidos os blocos completos de comentários estáticos (incl. subtítulos "Comentários 3"/"Deixar um Comentário", listas `commentlist`, formulário `add-comment`, tooltip "Escrever um comentário"/"Reportar" morto — o `Reportar` era `href="#"` sem função).
- `news-filter.blade.php` / `interview-filter.blade.php`: opção `<option>Mais Comentados</option>` removida.
- Páginas validadas por HTTP: home 200, /noticias 200, /projetos 200, /eventos 200, /formacao 200, /tecnologias 200, /pesquisa?se=mapa 200, /noticias?cat=geociencias 200, /contactos 200, /registo-empresa 200, notícia/projeto show 200 sem secção `single-post-comm` nem "Escrever um comentário".

### 17.3 Remoções do backend / BD
- **Migration nova** `2026_11_11_000001_drop_comments_count_from_content_tables.php` — drop da coluna nas 10 tabelas com guard `Schema::hasColumn` (no-op em instalações novas; registada em `pigea_test` e dev).
- **Criação atualizada**: `2026_09_28_220002_create_content_tables.php` já não cria `comments_count`.
- **10 modelos** (News, Project, Research, Event, Legislation, Interview, Technology, Training, Supplier, Directory): `comments_count` removido do `$fillable` e dos `$casts`.
- **ContentSeeder**: `'comments_count' => …` removido (o seed JSON foi limpo — 104 entradas). **ContentFormController**: `$modelo->comments_count = 0;` removido. **NewsFilter/InterviewFilter**: regra de ordenação "Mais Comentados" removida.
- **Seed JSON** (`database/data/*.json`): `comments_count` removido de `directory`, `events`, `interviews`, `jobs`, `legislation`, `news`, `projects`, `research`, `suppliers`, `technologies`, `training` (o ficheiro `jobs.json` é legado não lido pelo seeder atual). JSON validado com Node.
- **CMS admin**: nenhuma funcionalidade de moderação/contadores de comentários existia — nada a remover (verificação registada).

### 17.4 Incidente — BOM acrescentado aos ficheiros reescritos (corrigido)
As remoções por linha usarem `[System.IO.File]::WriteAllLines(..., [System.Text.Encoding]::UTF8)` em PowerShell escrevem **BOM (EF BB BF)** — o projeto não tem BOM nos ficheiros originais, e PHP põe o BOM como output antes dos headers (risco real de "headers already sent"). Verificado e corrigido: **39 ficheiros** (10 modelos, 19 views, style.css, scripts.js, lang/actions.php, 13 JSON de dados) — preâmbulos removidos com reescrita byte-a-byte, verificação com `php -l`, Node JSON.parse e re-scan (0 restantes).

### 17.5 Incidente — tabelas de privilégios MySQL danificadas de novo
O MySQL do XAMPP (MariaDB 10.4.32) crasha-se periodicamente nesta máquina e as tabelas Aria de privilégios voltam a ficar corruptas: desta vez `REPAIR TABLE mysql.db` devolveu **"rows changed from 3 to 0"** (as entradas padrão XAMPP `%/test`, `%/test_%`, `localhost/phpmyadmin`) e o `pma` ficou com **access=0**. Reparado **server-side** (`ALTER TABLE mysql.db FORCE` + INSERT das 3 entradas — grants limitados à BD `phpmyadmin` e aos DBs de teste, inofensivos para pigea_test/pigea_db) + `FLUSH PRIVILEGES`. Verificação final: MySQL normal OK; root com `GRANT ALL`; **suíte verde 149/545**; MySQL **parado no fim**. O volume `pigea_db` (projeto paralelo do cliente na mesma instância) **intocado**.

### 17.6 Resultado
- Suíte: **OK (149 testes, 545 asserções)**; SQLite de dev sem a coluna; `pigea_test` com a migration registada; HTTP 200 em todas as rotas verificadas.
- Ambiente depois do trabalho: MySQL parado; views compiladas limpas (`view:clear`); nenhum ficheiro temporário deixado no projeto (scripts ps1/sql em `Temp\opencode\`).


---

## 18. Interface profissional do CMS — `cms_pro.md` (11/11/2026)
Implementação completa do spec do cliente (`cms_pro.md`): interface moderna do painel, focada na produtividade editorial. Tudo verificado ao vivo via HTTP (login real + HTML servido) e suíte **OK (159 testes, 572 asserções)**.

### 18.1 TinyMCE self-hosted (licenças/CSP)
- `tinymce@7.9.3` via npm (GPL-2.0-or-later) — nunca CDN (CSP `script-src 'self'`); copiado para `public/js/tinymce/` (**7.6MB, 206 ficheiros**: skins, icons, themes, plugins, models, tinymce.min.js, license.md).
- `package.json` e `package-lock.json` ganham o pacote; a página do editor carrega `/js/tinymce/tinymce.min.js`.

### 18.2 Contador de visitas real (`page_views`)
- Migration `2026_11_11_000200_create_page_views_table.php` (1 linha/dia: date unique + visits unsigned bigint). Não há dados pessoais (sem IP/cookie/UA).
- `App\Models\PageView` (`incrementarHoje` + `serieUltimosDias`).
- **Middleware terminable** `App\Http\Middleware\CountPublicVisits` registado no grupo `web` (`bootstrap/app.php`): conta apenas páginas públicas com resposta **200**; ignora método ≠ GET; ignora bots conhecidos (regex user-agent incl. Googlebot/curl/wget); ignora `ADMIN_PATH` **lido dinamicamente** de `config('app.admin_path')` + `admin` legado + `livewire`, `csp-report`, `storage`, `build`, `hot`, `favicon`; corre **depois** da resposta (zero latência).
- Testes novos `tests/Feature/VisitsCounterTest.php` (6): home conta 1; acumular 2; admin autenticado ZERO; admin anónimo (302 → ≠200) ZERO; 301 ZERO; notícia pública conta.

### 18.3 Dashboard redesenhado
- **4 cartões** (`stats-grid`): Notícias Publicadas (contador real + "+N este mês"), **Empresas Pendentes** (link direto para `admin.empresas` — aprovação), **Visitas no Site** (total + hoje), Banners Ativos.
- **Gráfico central** linha dos últimos 7 dias de visitas (`grafico-visitas7`) — série zq ero nos dias sem dados (nunca salta o eixo); mantidos "Novos utilizadores por mês" (barras) e "Distribuição por província" (pie).
- **Ação rápida**: cartão hero com botão grande "+ Nova Notícia" (`.quick-action`, borda lateral vermelha; `.btn-quick` com sombra).
- Removido o cartão decorativo "Conteúdo pendente" (botões sem handler) e o cartão "Profissionais pendentes" + "Utilizadores" (sai do spec dos 4 cartões).
- `.stat-card-link` hover (lift). `dashboard.css` ganhou regras mínimas CMS-Pro (paleta intacta, braces equilibrados 246/246, sem BOM, sem novos hex).

### 18.4 Sidebar reorganizada por grupos + role visibility + topbar "Ver site"
- **Grupos exatos do spec**: PRINCIPAL (Dashboard) · PRODUÇÃO (Notícias) · DIRETÓRIO (Empresas, Profissionais, Documentos) · COMERCIAL (Publicidade) · **SISTEMA (Utilizadores, Configurações — SÓ `admin`)**. Labels em `.sidebar-group` (10px, letter-spacing).
- **Ocultação por papel** (`hasRole('admin')` para o grupo SISTEMA; `can:` por item) — a proteção continua a ser o middleware (ocultação é cosmética).
- Removidos do menu: Projetos, Eventos, Legislação, Investigação, Entrevistas (decisões do cliente — painel enxuto; as páginas continuam reachable por URL, ver Fase 10 `fase_all.md`).
- **Topbar**: botão **"Ver site"** (abre frontend noutro separador, `rel=noopener`) + **sino com badge real** (Empresas+Profissionais pendentes em moderação; oculto a 0). Avatar/nome/logout mantidos.

### 18.5 Editor de Notícias — Livewire full-page ("o ecrã mais importante")
- **Componente** `App\Livewire\Admin\NewsEditor` (modos criar/editar) com `#[Layout]` via wrapper `admin/noticia-editor.blade.php` (breadcrumb `Admin › Notícias › Nova/Editar notícia`).
- **Layout 2 colunas** (70/30, `.editor-grid`): esquerda = Título, Subtítulo ("Linha fina", máx 500 ch, legacy excerpt), corpo TinyMCE (`.dashboard-form-textarea` + hidden `#conteudo` com `wire:model.live.debounce.5000ms`); direita = AÇÕES (Pré-visualizar · Guardar rascunho · Publicar), Categoria (`type=news`), **Imagem de capa** (upload ≤2MB + help «Recomendado: 1200x675px (16:9). JPG, PNG, WEBP. Máx: 2MB.»), **Imagens de contexto** (multiple ≤4MB + help «Recomendado: 800px de largura…»), Vídeo (YouTube, `url:http,https`), Documento (PDF, `url:http,https`).
- **Pré-visualizar**: modal `.dashboard-modal` com o estado atual do formulário (**sem gravar** — `temporaryUrl()` para uploads; classes `public.css` para o conteúdo renderizado: `.has-drop-cap`, `blockquote`, `.texto-duas-colunas`, `.news-preview-*` adicionadas a `dashboard.css` § news-preview).
- **Autosave**: `updated()` → guarda rascunho silenciosamente a cada alteração no Livewire, com debounce 5s (`wire:model.live.debounce.5000ms`); mensagem inline «Guardado às HH:MM» (`.autosave`). Rascunho aceita texto parcial; **Publicar exige** título + conteúdo + categoria.
- **Botões**: Pré-visualizar (`wire:click="abrirPreview"` → modal), **Guardar rascunho** / **Publicar** (`btn-secondary`/`btn-success`) — públicos para news apenas.
- **Conteúdo special styles** no TinyMCE: `Drop Cap` (`.has-drop-cap:first-letter`), `Blockquote` (`blockquote`), `Duas colunas` (`.texto-duas-colunas`, 2 colunas — regra adicionada ao CSS público E ao dashboard.css para o preview).
- **Capa Spatie** `capa` (coleção) + coluna legada `image` (`storage/…`); contexto → coleção `contexto`; slug automático (`slugUnico`).
- Rotas: GET `gestao-pigea/noticias/nova` (`admin.noticia-nova`), GET `gestao-pigea/noticias/{noticia}/editar` (`admin.noticia-editar`) — ganham precedença do `/{tipo}/{id}/editar` (notícias NUNCA cai no form genérico; redirect defensivo em `ContentFormController::edit()` inalcansável).
- **WebForm antigo apagado**: `Admin\NewsFormController` (e vista `admin/noticia-nova.blade.php`) — zero referências; wrappers ativos.
- **Conteúdo JS inline** (TinyMCE init + sync `#conteudo`→`wire:model.live.debounce`) — sem ficheiro .js adicional.

### 18.6 Help texts e imagens (spec §5)
- Notícias capa: «Recomendado: 1200x675px (16:9). Formatos: JPG, PNG, WEBP. Máx: 2MB.»
- Notícias contexto: «Recomendado: 800px de largura. Formatos: JPG, PNG, WEBP.»
- Banners: «Dimensão exata: 970x250px (Leaderboard). Formatos: JPG, PNG. Máx: 4MB.» (`banner-form`)
- Logótipo (Configurações > Geral): «Recomendado: 400x100px (Fundo transparente PNG).»
- **Spec §4.2 não incluiu** texto de ajuda para favicon — mantém «PNG 32x32» existente.

### 18.7 Testes atualizados/novos
- `AdminFormsTest`: 2 testes de «criar notícia» migrados para o editor Livewire (`set` + `call('publicar')`) em vez do POST à rota removida; asserts por `Livewire\TestResponse`.
- `AdminContentFormTest`: noticias em «edição» → agora asserta a página do editor (`assertOk` + `assertSee('id="titulo"')` + `assertDontSee('name="titulo"')`) — o generic endpoint nunca responde para noticias (precedência de rota).
- **Novos**: `NewsEditorTest` (4 tests: rascunho DB; publicar exige conteúdo+categoria; publicar sucesso → published+redirect; edição/excerpt preservado) · `VisitsCounterTest` (6 tests). Suíte **OK (159 testes, 572 asserções)**.

### 18.8 Notas de ambiente (importantes)
- O cliente alterou `ADMIN_PATH` do `.env` para **`gestao-pigea`** neste dia (antes desta fase o painel vivia em `admin`). Roles/middleware/`role:admin,editor` e config('app.admin_path') continuam — obscurecimento não substitui autorização.
- O servidor de dev é gerido pelo ambiente (não matar processo por suposição); bootstrap lê `.env` por arranque.
- **MySQL do XAMPP** instável (crash cíclico) — esta sessão começou com um mysqld zombi pendurado (force-kill sem efeito nos privilégios — verificado íntegro: `aria_chk -c` todas OK; pigea_test intacta; suíte verde após).


---

## 19. Correcoes do ui-finish-gate + acessibilidade WCAG AA (11/11/2026)

Passagem de qualidade completa apos o CMS Pro (§18): auditorias `ui-finish-gate-reviewer` e `accessibility-auditor`. Nenhuma correccao foi feita pelos auditores — todas as listadas abaixo foram aplicadas manualmente e revalidadas (suite: **OK 159/572**; HTTP admin: 200 em todas as paginas; verificação byte-level de UTF-8 valido em todos os ficheiros de views e CSS).

### 19.1 Achados criticos do ui-finish-gate e correcoes aplicadas
- **Sino invisivel no desktop**: `.header-icon` e `.header-logout` passaram a `display: inline-flex` sempre (o plugin anterior escondia-os com `display:none`); o spec exige sino sempre visivel.
- **Sino vira link real para a fila de moderacao** (`route('admin.empresas')`); `aria-label` com a contagem de pendentes; role=link correto; badge só visivel com pendentes (>0).
- **Sidebar off-canvas**: breakpoint corrigido para ≤1064px (era ≤992px — alinha com o frontend publico e com o spec; existia uma zona 993–1064px sem toggle).
- **Contraste AA corrigido**: `.sidebar-group` #6E6E6E→#A0A0A0 (≈6.7:1); `.help-text` #8A8A8A→#666666 (≈5.7:1); `.sidebar-logout a` (vermelho sobre fundo escuro, ≈1.9:1) — cor de texto ajustada para ser legivel em fundos claros e escuros.
- **Aprovar com cor de sucesso (#2E7D32)**: tabelas Empresas/Profissionais passaram a usar `.icon-btn.success` no botao de aprovar, seguindo o padrao de sucesso/perigo definido no CSS global.
- **Grafico central vs auxiliares**: `.chart-box-principal canvas` com altura 260px — destaque visual para a linha dos 7 dias de visitas.
- **Rodape de "Empresas pendentes"**: classe `.stat-card-footer.pending` (dourado #A06F00) em vez de verde — o estado pendente/em triagem nao deve parecer concluido.
- **Badge neutral para "Inativo"**: `banners-table` `rejected` → `neutral` (cinza), vermelho só para "Eliminar".
- **`help-text-ok`** criada para estados de sucesso inline (substitui `style="color:#2E7D32"` inline).
- **Mojibake corrigido**: 1 arquivo CSS reescrito com comentários em ASCII puro (a abordagem de 'degrad'+byte-alteracao produz bytes impossiveis de destracinar — nenhum byte especial restante).
- **`th scope="col"`**: todas as tabelas admin ganharam o atributo `scope="col"` (banners/empresas/professores/documentos/utilizadores/content-table).
- **Labels associadas**: 18 paginas admin — todos os breadcrumbs passaram de `<ul>` para `<ol>` com `aria-current="page"` no ativo; erros de formulario ganharam `role="alert"` (67 instancias); selects de filtro ganharam `aria-label`; paginacao virou `<nav aria-label="Paginacao">`; `@class(['active' => …]) aria-current="page"` na pagina atual; botoes "»" com `aria-label="Pagina seguinte"`.
- **TinyMCE em portugues**: `tinymce-langs@1.0.0` instalado como devDependency; `public/js/tinymce/langs/pt_PT.js` servido localmente; `language: 'pt_PT'` + `language_url` no `tinymce.init`. Dialogs, menus, hints e toolbars em portugues.
- **`focus-visible` global**: anel de foco vermelho (#C8102E) de 2px + offset em todos os controlos (links, botoes, icon-btn, sidebar-item a, stat-card-link, switch, modal-close, breadcrumb a). Antes, o `plugins.css` apagava todos os outlines sem substituir.
- **Modais accessiveis**: `.dashboard-modal.open` ganhou focus e ESC fecha com retorno do foco ao trigger; `.livewire-close` com foco no botao de fechar; rotulos "Aria-label" em todos os botoes icone-so; `.status-badge.active/pending/neutral` com contrastes AA verificados.

### 19.2 Implementacao / validacao
- **Suíte OK (159 testes / 572 assercoes)**; HTTP: todas as paginas admin com conteudo real (dashboard, noticias, gestao-pigea/noticias/nova, empresas, profissionais, documentos, banners, utilizadores, configuracoes) a devolver 200 com textos em portugues correto.
- **Verificacao byte-level**: nenhum ficheiro de view/CSS contem mojibake (bytes FF FD); todos os ficheiros de CSS validados com chaves equilibradas e sem "BOM";ficheiros .json do seeder intactos.

### 19.3 Itens que ficam (decisao consciente)
- **Visual manual em browser** pendente (screenshots das jornadas login/painel/criacao/preview/publicacao de noticias) — recomendacao prioritaria antes de handoff ao cliente.
- **h1 por pagina** mantida por conveniencia (`.card-title` aponta para h1 em paginas de lista/formulario); skip-link aggiunged ao layout; `id="conteudo"` no `<main>` do layout; "Menu de administracao" nomeado na sidebar; "Vu"adicionado ao bell com simbolo de pendentes.



## 20. Melhoria do Editor de NotÃ­cias e do CMS â€” `cms_v.md` (11/11/2026)

**Ámbito:** migrar o editor para Blade tradicional (já feito em Fase 10.1), depois `cms_v.md` completo — artigo idêntico no editor, na pré-visualização e no site; conteúdo seguro; painel apresentável.

### 20.1 Fase 1 — `.article-body` único (site público + preview + TinyMCE)
- **Novo** `public/css/article.css` — estilos do corpo (parágrafos, H2/H3, letra capitular vermelha (#C8102E), citação destacada (faixa vermelha + aspas), coluna estreita, duas colunas (1 col em ≤768px), figura media-left/media-right com `figcaption`, imagens, tabelas, listas, links. Nenhuma cor nova — todas as cores já existiam no código.
- **Novo** `news/partials/article-body.blade.php` — parcial Blade único (`<div class="article-body">{!! $content !!}</div>`), usado pelo show público e pela pré-visualização do editor.
- `news/show.blade.php`: `{!! $post->content !!}` → `@include('news.partials.article-body', ['content' => $post->content])` + `@push('styles')` liga `/css/article.css`.
- `layouts/dashboard.blade.php`: `article.css` adicionado ao head (preview modal renderiza `.article-body` no admin).
- **Removidas** do `dashboard.css` as regras `.news-preview-content*` (mortas) e do `style.css` os blocos legados `.texto-duas-colunas` e `.has-drop-cap` (consolidados em `article.css`); órfão "CMS Pro" comentário apagado.
- **Notícia seed normalizada**: `database/data/news.json` — `has-drop-cap` → `drop-cap`, `texto-duas-colunas` → `two-cols` (2 ocorrências), BOM removido (JSON válido).

### 20.2 Fase 2 — TinyMCE completo + fonte única `config/article.php`
- **Novo** `config/article.php` — lista branca única: elementos, atributos por elemento, classes (`drop-cap`, `pull-quote`, `narrow`, `two-cols`, `media-left`, `media-right`), schemes de URI (`http`, `https`, `mailto`). Usada pelo TinyMCE e pelo purifier do servidor.
- **Novo** `public/js/admin/news-editor.js` (movido do inline @push): `tinymce.init` completo — `content_css: /css/article.css`, `body_class='article-body'`, `menubar:'edit view insert format table'`, plugins `advlist autolink lists link image table code fullscreen wordcount searchreplace autoresize visualblocks`, height 520, `promotion:false`, `elementpath:false`, `statusbar:true` (wordcount), pt_PT, H2/H3 `block_formats`, toolbar agrupada com `blocks` e **menu "Estilos do PIGEA"** (`styles`) com Letra capitular/Citação destacada/Coluna estreita/Duas colunas/Imagem à esq./à dir. Substitui os botões soltos dropcap/blockquote/twocols. `valid_elements`/`valid_classes` alinhados exatamente com o whitelist do servidor; `paste_data_images:false`; `triggerSave()` explícito no submit.
- **Removido** do blade todo o inline JS (fica apenas `<script src="js/tinymce/tinymce.min.js">` + `<script src="js/admin/news-editor.js">`).
- **Pré-visualização**: o modal agora obtém o corpo por `POST {ADMIN_PATH}/noticias/pre-visualizar` (fetch do formulário), que sanitiza e devolve o HTML do parcial — **o admin mostra sempre o mesmo markup do site**, nunca conteúdo confiado sem sanitizar.
- **Validade**: `php -l` não se aplica a JS; `node --check public/js/admin/news-editor.js` OK.

### 20.3 Fase 3 — Sanitização (Ezyang HTMLPurifier)
- `composer require ezyang/htmlpurifier` (v4.19) — `composer.json/lock` atualizados (responsabilidade explícita da Fase 3 da spec).
- **Novo** `app/Services/ArticleSanitizer.php` — `ArticleSanitizer::clean()`. Detalhes de implementação: DTD XHTML 1.0 não tem `figure`/`figcaption` → adicionados via `maybeGetRawDefinition('HTML')` com `HTML.DefinitionID`+`Cache.SerializerPath` (`storage/framework/cache/htmlpurifier`, ignorado pelo git); `loading` adicionado a `img`; módulo Target XHTML 1.1 **não** usado (conflita com `a`) — `target` adicionado via `addAttribute`; `rel="noopener noreferrer"` em todo `target=_blank` garantido por pós-processamento regex. Cache removido após builds (limpa artefatos locais).
- **Testes** (`tests/Feature/ArticleSanitizerTest.php`): `<script>` removido; atributos `on*` removidos; `style` removido; classes desconhecidas removidas, classes PIGEA preservadas; `javascript:` removido; `iframe` removido; `h1` removido, `h2` mantido; classes `media-left/right`/`two-cols` preservadas; `target=_blank` ganha `rel`; vazio/null → null.
- `NewsEditorController::atributos()` passa o conteúdo por `ArticleSanitizer::clean()` ao guardar; o endpoint de pré-visualização também sanitiza.

### 20.4 Fase 4 — Upload de imagens no corpo
- **Novo** `app/Http/Controllers/Admin/NoticiaImagemController.php` — POST `{ADMIN_PATH}/noticias/imagem` (autenticado, `can:news.manage`, `throttle:20,1`). Valida mimetype real (`image/jpeg`, `image/png`, `image/webp`), máx. 4 MB; GD ativo (presente na máquina): resize para ≤1200px, conversão WebP q85; grava em `noticias/corpo/AAAA/MM/` via `Storage::disk('public')`; devolve JSON `{location}`. **Não** usa Spatie Media Library (a notícia pode ainda não existir). Textos de erro em PT; JSON 422/3xx.
- **Novo teste** `tests/Feature/NoticiaImagemUploadTest.php` — convidado redireccionado; formato inválido → 422; 5MB e JPG >4MB → 422 com mensagem PT; upload válido → 200 + `location` `.webp`.

### 20.5 Fase 5 — Aspecto do painel
- **Hierarquia**: Publicar = `btn-primary`, Guardar rascunho = `btn-secondary`, Pré-visualizar = `btn-ghost` (todos largura 100% na coluna via `.editor-actions-body .btn`).
- **Sticky**: `.editor-actions { position: sticky; top: 88px }` (o botão Publicar nunca sai do ecrã).
- **Breadcrumb**: único para `ol` e `ul` (`flex-wrap: nowrap; overflow: hidden`), `aria-current` mantido; **skip link** adicionado a `layouts/dashboard.blade.php` ("Saltar para o conteúdo") — WCAG 2.4.1, `<main id="conteudo-principal" tabindex="-1">`.
- **Topbar**: nome + **papel** (Admin/Editor/Colaborador) ao lado do avatar; **logout duplicado removido da sidebar** (fica apenas no topbar); **Sino** já era `<a>` para `admin.empresas` (fase 10.2) — mantém.
- **Uploads em PT** (componentes novos `.file-upload`): área de largar `is-dragover`, botão «Escolher ficheiro(s)», pré-visualização inline, nome+tamanho, botão «Remover», erro PT; usado em capa e contexto do editor.
- **Toast global** (`window.PIGEAToast` no `dashboard.js`, substituindo o flash simples): `.toast`, `.toast.success`, `.toast.error`, fechar individual, região `aria-live="polite"`, 5s auto-dismiss; flash simples das views (notícias/editor) troca para `data-flash-toast` mapeado para toast.
- **Modal de confirmação genérico** (`#confirm-modal` no layout, `js-confirm`/`confirm-action` no `dashboard.js`, `btn-danger`): pronto para apagar (fila/admin pages fornecem `data-*`).
- **Estado vazio** (`.empty-state`) pronto e usado na lista de notícias.
- **Loading** em submits (`data-loading-submit` + `.is-loading` com spinner CSS).

### 20.6 Fase 6 — Campos em falta e fluxo editorial
- **Campos novos**: Slug (editável, input mono, **verificação de unicidade AJAX** via `/gestao-pigea/noticias/slug-unico`), Etiquetas (`tags`), **SEO recolhível** (`meta_title` ~60, `meta_description` ~160, com contadores), Resumo (`subtitulo` → `excerpt`, contador 500), Título com contador 190, **Texto alternativo** (`image_alt` — nova coluna, migration aditiva segura) + **Crédito da imagem** (`image_caption`).
- **Estados**: `draft` → Rascunho · `em_revisao` → Em revisão · `published` → Publicado. **Editor não publica** (a não ser com `EDITOR_PODE_PUBLICAR=true`): editores vêem «Submeter para revisão»; Admin vê «Publicar» (+ «Devolver a rascunho» e «Despublicar» quando published). Permissão forçada no servidor (`verificarPermissaoPosse`), defesa em profundidade — não só pela vista.
- **`config('cms.editor_pode_publicar')`** (env `EDITOR_PODE_PUBLICAR`, default **true** — não restringe o fluxo existente; mudar para `false` é uma linha no `.env`).
- **Fluxo redirect pós-publicar/editar/ações**: publicar → lista + toast «Notícia publicada!»; outros → editor + toast correspondente («Rascunho guardado.», «Enviada para revisão.», …).
- **Badge de estado** no editor (ao lado do card-title, mapeamento `draft→neutral`, `em_revisao→pending`, `published→active`).
- **slug**: `Rule::unique` + verificação independente `slugUnicoCheck`/JS debounce 400ms.

### 20.7 Fase 7 — Lista de notícias (`NewsListTable`)
- **Novo** `app/Livewire/Admin/NewsListTable.php` + `resources/views/livewire/admin/news-list-table.blade.php` (o `ContentTable` genérico fica intacto para os restantes módulos).
- **Colunas**: Título · Categoria · Autor · Estado (badge) · Data · Visitas · Ações (editar · ver (só se published) · **duplicar** · apagar→lixo · restaurar quando na vista de lixo).
- **Filtros**: estado (published/draft/em_revisao), categoria, autor (com autores distintos reais) + **busca por título** (debounce 500ms) + **ordenação por coluna** (`ordenarPor()`, `aria-sort` via `↑/↓`).
- **Lixo**: checkbox «Só apagados» alterna para `onlyTrashed()`; «Eliminar» vai para o lixo (SoftDeletes); «Restaurar» devolve.
- **Estado vazio**: `.empty-state` («Nada encontrado») + botão limpar filtros.
- **Paginação** com `wire:click=gotoPage` para `<nav aria-label="Paginação">`.
- `noticias.blade.php` agora renderiza `livewire:admin.news-list-table`.

### 20.8 Fase 8 (opcionais — ambas implementadas)
- **8.1 Autosave**: `POST {ADMIN_PATH}/noticias/autosave` (rota + throttle 60/min), JSON `{id, titulo, conteudo, subtitulo, categoria}`; sanitiza corpo, **NUNCA publica**; apenas registos com id (edição); `beforeunload` com alterações não guardadas; indicador `Clous `.js` próprio (15 s, debounce) maso. Testes `tests/Feature/NoticiaAutosaveTest.php`.
- **8.2 Agendamento**: migration `2026_11_11_181000_published_at_date_to_datetime_news_table` (SQL direto, sem doctrine/dbal; datas preservadas às 00:00; coluna `published_at` mantém compat; cast `datetime` no model). Campo **«Publicar em»** (`datetime-local`) com `aria-describedby`; `publicar_em` futuro mantém status=published mas o artigo é invisível no público (`scopePublished` `published_at <= now()`) e fica visível na hora certa; **comando `news:publish-scheduled`** (idempotente, `--dry-run`) agendado em `routes/console.php` a cada minuto (`withoutOverlapping()`). **Pré-requisito**: Task Scheduler do Windows a correr `php artisan schedule:run` a cada minuto (não foi configurado por mim sem OK; o comando funciona manualmente).
- **Backup lógico pré-migration**: `backups/pigea_20261111_200133.sql` (992 KB) e `pigea_test_…` (968 KB).

### 20.9 Testes (suíte completa)
- `ArticleSanitizerTest` (9), `NoticiaImagemUploadTest` (4), `EditorFluxoEditorialTest` (6), `NewsListTableTest` (3 duplicar/apagar/restaurar/busca), `NoticiaAutosaveTest` (3), `AdminContentFormTest` atualizado para `NewsListTable`.
- **Resultado final: OK (181 tests, 631 assertions)** contra MySQL 8.0.46 (`pigea_test`, `utf8mb4`).

### 20.10 Ambiente (crítico)
- O **XAMPP foi descartado pelo cliente** — o projeto roda sobre a **instância MySQL 8.0.46 de sistema** (`MySQL80`, porta 3306). Dev aponta para `pigea`, suíte para `pigea_test` (charset `utf8mb4`).
- **Conta do projeto**: `pigea@localhost` + `pigea@127.0.0.1`, `ALL` apenas em `pigea.*` e `pigea_test.*` (sem acesso a `mysql.*`). Password forte (48 hex) no `.env` **local** (gitignored), nunca no git.
- `root/devs` fica **só para administração** (criar/manter utilizadores e grants). A password continua fraca — **mudar quando puder** (não foi alterada sem pedido explícito).
- XAMPP continua instalado mas **não é usado** pelo projeto (o PHP da app/testes ainda vem de lá). Se o XAMPP for desinstalado fisicamente, a app **quebra** — instalar PHP 8.2 + extensões do sistema primeiro.
- `phpunit.xml` ganha `<env name="DB_PORT" value="3306"/>` (working tree, **não comitado** — a decidir no teu PR/UAT).

### 20.11 INCIDENTES próprios corrigidos
- `...\news.json` ganhou **BOM** pela minha escrita automática (`UTF8` com BOM) — removido (`json valido: sim`).
- Nessa cadeia, **dois ficheiros de views/CSS voltaram a ganhar BOM** (noticia-editor.blade.php, dashboard.css, style.css) — removidos byte-a-byte; chavetas equilibradas (255/252/954 → verificado). Lição: usar sempre `[System.IO.File]::WriteAllText(..., New-Object System.Text.UTF8Encoding($false))` para **conteúdo**; `UTF8` simples com BOM só para ficheiros cujo target ID específico pede (nunca neste projeto).
- Falha inicial de montagem automática do `diagnostico_editor.md` (caminhos relativos → rascunho com placeholder truncado) — **rascunho apagado** pelo próprio autor; resultado final correto verificado.
- `composer require ezyang/htmlpurifier` — com sucesso; `composer audit` reporta **11 avisos em 6 pacotes** (pendência de atualização geral — não alterado sem OK).
- Comentários do config/NewsEditorController com mojibake legítimo do terminal não reconvertidos (UTF-8 Bom verdadeiro a nível de bytes).

### 20.12 NÃO VERIFICADO (honesto)
- **Browser**: nenhum teste visual interativo — TinyMCE a renderizar o iframe, drop-cap/duas-colunas ao vivo, sticky, modals, toast, responsivo 390px, navegação só por teclado + skip link efetivo, ícone `fa-times`, role dos botões.
- **upload de imagem no corpo ao vivo**: testa-se via PHPUnit (Storage::fake) — o caminho real com GD + WebP não foi verificado em browser.
- **Scheduler**: `news:publish-scheduled` precisa de Task Scheduler/cron — **não configurado** sem OK explícito.
- **Backup pessoal**: `database-sqlite-backup-20261111.sqlite` no `%TEMP%` pode desaparecer (temporários).


## 21. Recursos extras do editor (11/11/2026) — link genérico, capa por URL e categorias hierárquicas

**Ámbito:** a pedido do cliente após a Fase 11: painel de links (para além do vídeo), imagem de capa sem ficheiro, e categorias organizadas pai → subcategoria.

### 21.1 Link genérico (novo campo)
- Migration `2026_11_11_202000_add_link_to_news_table` — `ALTER TABLE news ADD COLUMN link VARCHAR(255) NULL AFTER documento_link`. **Aditivo**, sem doctrine/dbal.
- `NewsEditorController::validar()` — novo campo `link` (URL http/https).
- `NewsEditorController::atributos()` — `link` parcialmente preenchido limpo se vazio (consistente com os restantes links).
- `News.php` fillable — `link`.
- Vista do editor — campo «Link (URL)» após Documento (PDF).
- Campo público: **não** editado o markup do show da notícia para mostrar `link` — vídeo/documento/link externos continuam sem exibição no frontend; é o utilizador que decide e o link segue como simples metadata da edição (comportamento idêntico a `video_link` e `documento_link` hoje).

### 21.2 Capa por link (em vez de ficheiro)
- `NewsEditorController` — novo campo `capa_link` validado (`url:http,https`). Regra extra em `validar()` — `capa` (ficheiro) e `capa_link` MUTUALLY EXCLUSIVE: envia ambos ⇒ `ValidationException`.
- `NewsEditorController::processarMidia()` — se `capa_link` estiver preenchido, grava a URL diretamente em `news.image`; se `capa` (ficheiro), upload Spatie como antes.
- Vista editor — toggles «Ficheiro (upload)» / «Link (URL)» via radios `name="capa_fonte"`; JS `public/js/admin/news-editor.js` alterna `hidden` nos dois painéis e `disabled` nos inputs inativos.

### 21.3 Imagens absolutas no frontend
- **Novo** `app/helpers.php` — `image_url(?string $path, ?string $fallback = null): string` — retorna `$path` tal qual se começar por `http`, `asset()` caso contrário; `asset($fallback)` se `$path` vazio e fallback foi dado.
- `composer.json` — autoload `"files": ["app/helpers.php"]`; `composer dump-autoload`.
- **56 ocorrências** de `asset($item->image)` / `asset($noticia->image)` etc. em **16 views** substituídas por `image_url(...)`. Banner e avatar mantidos em suas formas (já suportavam http/local).

### 21.4 Categorias de notícias por hierarquia (optgroup)
- **Novo** `App\Support\CategoriasNews` — `mapa()`: `tops` (parent_id IS NULL, type=news, ordenados por name) + `filhos` (agrupados por parent_id).
- Vista editor — select de categoria substituído por:
  - `<optgroup label="Mineração|Petróleo e Gás|Geociências">` com opção «nome (geral)» + subcategorias indentadas;
  - `<option>` direto para os restantes tops (Água, Ambiente, Internacional, Investigação);
  - o grupo é renderizado só quando há filhos (derivado de `parent_id` — nada de "hard code" de nomes).
- **NewsListTable** — mesmo padrão hierárquico no filtro «Categoria» da lista.

### 21.5 Categoria «Outros»
- Migration `2026_11_11_201000_add_outros_category_to_news` — insere `type=news`, `slug=outros`, `name=Outros`, `order=999`, `parent_id=NULL`. Idempotente (`insertOrIgnore` com unique type+slug).
- «Outros» aparece automaticamente na lista hierárquica (como top sem filhos).

### 21.6 Testes e evidência
- Novo `tests/Feature/NoticiaEditorExtrasTest.php` — 4 cenários: link genérico gravado; capa por URL gravada; ficheiro+URL simultâneos → `ValidationException`; categoria Outros gravada.
- **Suite completa: OK (185 tests, 643 assertions)** — inclui todos os cenários das fases anteriores.
- `view:cache` OK; site público 200 em `/`, `/noticias`, `/login`.
