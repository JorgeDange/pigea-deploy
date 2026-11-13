# PIGEA — Requisitos para hospedagem em hosting partilhado (deploy-detalhes.md)

**Projeto:** Portal de Notícias + Diretório PIGEA (Laravel 12 · Livewire 3 · MySQL)
**Pasta de código:** `pigea-deploy/` · **Branch:** `updates`
**Data do levantamento:** 2026-11-12 (base: inspeção real ao código, migrations, testes e ao ambiente XAMPP local — PHP 8.2.12, MySQL 8.0)

> Este documento lista **o que a hospedagem precisa de ter** para o site funcionar em produção. Toda a afirmação abaixo foi verificada no projeto; o que depende do host está marcado como «a confirmar».

---

## 1. O que vai ser hospedado

| Componente | Tecnologia | Observações |
|---|---|---|
| Aplicação web | Laravel 12 (PHP ^8.2) | Código em `pigea-deploy/` |
| Painel admin | `/gestao-pigea` (`ADMIN_PATH=gestao-pigea`) | Rotas `admin.*`; nomes de rota não mudam |
| Base de dados | MySQL 8 / MariaDB 10.6+ | Sessões, cache, queue e 2FA vivem na BD (ver §8) |
| Ficheiros enviados | `storage/app/public` (uploads) | Banners (2 MB máx. por imagem), capas, documentos |
| Agendamento (cron) | `news:publish-scheduled` | Publica notícias agendadas — **a cada minuto** |
| Email | SMTP real | Formulário de contactos envia para `geral@pigea.ao` |
| Frontend | CSS/JS estático do tema (`public/css`, `public/js`) | **Não usa** `@vite` no layout; TinyMCE é auto-hospedado em `public/js/tinymce` |
| Build de assets | `public/build` (gitignored) | **Sem impacto em runtime** — não referenciado pelas views |

---

## 2. Requisitos mínimos de hospedagem

### 2.1 Servidor e PHP

| Requisito | Valor exigido | Nota |
|---|---|---|
| PHP | **^8.2** (local: 8.2.12) | Obrigatório — não aceitar 8.1 ou inferior |
| Laravel | 12.x | Declarado em `composer.json` (`laravel/framework ^12.0`) |
| Extensões PHP | **pdo_mysql**, **mbstring**, **openssl**, **ctype**, **tokenizer**, **xml/dom/xmlreader/xmlwriter**, **json**, **fileinfo**, **session**, **pdo**, **hash**, **filter**, **iconv**, **curl**, **zip** | Presentes no ambiente local validado |
| Imagens | **GD com suporte WebP** **ou** **Imagick** | A MediaLibrary do Spatie converte para WebP (`webp`) nas coleções `capa`/`contexto`. **Sem GD+WebP ou Imagick, o upload de imagens quebra.** |
| mbstring / fileinfo | Sim | Requeridos pelo framework/validação |
| Composer | Idealmente disponível por SSH; caso contrário, ver §9 | Alternativa: instalar `vendor/` localmente e carregar os ficheiros |
| Acesso SSH/Terminal | Recomendado (não obrigatório) | Permite `artisan` direto; sem ele, usar cron + painel do host |

> **Confirmar com o host (pergunte antes de contratar):** «O PHP do vosso alojamento suporta 8.2+ com extensão **GD e suporte a WebP**? Se não, suporta **Imagick**?» Em cPanel isto vê-se em *Select PHP Version → Extensions* ou `phpinfo()`.

### 2.2 Base de dados

| Item | Requisito |
|---|---|
| MySQL | 8.0+ **ou** MariaDB 10.6+ |
| Caracteres | `utf8mb4` + `utf8mb4_unicode_ci` (tabelas criadas pelas migrations do projeto) |
| Privilégios | 1 base de dados + 1 utilizador com **todos** os privilégios nessa BD |
| Acesso remoto | Não é necessário (a aplicação liga-se via `localhost`/`127.0.0.1`) |

### 2.3 Limites do PHP (php.ini / MultiPHP INI Editor)

| Diretiva | Valor recomendado | Justificação |
|---|---|---|
| `upload_max_filesize` | **≥ 8M** | App limita imagens a 2048 KB (banners/capas/logo); 2 MB + margem |
| `post_max_size` | **≥ 10M** | Deve ser ≥ `upload_max_filesize` |
| `memory_limit` | **≥ 256M** | Laravel + conversões de imagem em runtime |
| `max_execution_time` | **≥ 120** | Conversão WebP é **síncrona** (`nonQueued`) no request |
| `max_input_time` | ≥ 60 | Uploads grandes |
| `max_input_vars` | ≥ 3000 | Formulários de edição longos do admin |
| `max_file_uploads` | ≥ 20 | Uploads múltiplos do editor |

