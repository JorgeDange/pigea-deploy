# 📋 Resumo do Projeto PIGEA

**Projeto:** PIGEA  
**Localização:** `pigea-deploy/`  
**Tipo:** Portal Web (Laravel 12 + Livewire 3 + Tailwind CSS 4 + Vite)  
**Idioma:** Português (pt-PT)  
**Banco de dados:** SQLite (desenvolvimento) / MySQL (testes automatizados: `pigea_test`; produção recomendado)  
**Data do resumo:** 11/11/2026

---

## 1. O Que é o PIGEA

O **PIGEA** é um **portal de notícias do setor de minas, petróleo & gás e geociências em Angola, com um diretório de empresas, profissionais e documentos**. A restruturação de 10/11/2026 (`restrutura.md`) fixou o novo paradigma:

- **Sem cadastro/login para empresas e profissionais** — eles submetem formulários públicos (`/registo-empresa`, `/registo-profissional`) e aguardam aprovação (status: `pendente` → `aprovado`/`rejeitado`).
- **Gestão exclusiva pela equipa interna** (roles Spatie apenas `admin` e `editor`), em painel próprio (`/admin…` sob prefixo obscurecido `ADMIN_PATH`, rotas `admin.*`).
- **Não existem**: Vagas, Candidaturas, Painel da Empresa, Painel do Utilizador, registo público. (Removidos na Fase 1 — detalhes em `modificar.md` §8.)
- **Diretório público** (`/directorio`): abas Empresas e Documentos; busca por nome/área; **NIF nunca apresentado**; apenas registos aprovados.
- **Publicidade**: banners geridos no admin (`banners`, posição `topo|sidebar|rodape`) renderizados estaticamente pelo componente `<x-banner-publicitario />`.

### Público-alvo
Visitantes em geral (leitura pública de todas as notícias/conteúdos) e equipa interna (admin/editor) com acesso ao painel de gestão.

---

## 2. Como Funciona

### Arquitetura Geral

```
Visitante → Navegador → Livewire (componentes UI) → Laravel (controladores/Livewire) → Models → BD
```

- **Frontend:** páginas Blade compostas com o tema PIGEA (limpo do legado) + Livewire 3 para partes reativas (`NewsFilter`, `DirectoryListing`, etc.).
- **Admin (`/admin…`):** componentes Livewire `app/Livewire/Admin/` (moderação CompaniesTable/ProfessionalsTable; CRUD DocumentsTable/BannersTable; gestão SiteSettings), protegido por `auth` + middleware `role:admin,editor` (granular via `can:<permissão>`).
- **Tema público:** CSS próprio sobre o tema (`menu.css`, `topo.css`); mega menu DB-driven (`categories` com `menu_group`).

### Áreas Funcionais

| Área | Descrição | Localização |
|------|-----------|-------------|
| **Notícias** | Capa Spatie Media (WebP), coleção `contexto[]`, `video_link`/`documento_link`, filtros por categoria `?cat=` | `News`, `NewsFilter` |
| **Projetos** | Projetos de minas/petróleo/geociências | `Project`, `/projetos` |
| **Pesquisa/Eventos/Legislação/Entrevistas/Tecnologias/Formação** | Repositórios de conteúdo | `Research`, `Event`, `Legislation`, `Interview`, `Technology`, `Training` |
| **Diretório público** | Empresas (aprovadas) + Documentos (download) | `/directorio`, `DirectoryListing` |
| **Registo público** | Formulários de empresa/profissional (pendentes) | `/registo-empresa`, `/registo-profissional` |
| **Painel Admin** | Notícias/conteúdos (`ContentFormController`, `ContentTable`), moderação (`CompaniesTable`, `ProfessionalsTable`), Documentos (`DocumentsTable`+`DocumentFormController`), Banners (`BannersTable`+`BannerFormController`), Configurações (`SettingsController`, endereço + redes sociais), Utilizadores | `app/Livewire/Admin/`, `app/Http/Controllers/Admin/` |
| **Autenticação** | Login exclusivo da equipa (Fortify sem registo público); 2FA disponível; **passkeys desativadas** | `Fortify`, `FortifyServiceProvider` |
| **Menu público** | 9 itens com mega menu (DB-driven); item ativo por rota/`?cat=` | `components/main-menu.blade.php`, `App/Support/MenuCategorias` |
| **Publicidade** | Banner `topo|sidebar|rodape` com período e ativo; sem link → não clicável; desempate pelo mais recente | `<x-banner-publicitario />`, `banners` |
| **Transição `/diretorio` → `/directorio`** | Ortografia do cliente; antigo `/diretorio` → **301** | `routes/web.php` |

### Papéis e Permissões (Spatie)

- `admin` — todas as permissões (`syncPermissions(Permission::all())`).
- `editor` — `news.manage`, `project.manage`, `event.manage`, `legislation.manage`, `research.manage`, `interview.manage`, `document.manage`, `banner.manage` (**sem** moderação `company.moderate`, `user.manage`, `settings.manage`).
- Sem registo público nem roles para empresas/profissionais.

