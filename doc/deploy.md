# PIGEA — Deploy Manual (FTP / cPanel)

> Documento de deploy. Estado de referência: `updates` (HEAD `43e2e0d`).
> Última actualização do documento: 2026-11-14.

---

## 1. Requisitos do servidor

| Item | Versão mínima | Como confirmar |
|---|---|---|
| PHP | 8.2 | `php -v` |
| MariaDB / MySQL | 10.4+ | `mysql --version` |
| Composer | 2 | `composer -V` |
| Extensões PHP | `pdo_mysql openssl mbstring fileinfo curl gd zip bcmath dom xml tokenizer` | `php -m | grep -E '...'` |

> Pesquisa FULLTEXT exigível (MATCH AGAINST) — SQLite e PostgreSQL **não** são suportados.

---

## 2. Auditoria de pré-deploy (resposta)

### 2.1 Suite de testes
**261 testes / 885 assertions**, 100% verde (`php artisan test`). Zero falhas.

### 2.2 Compilação de assets
O projeto **não exige build no servidor**. CSS e JS são servidos directamente via `asset()`. `public/build/` está no `.gitignore`. Nada para compilar.

### 2.3 Segurança do `.env`

| Dado | Estado |
|---|---|
| `.env` no `.gitignore`? | ✅ Sim |
| `.env` nunca foi commitado? | ✅ Confirmado (`git ls-files`) |
| `.env.example` com `ADMIN_PATH` | ✅ Sim (`ADMIN_PATH=admin`) |
| `.env.example` com `PIGEA_LOGIN_PATH` | ✅ Sim (`PIGEA_LOGIN_PATH=entrar-equipa`) |
| `.env.example` com `PROJECTS_PUBLICO=false` | ✅ Sim (default degradado para prod) |

`ADMIN_PATH` e `PIGEA_LOGIN_PATH` trocam os caminhos administrativos — **é recomendado que o cliente defina valores opacos próprios em produção** (não deixar `admin` nem `entrar-equipa`).

### 2.4 Permissões de ficheiros

```bash
chmod -R 775 storage bootstrap/cache
chmod -R 755 public
find public -type f -exec chmod 644 {} \;
```

### 2.5 Rotas protegidas / invisíveis (verificadas)

| Rota | Estado | Como confirmar |
|---|---|---|
| `/login` | ✅ 404 | `curl -I https://teudominio/login` |
| `/{PIGEA_LOGIN_PATH}` | ✅ 200 (não exposta sem contar) | ex.: `/entrar-equipa` |
| `/admin` | ✅ 404 | `ADMIN_PATH` não ligado |
| `/{ADMIN_PATH}` | ✅ 200 (não exposta sem contar) | ex.: `/gestao-pigea` |

Rate limiting: **5 tentativas/minuto** por email+IP (bloqueio no `FortifyServiceProvider`).

### 2.6 Limpeza de código

| Local | Observação |
|---|---|
| `node_modules/` | Local, ignorado no git — não entra no pacote |
| `vendor/` | Local, ignorado no git — recria-se no servidor com `composer install` |
| `qa-*.png`, `relatorio-*.png` | Screenshots locais — **fora do pacote** |
| `storage/logs/*.log` | **Limpar antes** de empacotar: `php artisan log:clear` |
| `storage/framework/views/*.php` | **Limpar** (view cache local) |
| `.phpunit.result.cache` | Fora do git |
| `doc/` (docs interno) | Opcional — não enviar |
| `tests/` | Não enviar |
| `phpunit.xml` | Não enviar |
| `.env`, `.env.backup` | Não enviar |

---

## 3. Lista de entrega para o servidor

**Enviar:**
```
app/        bootstrap/    config/    database/    lang/
public/     resources/    routes/    storage/
vendor/     artisan       composer.json   composer.lock
```

**NÃO enviar:**
```
.env       (criar no servidor)
node_modules/   .git/   tests/
phpunit.xml    qa-*.png    relatorio-*.png
storage/logs/*.log   (limpar antes)
storage/framework/views/*.php  (limpar antes)
backups/    composer             (local)
doc/        (opcional — não é necessário no servidor)
.gitignore/.gitattributes  (opcional)
```

**Bloco a verificar no servidor após upload:**
```bash
chmod -R 775 storage bootstrap/cache
```

---

## 4. Instalação manual (cPanel / FTP)

### 4.1 Criar a base de dados

1. No cPanel: **MySQL® Databases** → criar a base `pigea` (ou nome livre).
2. Criar utilizador `pigea_user` com boa password.
3. Atribuir **ALL PRIVILEGES** ao utilizador na base.
4. Anotar: host MCP (geralmente `localhost` para MySQL do cPanel), nome da BD, utilizador e password — vão para o `.env`.