### 2.4 Outras necessidades

| Item | Detalhe |
|---|---|
| **HTTPS/SSL** | Obrigatório — painel admin com `SESSION_ENCRYPT=true` e cookies seguros |
| Cron | 1 entrada a cada minuto (ver §7) |
| Certificado | cPanel *AutoSSL* / Let's Encrypt gratuito resolve |
| Domínio + DNS | Registo A para o IP do host; `www` CNAME; MX com o servidor de email |
| Backups | Pelo menos diários da BD + de `storage/app/public` (uploads) — ver §13 |
| Espaço em disco | ~500 MB a 1 GB (vendor + uploads; cresce com o conteúdo) |

---

## 3. Sessões, cache e filas (atenção — sem Redis)

O `.env.example` do projeto vem assim (e o ambiente local validado usa o mesmo):

```
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

**Consequências:**
- Não é preciso **Redis** nem memcached.
- As tabelas `cache`, `cache_locks`, `jobs`, `failed_jobs` e `sessions` são criadas pelas **migrations por defeito do Laravel** — incluídas no projeto, nada a configurar à mão.
- **Não há worker de fila a correr** em produção nem é necessário: as conversões de imagem são `nonQueued` e os "toasts" do admin são eventos de browser (Livewire), não jobs.
- O motor de pesquisa (**Scout**) usa o driver local (`collection`) — **sem serviço externo** (Algolia/Meilisearch não são necessários).

---

## 4. Email — obrigatório para o formulário de Contactos

- O `/contactos` envia email real via `Mail::raw` para **geral@pigea.ao** (`ContactForm.php`). Em produção tem de existir **SMTP funcional** (do host, ou de um serviço como o do próprio alojamento — cPanel → *Email Accounts*).
- DNS correto (MX + DKIM/SPF do domínio) senão o email cai em spam.
- Configuração no `.env`: `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT` (465 SSL ou 587 STARTTLS), `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME=PIGEA`.
- **Não** usar `MAIL_MAILER=log` em produção (o email ficaria só no ficheiro de log).

---

## 5. Estrutura de pastas e docroot

- O **document root deve apontar para `pigea-deploy/public/`** — nunca para a raiz do projeto (o `.env` e o código não podem ficar acessíveis pela web).
  - cPanel: *Domains → Document Root* = `/home/<user>/pigea/public` (ou `public_html` com o conteúdo de `public/` e caminhos ajustados — preferir mudar o docroot), ou
  - VPS/Apache: `DocumentRoot /home/pigea/public` + `AllowOverride`.
- `public/.htaccess` já existe (reescrita do Laravel) — o host tem de permitir reescrita (Apache `mod_rewrite`; comum em hosting partilhado).
- `.env` **fora** do docroot; nunca versionado (`.env` está no `.gitignore`; só `.env.example` vai no repo).
- **Permissões:** ficheiros 644, pastas 755; `storage/` e `bootstrap/cache/` graváveis pelo PHP (775; em último recurso 777 nessas duas pastas).
- **Symlink de uploads:** `php artisan storage:link` cria `public/storage → storage/app/public`. Sem SSH, alternativa: copiar o conteúdo de `storage/app/public` para `public/storage` após cada lote de uploads (possível mas frágil — **preferir hospedagem com terminal ou symlink via cron**).

---

## 6. Variáveis de ambiente de produção (.env)

```dotenv
APP_NAME="PIGEA"
APP_ENV=production
APP_KEY=            # gerar com: php artisan key:generate
APP_DEBUG=false     # OBRIGATÓRIO — nunca true em produção
APP_URL=https://www.pigea.ao   # ou o domínio final
APP_TIMEZONE=Africa/Luanda
APP_LOCALE=pt
APP_FALLBACK_LOCALE=en

SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true     # forçar cookie só em HTTPS
ADMIN_PATH=gestao-pigea        # valor não-óbvio (NÃO usar "admin" em produção)

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<nome_da_base>
DB_USERNAME=<utilizador>
DB_PASSWORD=<palavra-passe forte>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=<smtp do host>
MAIL_PORT=465
MAIL_USERNAME=geral@pigea.ao
MAIL_PASSWORD=<…>
MAIL_FROM_ADDRESS=geral@pigea.ao
MAIL_FROM_NAME="PIGEA"
```

> Nota: `APP_DEBUG=false` expõe nenhuma informação técnica em erros; o conteúdo aparece genérico ao visitante.
> `ADMIN_PATH` só muda o prefixo da URL do painel (as rotas internas continuam `admin.*`). É **obscuridade, não segurança** — a proteção real é a autenticação Fortify + 2FA da equipa.

---

## 7. Agendamento (cron) — obrigatório

Uma única entrada, cada minuto, a correr como o utilizador do site:

```cron
* * * * * cd /home/<user>/pigea && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