---

## 3. Estrutura de Diretórios e Componentes

```
pigea-deploy/
├── app/
│   ├── Http/Controllers/        # Admin (Content, Document, Banner, Settings, Dashboard) + Frontend (Search, Directory)
│   ├── Livewire/
│   │   ├── Admin/               # CompaniesTable, ProfessionalsTable, DocumentsTable, BannersTable, ContentForm...
│   │   └── Frontend/            # NewsFilter, DirectoryListing, ContactForm, InterviewFilter, RegistoEmpresa/Profissional
│   ├── Models/                  # News, Project, Research, Event, Legislation, Interview, Technology, Training,
│   │                            #  Company, Professional, DirectoryDocument, Banner, Category, Setting, Directory(legado)...
│   ├── Support/                 # MenuCategorias, AreasAtuacao (whitelist de áreas)
│   └── Providers/               # App, Fortify, Auth(ServiceProvider)
├── bootstrap/
├── config/
├── database/
│   ├── migrations/              # Fase 2 criou media/companies/professionals/directory_documents/banners (+ ajustes de restrutura)
│   ├── seeders/                 # RoleSeeder, ContentSeeder, DirectorySeeder, MenuSeeder (wired no DatabaseSeeder)
│   └── database.sqlite          # Banco de desenvolvimento
├── public/
│   ├── css/                     # style.css (tema), menu.css, topo.css, color.css, plugins.css
│   ├── js/                      # scripts.js (tema limpo), menu.js, jquery.min.js, plugins.js
│   └── build/                   # Assets compilados pelo Vite
├── resources/views/
│   ├── layouts/                 # app (frontend), dashboard, auth
│   ├── components/              # header (top-bar + header-inner), main-menu, footer, banner-publicitario, aside-panel, ...
│   ├── admin/                   # páginas do painel (empresas, profissionais, documentos, banners, notícias, settings, users)
│   └── ...
├── routes/web.php
├── tests/                       # Feature (13 ficheiros) + Unit (2) — suíte REAL
├── vendor/  node_modules/
├── .env
└── phpunit.xml
```

---

## 4. Dependências

### PHP (Composer) — 160 pacotes

Principais:

| Pacote |  versão  | Função |
|--------|----------|--------|
| `laravel/framework` | v12.x | Framework base |
| `livewire/livewire` | v3.x | Componentes UI reativos |
| `laravel/fortify` | v1.x | Autenticação backend (sem registo público) |
| `spatie/laravel-permission` | 6.x | Roles e permissões (`admin`/`editor`) |
| `spatie/laravel-medialibrary` | 11.x | Media do `News` (coleções `capa`/`contexto`, conversão WebP) |
| `laravel/scout` | v11.x | Busca (instalado; driver por defeito SQLite) |
| `phpunit/phpunit` | 11.x | Testes |

### JavaScript (NPM) — 87 pacotes

| Pacote | Versão | Função |
|--------|--------|--------|
| `vite` | v7.x | Build tool |
| `tailwindcss` | ^4.x | CSS utilitário |
| `@tailwindcss/vite` | ^4.x | Integração Tailwind+Vite |
| `laravel-vite-plugin` | ^2.x | Plugin Laravel |

---

## 5. Ambiente de Execução

| Ferramenta |  Versão  | Caminho |
|------------|----------|---------|
| PHP | 8.2.12 | `C:\xampp\php\php.exe` (XAMPP; não está no PATH) |
| Composer | 2.10.3 | `pigea-deploy/composer` |
| Node.js | v24.22.0 | `C:\Program Files\nodejs\node.exe` |
| npm | 11.19.1 | acompanha o Node |
| SQLite | embutido no PHP | (dev) |
| MySQL (XAMPP) | MariaDB 10.4 | testes automatizados (`pigea_test`; arranque/paragem ad hoc) |

---

## 6. Banco de Dados

### Desenvolvimento (`database/database.sqlite`)

Tabelas principais: `users`, `password_reset_tokens`, `sessions`, `cache*`, `migrations`, `permission_tables` (roles/permissions/model_has_roles/role_has_permissions), `categories` (com **`parent_id`**, **`menu_group`**, **`order`**), `news`, `projects`, `researches`, `events`, `legislations`, `interviews`, `technologies`, `trainings`, `directory` (legado artigos), `suppliers`, `companies`, `professionals`, `directory_documents`, `banners`, **`media`**, `notifications`, `settings`.

¹ Tabelas do legado (`jobs`, `applications`, `saved_jobs`, …) foram **apagadas na Fase 1** (migrations removidas; ver `modificar.md` §8).

### Testes (`pigea_test`, MySQL)

- Fail-closed no `tests/bootstrap.php`: recusa correr se `DB_DATABASE` não for exatamente `pigea_test`; nunca toca na BD de dev.
- **Estado restaurado do zero** (11/11/2026): `migrate:fresh --seed` → 3 categorias-pai news (`mineracao`/`petroleo-e-gas`/`geociencias`), 44 `menu_group`, 11 permissões (com `document.manage`/`banner.manage`), admin=11/editor=8.
- O bootstrap deteta permissões novas pelo nome; sem MySQL ativo, `phpunit` não corre.

