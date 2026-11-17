# PIGEA — Plataforma de Impulsionamento das Geociências em Angola

> Portal institucional das geociências em Angola. Notícias, directório de empresas/profissionais/documentos, legislação, eventos, formação, entrevistas, vídeos e oportunidades. Área administrativa para a equipa interna; autenticação separada para o público de equipa.
>
> **Propriedade:** PIGEA / QNBAngola.

| | |
|---|---|
| **Framework** | Laravel 12 · PHP 8.2 |
| **Base de dados** | **MariaDB / MySQL 10.4+** (obrigatório, FULLTEXT) |
| **Frontend** | HTML/CSS/jQuery servido directamente · TinyMCE self-hosted |
| **Packaging** | Composer (sem build obrigatório) |
| **Internacionais** | `lang/pt/` (fallback `pt_PT`) |
| **Fuso** | `Africa/Luanda` (UTC+1, sem horário de verão) |
| **Testes** | 299 testes / 1032 assertions — todos verdes |
| **Ambiente** | GitHub: `JorgeDange/pigea-deploy` · branches `main` (produção) e `updates` (trabalho) |
| **Deploy** | Manual (FTP/cPanel) — ver `doc/deploy.md` e `## 12. Deploy em produção` |

---

## 1. Visão geral

O PIGEA é o portal de referência das geociências em Angola. A plataforma combina três camadas:

1. **Conteúdo público** — notícias, eventos, legislação, entrevistas, formação, investigação, resultados, vídeos, oportunidades.
2. **Directório** — empresas, profissionais e documentos (sem login; aprovação interna da equipa).
3. **Painel administrativo** — gestão completa pela equipa (papéis `admin` e `editor`) via `ADMIN_PATH` com `APP_DEBUG=false`, nunca público.

**Não é um CMS genérico.** Cada módulo foi pensado para o fluxo editorial da equipa. Decisões de produto foram documentadas e confirmadas com o cliente — ver `doc/docs.md` e `doc/memoria.md`.

---

## 2. Stack e arquitectura

| Camada | Tecnologia | Função |
|---|---|---|
| Framework | **Laravel 12** | MVC |
| Auth | **Fortify** (login/reset/2FA) | Autenticação e recuperação de password |
| Permissões | **Spatie Laravel-Permission** | `admin`, `editor` (únicos papéis activos) |
| Motor de pesquisa | **FULLTEXT `MATCH ... AGAINST`** (via query raw em MariaDB) | Pesquisa de conteúdo |
| Imagens | **Spatie Media Library** | Media + conversão WebP para capa de notícia |
| Editor | **TinyMCE** self-hosted | WYSIWYG com sanitização no servidor (HTMLPurifier) |
| Email | **SMTP** via `.env` + Laravel Mail | Newsletter, contacto, notificações internas |
| Interfaces | **Livewire 3** + **Blade** + jQuery | Tabelas admin, filtros, registo público |

### Porque MariaDB/MySQL e não SQLite/PostgreSQL

A pesquisa usa `MATCH … AGAINST` (índice FULLTEXT) e alguns relatórios usam `DATE_FORMAT`. São funcionalidades que **só funcionam no MySQL/MariaDB**. Em qualquer outro motor a app falha ou dá resultados errados.\nNão há `@vite` obrigatório em `public/`: as views ligam CSS/JS com `asset()` — produção basta fazer upload dos ficheiros por FTP.

---

## 3. Estrutura do repositório