- Executa `news:publish-scheduled` (publica notícias cuja data/hora chegou ao instante).
- Drivers com horário em uso: TZ da aplicação (`APP_TIMEZONE=Africa/Luanda`).
- Sem este cron, **as notícias agendadas nunca publicam** (o painel mostra o agendamento, mas nada dispara).
- Confirmação: em cPanel → *Cron Jobs*; com VPS → `crontab -u <user> -e`. Caminho do PHP: `which php` ou `/usr/local/bin/php` / `/opt/cpanel/ea-php82/root/usr/bin/php`.

---

## 8. Instalação — dois cenários

### Cenário A — hosting com SSH/Terminal (recomendado)

```bash
cd /home/<user>
git clone <URL-do-repo> pigea        # ou upload por FTP do código
cd pigea
composer install --no-dev --optimize-autoloader
cp .env.example .env
# editar .env (§6)
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache route:cache view:cache
php artisan pigea:criar-admin        # cria o 1.º admin (email + password de prova no arranque)
```

### Cenário B — hosting partilhado sem SSH/Composer

1. Na máquina de desenvolvimento (já tem tudo): `composer install --no-dev --optimize-autoloader`.
2. Carregar por FTP **todo o projeto**, incluindo `vendor/`, exceto: `node_modules/`, `.git/`, `tests/` (opcional), `.env`.
3. Carregar a pasta `storage/` inteira (o symlink `public/storage` não sobrevive ao FTP — recriá-lo ou copiar `storage/app/public` → `public/storage`).
4. Definir `.env` de produção (upload + edição no gestor de ficheiros do cPanel).
5. Migrations **uma única vez**, via cPanel *Cron Jobs* temporário ou terminal do cPanel:
   ```cron
   # Entrada temporária — apagar depois de correr uma vez
   */1 * * * * cd /home/<user>/pigea && php artisan migrate --force >> /home/<user>/migrate.log 2>&1
   ```
6. Criar o primeiro admin: `php artisan pigea:criar-admin` (mesmo método) ou criar um utilizador direto na tabela `users` (a password tem de ser armazenada com *bcrypt* — **usar sempre o comando**).
7. `php artisan config:cache route:cache view:cache` — só se houver terminal/cron; ou aceitar o custo no primeiro request.

> **Importante em ambos os cenários:** a base de dados de produção começa **vazia**. **Não correr** `db:seed` com os seeders de demonstração (`DirectorySeeder`, `ContentSeeder`, `BannerSeeder`…) — eles existem para testar a moderação (registos "aguardando aprovação"/"rejeitado" são fictícios). Seedar só o necessário (`db:seed --class=RoleSeeder` se aplicável) ou deixar a equipa criar conteúdo real.

---

## 9. Checklist de verificação pós-deploy

1. `https://<domínio>/` → 200; imagens e CSS carregam (F12 *Network*).
2. `https://<domínio>/noticias` e `https://<domínio>/noticias?cat=academia` → 200 (mesmo vazia, com empty-state).
3. `/gestao-pigea` → ecrã de login do admin; login com o admin criado via `pigea:criar-admin`; saída e reentrada.
4. Upload de banner pelo admin (imagem ≤ 2048 KB) → grava em `storage/app/public` e aparece no site.
5. `https://<domínio>/contactos` → enviar formulário → email chega a `geral@pigea.ao`.
6. Cron: agendar uma notícia para daqui a 2 minutos → verificar que publica sem intervenção.
7. `view-source` do HTML → sem mojibake (`Ã`, `�`); `fonts`/FA carregam.
8. `robots.txt` permite indexação (`Disallow:` vazio) — confirmar que é o desejado.
9. Certificado SSL válido (cadeado verde); `http://` redireciona para `https://`.
10. `APP_DEBUG=false` (ver página de erro 500 propositada — tem de ser genérica).
11. Backup da BD feito e **restauração testada** pelo menos uma vez.

---

## 10. Backups e manutenção

- **Diário:** dump da BD (`mysqldump -u <user> -p <bd> | gzip > pigea-$(date +%F).sql.gz`) + cópia de `storage/app/public/`.
- **Retenção mínima:** 14 dias.
- **Antes de atualizar o código:** backup completo + `php artisan migrate --force` dentro de janela de manutenção.
- Restaurar: importar o dump + repor `storage/app/public` → validar admin e 3 páginas públicas.