### Seeders

- `RoleSeeder` — `admin` (todas) + `editor` (8 permissões).
- `ContentSeeder` — artigos/notícias/etc. + garante `Petróleo e Gás`/`Geociências` em todos os módulos.
- `DirectorySeeder` — empresas/profissionais/documentos de demonstração (dos 3 estados).
- `MenuSeeder` — 44 subcategorias do mega menu (9 grupos).

---

## 7. Como Executar

```cmd
cd pigea-deploy
C:\xampp\php\php.exe artisan serve
:: http://127.0.0.1:8000
```

### Testes (MySQL ad hoc)

```powershell
# 1) Arrancar o MySQL do XAMPP (janela escondida; aguardar)
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini" -WindowStyle Hidden
# 2) (Confirmar) C:\xampp\mysql\bin\mysql.exe -u root -e "SELECT 1"
# 3) Correr a suíte
$env:DB_USERNAME = "root"; $env:DB_PASSWORD = ""
C:\xampp\php\php.exe vendor\phpunit\phpunit\phpunit
# 4) Parar o MySQL no fim
C:\xampp\mysql\bin\mysqladmin.exe -u root shutdown
```

> Resultado atual (11/11/2026): **OK (145 testes, 534 asserções)**.

### Outros comandos úteis

```cmd
C:\xampp\php\php.exe artisan migrate:status
C:\xampp\php\php.exe artisan view:clear  && route:clear && cache:clear && config:clear
```

---

## 8. Segurança

- **Sem registo público.** Login em `/login` (ou o path oculto) exclusivo da equipa interna; `Fortify` sem `Features::registration()`.
- **`/admin` obscurecido** por prefixo `ADMIN_PATH` (`config('app.admin_path')`), middleware `role:admin,editor` + `can:<permissão>` granular. Sem sessão → redirect para `/login`; autenticado sem role → **403** (o middleware nega com 403, não redireciona); o "obscurecimento" depende de o `ADMIN_PATH` ser configurado não-óbvio em produção (ver resumo.md §8, `.env.example`).
- **NIF** das empresas/profissionais nunca é apresentado no público (asserções na suíte).
- **CSRF** ativado por defeito (Fortify/Livewire); guardas `role:` negam com 403.
- **`.env` nunca versionado**; `APP_KEY` gerida por `key:generate`.

---

## 9. O Que Falta / Limitações Conhecidas

| Item | Estado |
|------|--------|
| Screenshots visuais | ❌ Sem tooling de browser no ambiente; evidência = HTML renderizado + asserções da suíte. Captura manual recomendada (top-bar, mega menu, home). |
| Produção | SQLite → MySQL/PostgreSQL; `artisan serve` → Apache/Nginx + PHP-FPM; Redis para cache/sessões/filas; SSL; variáveis de ambiente do servidor; S3 (Media Library) recomendado. |
| Índices FULLTEXT (SQLite) | ❌ Migração de full-text pulada por limitação do SQLite; produção deve usar MySQL (InnoDB fulltext) — sem impacto funcional atual (`LIKE`). |
| URLs das redes sociais | ❌ Top-bar/rodapé ainda com links `#` (placeholders); gestão já existe em Configurações do admin. |
| CSS inerte do tema | ⚠️ Blocos `.main-register-*`/`.bg-wrap` inertes mantidos em `style.css` (arquivo third-party, ~150 linhas espalhadas) — JS/markup removidos (Fase 8); decisão registada em `modificar.md` §16. |
| PHP no PATH | ⚠️ Sempre usar `C:\xampp\php\php.exe` (absoluto). |

---

## 10. Documentos de Registo (raiz `pigea/`)

| Documento | Conteúdo |
|-----------|----------|
| `memoria.md` | Memória do projeto (versão **2.3**) — contexto, decisões, versões |
| `modificar.md` | Registo de **todas** as alterações de código (secções 1–16) |
| `fase_all.md` | Plano-mestre de fases 0–8 (com 5.1); FASE 8 = esta documentação |
| `restrutura.md` | Instruções do cliente (novo paradigma) |
| `menu.md` | Spec do Mega Menu |
| `pub.md` | **Sistema de Publicidade** (estado atual — módulo implementado) |
| `verificar.md` | Análise original de dependências/ambiente |
| `resumo.md` | Este documento |

---

## 11. Conclusão

O PIGEA foi restruturado (Fases 0–7) num **portal de notícias + diretório** coerente com as instruções do cliente: sem login público, gestão pela equipa interna via painel `/admin`, formulários públicos para empresas/profissionais com moderação, mega menu DB-driven, publicidade por banners e suíte de testes real verde (**145/534**). O sistema está pronto para ambiente de desenvolvimento local; para produção, alguns ajustes de infraestrutura (secção 9) são necessários.

---
*Documento reescrito em 11/11/2026 (Fase 8) — reflete o paradigma atual do sistema (pós-restruturação `restrutura.md`).*