```
pigea-deploy/
├── app/
│   ├── Concerns/                  traits partilhados
│   ├── Console/Commands/          pigea:criar-admin, news:publish-scheduled
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             painel (Dashboard, Newsletter, Acompanhamento, …)
│   │   │   └── Frontend/          site público (Newsletter, Contact, …)
│   │   ├── Middleware/            SecurityHeaders, CountPublicVisits, …
│   │   └── Requests/
│   ├── Models/                    News, Banner, Category, User, …
│   ├── Notifications/             EventoPainel (sino do painel)
│   ├── Livewire/
│   │   ├── Admin/                 tabelas/ações de painel
│   │   └── Frontend/              filtros, formulários públicos
│   ├── Services/                  ArticleSanitizer (HTMLPurifier)
│   └── Support/                   MenuCategorias, ContadorDeVisitas, Notificador, Autor, Provincias, AreasAtuacao, Provincias…
├── config/                        app.php (admin_path, timezone), auth, fortify, …
├── database/
│   ├── migrations/                todas as migrações (estado de produção descrito em §8)
│   ├── seeders/                   RoleSeeder, ContentSeeder, MenuSeeder, …
│   └── data/                      JSONs de conteúdo real
├── doc/                           documentação detalhada de cada módulo e fase
├── public/                        document root: css/, js/, images/, build/
├── resources/views/               Blade: público, painel admin, painel do utilizador
├── routes/web.php                 1 ficheiro, totalmente legível, sem grupos escondidos
└── tests/                         Feature tests — 299 testes / 1032 assertions
```

### Documentos e ficheiros principais

| Arquivo | Função |
|---|---|
| `doc/deploy.md` | Guia manual FTP/cPanel |
| `prod-patch-2026-11-16-b.sql` | Patch Fase B (coluna `must_change_password` + permissões `institucional.manage`/`newsletter.manage`) — no `README`/`memoria.md` |
| `prod-patch-2026-11-16.sql` | Patch principal da release (29 migrações, esquema completo Fase 11–16) |
| `prod-categorias-news.sql` | Repõe a árvore `news`/`menu` em produção (37 categorias garantidas) |
| `doc/orienta_deploy.md` | Manual de deploy (FTP/cPanel, .env, patch, GS) |
| `doc/fix-equipa-route-2026-11-15.md` | Post-mortem do «Route [equipa] not defined» em produção |
| `doc/prop.md` | Proposta do painel de Gestão/Perfil de utilizadores (3 fases) — A ✔ / B ✔ / C pendente |
| `doc/docs.md` | Arquitectura e decisões técnicas (anti-bot, pin editorial, banners, etc.) |
| `doc/dimensao.md` | Especificação de imagens e Banners para o designer |
| `doc/email.md` | Historial do problema SMTP de produção e correção |
| `doc/evolution_news.md` | Visão do módulo Newsletter Pro (N2) — **pendente decisão do cliente** |
| `doc/otimizacao.md` | Otimizações técnicas aplicadas (CLS, LCP, async CSS) |
| `doc/*.md` | Cada módulo e cada fase guiadas por MD |

---

## 4. Funcionalidades completas

### 4.1 Site público

- **Notícias** com editor (TinyMCE), capa, tags, autor interno/convidado, áreas de atuação de Angola, destaque editorial, agendamento de publicação
- **Menu mega** com 9 grupos e 44 subcategorias (`MenuSeeder`)
- **Directório** empresas/profissionais/documentos (aprovação interna)
- **Eventos**, **Legislação**, **Pesquisa** (FULLTEXT), **Investigação**, **Entrevistas**, **Formação**, **Tecnologias**, **Vídeos**
- **Contactos / Fale connosco** (Livewire + email via `.env`)
- **Newsletter** opt-in duplo (subscrição e cancelamento pelo link de token único)
- **Registo de Empresa** e **Registo de Profissional** (Livewire, sem login)

### 4.2 Painel administrativo

Rota `/{ADMIN_PATH}` (por omissão `gestao-pigea` em dev). Subdivisões:

| Secção | Função |
|---|---|
| Dashboard | 4 cartões-chave: notícias publicadas, empresas pendentes, visitas-site (anti-bot), banners ativos; gráfico de visitas 7d; provincia chart |
| Acompanhamento | Saúde do painel: KPIs de banners Largo, províncias dos artigos, pins editoriais, qualidade de tags/imagens |
| Notícias | Lista, criar/editar com editor completo, pré-visualização, agendar, destaque editorial |
| Vídeos | CRUD de vídeos por link (YouTube/Vimeo) + vídeo em destaque dinamico |
| Empresas/Profissionais/Documentos | Moderar/aprovar/rejeitar/eliminar candidatos e documentos |
| Publicidade | Banner Lateral (1:1), Largo (cheio), Topo (top-bar), Rodapé — XHR upload + confirmação interna |
| Destaques | Rotativo, escopo Inicial/Interior/Site inteiro |
| Configuração | Nome do portal, descrição, logo, favicon, seo, email/telefone, redes sociais (confirmações internas com empresa/profissional vs admin) |
| Newsletter | KPIs, confirmar/confirmar pendentes, reenviar (com email genérico), exportar CSV por estado |
| Utilizadores | Criar/editar/eliminar utilizadores com papéis (`admin`,`editor`) |
| Notificações | Sino com contagem real (Laravel Notifications) — dispara em eventos: empresa/profissional pendente, notícia publicada/em revisão, novo utilizador |

### 4.3 Detalhes técnicos importantes

| Item | Como funciona |
|---|---|
| **Contador de Visitas** | `page_views` agregado por dia; middleware não-conta bots e ignora `/admin`, `/livewire`, `/csp-report` |
| **Anti-bot** | `App\Support\ContadorDeVisitas` ignora user-agentes vazios e Googlebot/Bingbot/etc.; também PBS de 5 min por sessão |
| **FULLTEXT** | `MATCH … AGAINST` nas colunas `title`, `content`, `excerpt` — verifica com `SHOW INDEX FROM news` |
| **HTML seguro** | `ezyang/htmlpurifier` no save + preview; lista branca `config/article.php` |
| **Uploads** | `public/storage` liga a `storage/app/public`; criar a liga simbólica com `php artisan storage:link` |
| **SEO** | `<meta description>`, `og:*`, canonical/facebook-mention-links; JSON-LD em estudo |
| **Logs** | `storage/logs/laravel.log` — observar para erros de SMTP/cron/etc. |

---

## 5. Requisitos

| Item | Versão mínima | Verificar com |
|---|---|---|
| PHP | **8.2** | `php -v` |
| MariaDB / MySQL | **10.4** | `mysql --version` |
| Composer | **2** | `composer -V` |
| Extensões PHP | `pdo_mysql`, `openssl`, `mbstring`, `fileinfo`, `curl`, `gd`, `zip`, `bcmath`, `dom`, `xml`, `tokenizer` | `php -m` |

**Node.js não é obrigatório** para puxar o site de produção (CSS/JS são servidos directamente). O `package.json` só serve ao `npm run dev` se for preciso editar frontend.

---

## 6. Instalação local

```bash
git clone https://github.com/JorgeDange/pigea-deploy.git pigea
cd pigea/pigea-deploy
composer install          # ou --no-dev --optimize-autoloader em produção
cp .env.example .env
php artisan key:generate
# Preencher .env (ver §7)
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=ContentSeeder --force    # conteúdo real de exemplo
php artisan storage:link
php artisan optimize
```

Verificar:

```bash
php artisan serve              # http://127.0.0.1:8000
```

---

## 7. Ficheiro `.env`

`.env.example` está em **modo desenvolvimento**. Em produção, ajuste pelo menos:

| Chave | Produção | Nota |
|---|---|---|
| `APP_KEY` | gerada | sem ela a app não arranca |
| `APP_URL` | `https://pigea.ao` | onde o Share/OG/Link devolvem |
| `APP_DEBUG` | `false` | obrigatório em produção |
| `APP_ENV` | `production` | |
| `APP_TIMEZONE` | `Africa/Luanda` | UTC+1 sem horário de verão |
| `APP_LOCALE` | `pt` | Idioma padrão |
| `DB_CONNECTION` | `mysql` | MariaDB/MySQL obrigatório |
| `ADMIN_PATH` | `painel-gestao-7f3a` | Não óbvio, mas não é barreira de segurança |
| `PIGEA_LOGIN_PATH` | `entrar-7x2` | `/login` devolve 404 |
| `MAIL_MAILER` | `smtp` | com `log` nenhum email é enviado |
| `MAIL_HOST/PORT/USERNAME/PASSWORD` | credenciais reais | |
| `SESSION_DRIVER` | `database` | |
| `QUEUE_CONNECTION` | `database` | aproveitar fila em produção se houver crontab |
| `CACHE_STORE` | `database` | |