---

## 11. O que o cliente precisa de fornecer / decidir

| # | Item | Responsável |
|---|---|---|
| 1 | Domínio final (ex.: `pigea.ao` + `www`) e acesso ao DNS | Cliente |
| 2 | Conta de alojamento (cPanel/plesk) com PHP 8.2+, GD+WebP (ou Imagick) e MySQL | Cliente — **a confirmar por escrito com o fornecedor** |
| 3 | Conta de email `geral@pigea.ao` + credenciais SMTP | Cliente |
| 4 | Nome/utilizador/palavra-passe da base de dados | Cliente |
| 5 | Email(s) do(s) administrador(es) do painel | Cliente |
| 6 | Hora local para o cron e TZ (`Africa/Luanda`) | Confirmado: Africa/Luanda |
| 7 | Conteúdo real para substituir a demonstração (notícias, projetos, banners, empresas/profissionais se o diretório arrancar público) | Cliente/equipa PIGEA |

---

## 12. Riscos de hospedagem partilhada e quando subir para VPS

| Risco (partilhado) | Mitigação |
|---|---|
| Sem SSH → deploys manuais e erros de FTP | Exigir terminal cPanel/cron; ou VPS pequeno (ex.: 2 vCPU / 2 GB RAM) — custo similar a um hosting empresarial |
| Limites de CPU/memória por processo | Conversões WebP são síncronas; em imagens 2 MB costuma caber em 256M/128 s |
| Contadores de visitas + lista de notícias sem cache agressivo | `config:cache`/`view:cache`; `page_views` em BD já está indexado |
| Email do formulário em spam | SPF/DKIM/DMARC no DNS + SMTP real (não `mail()`) |

**Regra prática:** se o host não garantir **PHP 8.2 + GD/Imagick com WebP + MySQL 8 + cron por minuto**, não contratar — migrar para VPS gerido ou para hosting com terminal Laravel-ready.

---

## 13. Resumo executivo (1 ecrã)

> Hospedagem partilhada **é suficiente** desde que tenha: **PHP 8.2+ (GD+WebP ou Imagick) · MySQL 8/MariaDB 10.6+ · SMTP real · SSL gratuito · cron a cada minuto · documentação do docroot apontando para `public/` · 500 MB–1 GB**.
> Sessões/cache/filas vivem na base de dados (sem Redis); não há worker de fila; pesquisa é local (Scout `collection`); frontend é CSS/JS estático (sem Node em produção); o único artefacto de build (`public/build`) não é referenciado e não vai para o servidor.
> Primeiro deploy: backup zero → código + `vendor/` por FTP (ou `git clone`) → `.env` de produção → migrations → `storage:link` → `pigea:criar-admin` → cron → teste do checklist §9.
> **Nada de dados de demonstração em produção** (empresas/profissionais "aguardando aprovação", banners de teste) — a BD começa limpa; o conteúdo entra pela equipa via `/gestao-pigea`.

---

## Anexos — evidências do levantamento (2026-11-12)

- `composer.json`: `php ^8.2`, `laravel/framework ^12.0`, `spatie/laravel-medialibrary ^11.23` (conversões WebP `nonQueued`), `laravel/fortify`, `livewire/livewire ^3.8`, `spatie/laravel-permission`.
- `.env.example`: `DB_CONNECTION=sqlite` (default) → produção usa **mysql**; `SESSION_DRIVER`/`CACHE_STORE`/`QUEUE_CONNECTION=database`; `SESSION_ENCRYPT=true`.
- `config/scout.php` inexistente → driver `collection` (sem serviço externo).
- Uploads: `NewsEditorController` capa `max:2048` (imagem), `BannerFormController`/`DestaquesController` `max:2048`, `SettingsController` logo idem.
- `ContactForm.php`: envio real para `geral@pigea.ao` (`Mail::raw`).
- Scheduler local (`storage/logs/scheduler.log`) corre `news:publish-scheduled` **a cada minuto** — replicar via cron.
- Ambiente validado: PHP 8.2.12, MySQL 8.0.46, GD disponível; suite PHPUnit **OK (208 tests / 709 assertions)**.
- Migrações para sessões/cache/fila: `0001_01_01_000001_create_cache_table.php`, `0001_01_01_000002_create_jobs_table.php`, sessões via `0001_01_01_000000_create_users_table.php`.
- `pigea:criar-admin` (comando próprio) é o caminho suportado para o primeiro administrador.
- `public/.htaccess` presente; `robots.txt` sem bloqueios; `.env` no `.gitignore`.