### 4.2 Upload dos ficheiros

1. Criar a pasta `public_html/` (ou escolher o domínio de destino).
2. Enviar o pacote para essa pasta (FTP/Gestor de Ficheiros).

### 4.3 Configurar o `.env` no servidor

```bash
cp .env.example .env
nano .env
```

**Preencher OBRIGATORIAMENTE:**

| Chave | Valor de produção |
|---|---|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://site.ao` |
| `APP_KEY` | deixar vazio → `php artisan key:generate` gera |
| `ADMIN_PATH` | opaco (ex.: `gestao-x7a9`) |
| `PIGEA_LOGIN_PATH` | opaco (ex.: `painel-y3k8`) |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `localhost` |
| `DB_PORT` | `3306` |
| `DB_DATABASE` | `pigea` (ou o nome criado) |
| `DB_USERNAME` | `pigea_user` (ou criado) |
| `DB_PASSWORD` | a password forte criada |
| `PROJECTS_PUBLICO` | `false` |
| `MAIL_MAILER` | `smtp` (ou `ses`, `mailgun` — conforme o provedor) |
| `MAIL_HOST` | host SMTP |
| `MAIL_PORT` | `587` (ou `2525` / `465` conforme o provedor) |
| `MAIL_USERNAME` | utilizador SMTP |
| `MAIL_PASSWORD` | password SMTP |
| `MAIL_ENCRYPTION` | `tls` (ou conforme o provedor) |
| `MAIL_FROM_ADDRESS` | `geral@pigea.ao` (ou o email do site) |
| `MAIL_FROM_NAME` | `PIGEA` |
| `SESSION_DRIVER` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `CACHE_STORE` | `database` |

> Tudo com `MAIL_MAILER=log` **não** se recomenda — as confirmações da Newsletter nunca chegariam.

### 4.4 Gerar APP_KEY e migrar

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan storage:link
```

> O `RoleSeeder` cria as permissões
> (`news.manage`, `project.manage`, `document.manage`, `banner.manage`, `video.manage`, …).
> Sem ele, o primeiro admin não consegue entrar no painel.

### 4.5 Criar o primeiro administrador

```bash
php artisan pigea:criar-admin admin@pigea.ao
```

O comando pergunta nome e password (a password não aparece no histórico).

### 4.6 Limpar cache e confirmar

```bash
php artisan optimize:clear
```

**E depois navegar:**
- `https://teudominio/` → homepage pública.
- `https://teudominio/{PIGEA_LOGIN_PATH}` → tela de login da equipa.

---

## 5. Troubleshooting

| Sintoma | Causa provável / remédio |
|---|---|
| 500 na homepage | `APP_KEY` vazio → `php artisan key:generate` |
| 500 ao logar | `APP_DEBUG=false` esconde o erro — logs em `storage/logs/laravel.log` |
| Imagens faltando | `php artisan storage:link` não foi corrido |
| Ficheiros cache não gravam | `storage/` não tem 775 → `chmod -R 775 storage bootstrap/cache` |
| Login não funciona | `MAIL_MAILER` (ainda) `log` — confirmações de reset perdem-se; o login local está ok, mas forgot-password não envia email |
| /admin público responde | `ADMIN_PATH` não foi redefinido no `.env` — publica a rota |
| Não consigo criar admin | `RoleSeeder` não correu — `php artisan db:seed --class=RoleSeeder --force` |

---

## 6. Checklist go-live (antes do site ser anunciado)

- [ ] `.env` em produção com `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` gerada
- [ ] `ADMIN_PATH` e `PIGEA_LOGIN_PATH` redefinidos para valores opacos
- [ ] `PROJECTS_PUBLICO=false` (ou `true`, se o cliente decidir ligar)
- [ ] SMTP configurado (e um email de teste recebido com sucesso)
- [ ] `php artisan migrate --force` sem erros
- [ ] `php artisan storage:link` executado
- [ ] `php artisan pigea:criar-admin <email-real>` executado
- [ ] `php artisan optimize:clear` executado
- [ ] `storage/` e `bootstrap/cache/` com 775
- [ ] `php artisan test` em dev (opcional, mas recomendado)

---

## 7. Verificação final

```bash
curl -I https://teudominio/{PIGEA_LOGIN_PATH}     # 200
curl -I https://teudominio/login                  # 404
curl -I https://teudominio/admin                  # 404
curl -I https://teudominio/{ADMIN_PATH}           # 302 → login (ou 200 já autenticado)
```

Caso confirmes estes quatro, o deploy está pronto.