### Guardas de segurança pós-alteração

```bash
php artisan optimize
```

Alterações a `.env` em cache requerem sempre `optimize`.

---

## 8. Base de dados e migrações

### Estado actual

- **Total de migrações**: 46 em `database/migrations/` (intervalo 28/09 a 16/11/2026).
- **Migrações de produção aplicadas** via `prod-patch-2026-11-16.sql` (29 execuções) — confirmado 16/11.
- **Coluna `must_change_password` (Fase B)**: criar via `prod-patch-2026-11-16-b.sql` ou o trecho SQL reproduzido na memoria.md (ainda por confirmar no servidor — `SELECT COUNT(*) ...` esperado: 1).
- Disponibilização **idempotente** via `prod-patch-2026-11-15.sql` / `prod-patch-2026-11-16.sql` (executável várias vezes na BD do servidor).

### Como aplicar na produção

Ver **§12. Deploy em produção**. Alternativamente, numa cópia local, executa o resto:

```bash
php artisan migrate --force
```

### Seedering

```bash
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=ContentSeeder --force
php artisan MenuSeeder --force         # 44 subcategorias do Mega Menu
php artisan pigea:criar-admin direccao@pigea.ao --nome="Direcção PIGEA"
```

ContentSeeder inclui os 113 registos reais:
20 notícias · 9 projectos · 8 vagas · 8 fornecedores · 8 eventos · 8 legislações · 9 investigações · 9 entrevistas · 9 tecnologias · 8 directórios · 9 formações · 9 autores.

> O `MenuSeeder` é **obrigatório** se quiseres o mega menu a funcionar no browser de produção: sem ele o menu não mostra subcategorias.

---

## 9. Painel administrativo — `/{ADMIN_PATH}`

Características intrínsecas:

- Prefixo personalizado (`painel-gestao-7f3a` por omissão, produção), `/admin` devolve 404
- Papel obrigatório: `admin` (função do Spatie Permission)
- Login em `PIGEA_LOGIN_PATH` (`entrar-7x2` por omissão em produção)
- CSRF em todos os forms
- Repositório
- Em caso de 2FA activada no utilizador, fluxo standard Fortify de challenge

### Subsecções principais

| Área | Rota |
|---|---|
| Dashboard | `/gestao-pigea` (`.env` dev) |
| Notícias | `/gestao-pigea/noticias` (Filtros, Categoria, Busca, Pré-visualizar, Publicar, Agendar, Destaque) |
| Vídeos | `/gestao-pigea/videos/novo` |
| Empresas | `/gestao-pigea/empresas` (Aprovar/Rejeitar) |
| Profissionais | `/gestao-pigea/profissionais` (Aprovar/Rejeitar) |
| Directório de Documentos | `/gestao-pigea/documentos` |
| Publicidade | `/gestao-pigea/publicidade` (Banners por posição) |
| Destaques | `/gestao-pigea/destaques` (Escopo: Site/Início/Interior, Rotativo) |
| Newsletter | `/gestao-pigea/newsletter` (Confirmar/reenviar/exportar) |
| Utilizadores | `/gestao-pigea/utilizadores` (Criar/Eliminar) |
| Configurações | `/gestao-pigea/configuracoes` (portal, logo, email, telefone, redes sociais — controlo 2FA pedido pelo cliente) |
| Acompanhamento | `/gestao-pigea/acompanhamento` (KPIs estruturados do que falta no painel) |
| Notificações | 🔔 no topbar (Laravel Notifications) |

> Os nomes de rota continuam `admin.*` (ex: `admin.noticias`, `admin.configuracoes`) mesmo quando o prefixo da URL muda por `ADMIN_PATH`.

---

## 10. Segurança — listagem completa

| Área | Medida |
|---|---|
| Auth | Fortify (login, reset, 2FA); rate-limit (5/min) em `.env` |
| Rotas admin | `can:admin_*` em cada rota |
| CSRF | token obrigatório em todos os forms |
| HTML rico | HTMLPurifier: `ArticleSanitizer::clean` no save + preview |
| Uploads | MIMEtype real verificado nos forms; artigos de capa em `media/` com conversão WebP |
| Headers | CSP `Report-Only` (rota para `/csp-report`), `X-Content-Type-Options`, `X-Frame-Options`, `Strict-Transport-Security` via `TRUSTED_PROXIES` quando atrás de proxy |
| Validar input | Todos os fields com `required`/`email`/`mimes:`/`file:` apropriados |
| Sessão | `SESSION_DRIVER=database`, `SESSION_LIFETIME=120`, `SESSION_ENCRYPT` opcional, `SESSION_PATH=/`, cookies `secure` |
| Permissões | Spatie Permission controla `admin` vs `editor` |
| SQL injection | Eloquent/Query Builder com bindings — nenhum SQL raw no caminho de entrada de utilizadores (o `FULLTEXT` é sem interpolação de strings do input) |
| Proxy | `TRUSTED_PROXIES` — HSTS só chega quando request é `https` |

---

## 11. Testes e qualidade

```bash
php artisan test
```

**Estado**: `295` testes, `1015` assertions, todos verdes (`.env.testing` → BD `pigea_test` separada; `projects_publico=true` forçado via `phpunit.xml`).

Para verificar tudo depois de alterações:

```bash
php artisan test
php artisan view:cache
```

### Estrutura dos testes

```
tests/Feature/AlvoSeAfirmaComportamentoTest.php   um ficheiro por área
```

Áreas cobertas: autenticação, autorizações (`RoleMiddleware`), formulários públicos (registo empresa/profissional/contact), CRUDs admin, migrações, uploads, CSP report, cron, segurança.

---

## 12. Deploy em produção

O cliente usa deploy **manual** via FTP/cPanel: o utilizador sobe o conteúdo do repositório para a pasta pública (`public/`) e aponta a raiz para `public/`.

> **Disciplina de deploy (aprendas de 15–16/11/2026):**
> 1. **Nunca** fazer upload ficheiro-a-ficheiro (subconjuntos) — qualquer descuido derruba o site inteiro. Use o **pacote completo**: `pigea-deploy-production-<AAAA-MM-DD>.zip` (sem `vendor`, `storage`, `.git`, `.env`, `bootstrap/cache`, imagens de conteúdo, `tests`, docs; **o `public/storage` (symlink do servidor) nunca entra no pacote**).
> 2. **md5sum de tudo** que entrou no servidor **depois de subir**, comparando com os valores locais — se algo não bate, voltar a subir esse ficheiro.
> 3. **Nunca tocar em `bootstrap/cache/`**: o `packages.php`/`services.php` local contém deps de desenvolvimento que **não** existem em produção → `PailServiceProvider not found` → site cai em 100%. Só sobem `app.php` e `providers.php`.
> 4. **Não correr `composer install`** no servidor. Vendor fica como está.
> 5. Depois: `php artisan optimize:clear` e **Ctrl+F5**.
> 6. **Backup** fresco da BD antes de qualquer patch. Os patches SQL manuais (`prod-patch-*.sql`) correm **sempre na base certa** — confirmar com `USE `nome\` e `SELECT DATABASE()` antes de colar.

### Sequência recomendada (pacote completo)

```bash
# 1. Local — gerar o pacote (validar com md5sum):
#    ver doc/orienta_deploy.md (Secção 6) ou regenerar:
#    robocopy pigea-deploy staging-prod /E /XD ... ; zip
cd pigea-deploy
git status                      # working tree limpa exigida?
php artisan view:clear
C:\xampp\php\php.exe artisan test   # suite tem de estar verde

# 2. Cliente — phpMyAdmin:
#    a) Use `<bd>` primeiro!  SELECT DATABASE(); -> tem de ser a base do site
#    b) Exportar (backup-fresco.sql)
#    c) Importar o patch SQL correcto (prod-patch-2026-11-16.sql / -b.sql)
#       e o verificador no final (1/2/3)

# 3. Cliente — FTP/cPanel:
#    a) Enviar o zip para /home/geopacas/public_html/
#    b) File Manager -> Extract (substituir)
#    c) Se ainda houver erros: rm -f public_html/bootstrap/cache/packages.php services.php

# 4. Servidor (Terminal cPanel):
cd /home/geopacas/public_html
php artisan optimize:clear
php artisan route:list --name=utilizador
php artisan tinker --execute='echo "ok";'   # não deve crashar
curl -I https://pigea.ao/
```

### Verificação pós-deploy

```bash
curl -sI https://pigea.ao/              # 200
curl -sI https://pigea.ao/(login .env)    # 200 (login team)
curl -sI https://pigea.ao/login         # 404 (esperado)
curl -sI https://pigea.ao/<ADMIN_PATH>  # 302 → login
```
- Home 200 com mega menu completo (9 grupos + subcategorias visíveis ao hover)
- `/gestao-pigea` login → editor/admin vêem dashboard sem 500
- Página Utilizadores: tabela + edição + desativar/reactivar + «Criado»
- `/entrar-7x2` 200 (ou PC do `PIGEA_LOGIN_PATH`)
- `/contactos` → envia → chega a `geral@pigea.ao` (from `PIGEA <geral@pigea.ao>`)
- Newsletter: subscrever/confirmar/cancelar/resubscrever — emails chegam
```

### Lista de verificação final

- [ ] `/` devolve 200 e o site aparece com as imagens
- [ ] Qualquer imagem nova enviada no admin **aparece** (se `storage:link` falha, uploads ficam 404)
- [ ] `/gestao-pigea` (ou `/painel-gestao-7f3a`) pede login e entra no dashboard
- [ ] Nova notícia publicada em `/` aparece com as tags reais ligadas a `/pesquisa?se=<tag>`
- [ ] Formulário de contacto envia email (sem `MAIL_MAILER=log`)
- [ ] O submenu de hover funciona (ver §8, seeder do menu)

### Troubleshooting crítico

| Sintoma | Causa | Correção |
|---|---|---|
| Mega menu sem submenu ao passar o rato | `MenuSeeder` não foi corrido ou coluna `menu_group` não existe | `php artisan db:seed --class=MenuSeeder --force` (+ migração prod-patch) |
| Uploads não aparecem | `storage:link` não foi feito | `php artisan storage:link` |
| Painel "desconfigurado" / inputs desajeitados | `bootstrap/cache/config.php` e/ou `storage/framework/views/*` desactualizados | Apagar cache + `Ctrl+F5` |
| Emails não saem | `MAIL_MAILER=log` ou credenciais erradas (535 Authentication failed) | `.env` correto → `php artisan optimize:clear` |
| `Class "Laravel\Pail\PailServiceProvider" not found` | Ficheiros `bootstrap/cache/{packages,services}.php` (cache local) acabaram no servidor | Apagar esses 2 ficheiros por File Manager (o Laravel reconstrói do `vendor/composer/installed.json`) |
| 500 interno | Permissão errada em `storage/` e/ou `bootstrap/cache/` | `chmod -R 775 storage bootstrap/cache` |
| `Route [admin.utilizador.editar] not defined` | `routes/web.php` do servidor está desatualizado | Reenviar `routes/web.php` (ver pacote dinâmico mais abaixo) |
| `Route [equipa] not defined` | `MenuCategorias.php` novo sem `routes/web.php` novo | Reenviar `routes/web.php` (corrida completa em 2026-11-15) |
| Submissões de notícias voltam a "Rascunho" | Botões submit desativados excluídos do form | (Fix aplicado no commit actual; confirmar pacote actualizado) |
| 404 num ficheiro CSS/JS (vídeo, JS distinto) | `public/build/` não foi incluído no pacote de upload | Reempacotar com `main` mais recente |
| Site inteiro 500 após upload parcial | Deploy fora de sincronia (ficheiros novos a mais/a menos) | **Usar o pacote completo** (`pigea-deploy-production-*.zip`) em vez de upload ficheiro-a-ficheiro |

### Release 2026-11-16 — o que mudou em produção

| Área | Estado em produção (16/11/2026) |
|---|---|
| Newsletter simplificada (`pending`/`active`/`cancelled`, 3 emails: subscrição/confirmação/cancelamento, rota `/newsletter/resubscrever/{token}`) | ✅ Confirmado pelo cliente («ok») |
| Home «Últimas Notícias» (sem abas, 5 recentes server-rendered) | ✅ Em produção |
| Equipa `/equipa` + Parceiros `/parceiros` institucionais + CRUD admin | ✅ Em produção |
| Sobre Nós editável (Admin → Configurações) | ✅ Em produção |
| Diretório — email transacional via mailer `geral` (toquesüstüplos; `newsletter@pigea.ao` INTOCADA) | ✅ Confirmado pelo cliente |
| Mega menu completo (9 grupos + 44 itens; removida «Missão, visão e valores») | ✅ Em produção |
| DB categorias news = 37 (10 topos + 27 filhos), menu = 16 | ✅ Verificado |
| Emails (logo i.ibb.co → **`https://docs.pigea.ao/imag/logo.png`** em todos os templates + glifos Unicode) | ✅ Cliente confirma recebimento |
| Formulário contacto funciona (`report()` no catch → log) | ✅ Confirmado (cidades) |
| **Fase A — Perfil: «A minha senha» (troca com sessão atual mantida, outras sessões terminadas) + «Informação da conta»** | ✅ Em produção |
| **Fase B — Gestão de utilizadores a sério**: editar dados, repor senha (troca obrigatória), desativar/reativar real, eliminar em 2 passos, paginação 10/25/50/100, guard último admin | ✅ Em produção (código + SQL) |
| **Permissões novas**: `institucional.manage` (só admin) e `newsletter.manage` (admin+editor) — sidebar/rotas | ✅ SQL verificado (2 rows, 3 links) |
| Coluna `users.must_change_password` | ✅ Criada e verificada no servidor (16/11, `col=1`) — o «Repor senha» funciona, obrigando troca no próximo login |
| DKIM (`pigea.ao`) | ✅ Atualizado (cliente 16/11) |
| `Route [equipa] not defined` (15/11 20:59) | ✅ Resolvido (routes/web.php em produção agora correto; pacote completo definido) |

### Patch de base de dados na produção (sem `migrate`)

Se preferir aplicar o esquema manualmente (ex: sem SSH), existe o ficheiro
`prod-patch-2026-11-15.sql` no repositório. Ele:

- cria 10 tabelas novas se não existirem;
- adiciona colunas que faltam com guarda `IF NOT EXISTS`;
- altera enums (inclui `lateral`/`largo`);
- alargar `province` de `projects` para VARCHAR(120);
- injeta as categorias estruturais (`Outros`, `Academia`, `Oportunidades`);
- **regista** as 25 migrações como executadas na tabela `migrations`;
- traz uma verificação prévia para NIFs duplicados.

> O patch **não remove** dados. Mas faz backup primeiro: **sempre** uma dump/Exportar do SQL antes de executar.

---

## 13. Equipa e contribuição

### Ambiente comum

```bash
composer install
cp .env.example .env
php artisan key:generate
# editar .env com MySQL + BD de desenvolvimento
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=ContentSeeder --force
```

Qualquer modificação no repositório exige que a suite complete **continue verde** — 295 testes / 1015 assertions.

### Estrutura de diretório para novos recursos

Os passos padrão para qualquer artefacto novo:

1. `php artisan make:model X -m` (migration)
2. Adicionar ao `database/migrations/` (nome do ficheiro idêntico ao gerado)
3. Adaptar `app/Models/X.php`
4. Adicionar rota em `routes/web.php`
5. Criar/ativar a view em `resources/views/.../x.blade.php`
6. Acompanhar por um teste em `tests/Feature/XTest.php`
7. Documentar o artefacto em `doc/docs.md` (decisão) **e** `doc/x.md` (especificação completa)

### Contribuições

Este é um projeto proprietário. Contribuições externas requerem acordo escrito com o dono do produto (PIGEA/QNBAngola).

---

## 14. Roadmap de produto

| Item | Estado |
|---|---|
| Portal de notícias + directório público | **Em produção** (15/11/2026 — release ponta; ver §12) |
| Área de utilizador para empresas/profissionais com login | **Removido** (decisão do cliente 2026-11) |
| Sistema de comentários | **Removido** (2026-11-11) |
| Mega menu com 9 grupos + 44 subcategorias | **Em produção** — resolvido 16/11 (menu por `menu_group`; árvore news corrigida: `news=37`, `menu=16`) |
| Newsletter com dupla opt-in | **Em produção** — emails funcionando (confirmar/cancelar/resubscrever/recibos transaccionais) |
| **Aviso de novo artigo** — envio manual no painel | **Implementado 17/11/2026** (299/1032 verde) — opt-in pelo painel; ≤100 activos por clique; modo «teste» só para a caixa do admin; histórico de edições; produção: criar `newsletter_editions*` via SQL inline + upload (10 ficheiros) |
| Banners laterais/largos/topo/rodapé | **Em produção** |
| Notificações internas (sino) | **Em produção** |
| Configuração completa no admin | **Em produção** (Social Links, contacto, Geral/SEO/emails) |
| Equipa/Parceiros institucionais (`/equipa`, `/parceiros`) | **Em produção — release 2026-11-16** |
| Perfil do colaborador (`/gestao-pigea/perfil` — troca de senha) | **Em produção — release 2026-11-16 (Fase A)** |
| Gestão de utilizadores (editar/repor/desativar, paginação) | **Em produção — release 2026-11-16 (Fase B)** (ver nota abaixo sobre verificação) |
| **Newsletter Pro** (envio orquestrado por painel com lotes de 100 e prazos) | **Pendente decisão do cliente** — ver `doc/evolution_news.md` |
| Painel `/gestao-pigea/acompanhamento` | **Em produção** |
| Secção de destaque/emissão de Notícias com destaque editorial | **Em produção** |
| Storage link em produção (uploads visíveis) | **Em produção** |
| Migrações `2026_11_*` corridas em produção | **Aplicadas** (patch `2026-11-16`: 29 migrações marcadas; categorias 37/16 verificadas) |

---

## 15. Documentos internos

- `doc/docs.md` — decisões estruturais (escolha de MySQL, anti-bot, pin editorial, banner Largo…)
- `doc/deploy.md` — guia de deploy manual FTP/cPanel
- `doc/memoria.md` — memória acumulada do projeto desde 28/10/2026
- `doc/otimizacao.md` — otimizações técnicas, análise PageSpeed
- `doc/dimensao.md` — especificações de imagem para o designer
- `doc/email.md` — historial do problema SMTP de produção e correção definitiva
- `doc/evolution_news.md` — módulo Newsletter Pro — pendente decisão do cliente
- `doc/*.md` — documentação detalhada de cada módulo e cada fase

---

## 16. Troubleshooting rápido

```bash
# app não arranca
cat storage/logs/laravel-2026-*.log | tail -40

# email não chega
grep "MAIL\|smtp\|mail" .env | head -5
tail -50 storage/logs/laravel-*.log

# password de um utilizador perdido
php artisan tinker
>>> App\Models\User::where('email','seu@email.ao')->update(['password' => bcrypt('sua-senha-forte')]);
```

---

## 17. Licença e créditos

Propriedade do PIGEA.

**Desenvolvido por QNBAngola — Jorge Dange** (2026).

> Full-stack em Laravel — site público, painel administrativo, segurança, dados, deploy manual.
