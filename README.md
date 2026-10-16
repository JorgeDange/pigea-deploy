# PIGEA — Plataforma de Impulsionamento das Geociências em Angola

Aplicação Laravel que serve o site do **PIGEA**: notícias, projetos mineiros,
emprego, diretório de empresas, investigação, legislação, eventos e
entrevistas sobre geociências em Angola.

| | |
|---|---|
| **Framework** | Laravel 12 · PHP 8.2+ |
| **Base de dados** | MariaDB / MySQL 10.4+ (obrigatória, ver abaixo) |
| **Idioma** | Português (`APP_LOCALE=pt`, `lang/pt/` instalado) |
| **Timezone** | `Africa/Luanda` (UTC+1, sem horário de verão desde 1911) |
| **Frontend** | HTML/CSS/jQuery servidos directamente — sem passo de build |

---

## 1. Requisitos

| Item | Versão mínima | Confirmar com |
|---|---|---|
| PHP | 8.2 | `php -v` |
| MariaDB / MySQL | 10.4+ | `mysql --version` |
| Composer | 2 | `composer -V` |

**Extensões PHP obrigatórias** — sem elas o Laravel nem arranca:

```
pdo_mysql  openssl  mbstring  fileinfo  curl  gd  zip  bcmath  dom  xml  tokenizer
```

Verificar:

```bash
php -m | grep -E '^(pdo_mysql|openssl|mbstring|fileinfo|curl|gd|zip|bcmath|dom|xml|tokenizer)$'
```

**Porquê MariaDB/MySQL e não SQLite/PostgreSQL**: a pesquisa usa
`MATCH … AGAINST` (índice FULLTEXT) e as contagens mensais do dashboard usam
`DATE_FORMAT`. É o único motor suportado.

**Não é preciso build de frontend**: as vistas ligam CSS e JS com `asset()` e
não usam `@vite`, por isso `npm` não é necessário para pôr o site no ar. O
`package.json` existe mas só é útil se algum dia se passar a usar Vite.

---

## 2. Instalação

```bash
git clone https://github.com/JorgeDange/pigea-deploy.git pigea
cd pigea
composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan key:generate
```

Preencher o `.env` (ver §3), depois:

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force      # papéis e permissões
php artisan db:seed --class=ContentSeeder --force   # o conteúdo do site — ver §4
php artisan pigea:criar-admin direccao@pigea.ao --nome="Direcção PIGEA"
php artisan storage:link
php artisan optimize
```

---

## 3. Configuração do `.env`

O `.env.example` vem em modo **desenvolvimento** (`DB_CONNECTION=sqlite`,
`APP_TIMEZONE=UTC`, `APP_DEBUG=true`, `ADMIN_PATH=admin`). Em produção é
preciso mudar pelo menos isto:

| Chave | Valor em produção | Porquê |
|---|---|---|
| `APP_KEY` | `php artisan key:generate --force` | Sem chave a app não arranca |
| `APP_URL` | `https://…` | Usada em links e cookies |
| `APP_DEBUG` | **`false`** | Com `true` o site expõe stack traces com caminhos e variáveis |
| `APP_ENV` | `production` | |
| `APP_LOCALE` | `pt` | Com `en` o utilizador vê chaves (`validation.required`) |
| `APP_TIMEZONE` | `Africa/Luanda` | UTC+1, sem horário de verão — sem ambiguidade na BD |
| `ADMIN_PATH` | valor próprio e discreto | Ver §5 |
| `DB_CONNECTION` | `mysql` | **Não** `sqlite` — o motor tem de ser FULLTEXT |
| `DB_HOST/PORT/DATABASE/USERNAME/PASSWORD` | credenciais reais | |
| `MAIL_HOST/PORT/USERNAME/PASSWORD` | servidor SMTP real | Com `MAIL_MAILER=log` os emails são descartados em silêncio |
| `TRUSTED_PROXIES` | `127.0.0.1` (ou rede do proxy) | Ver §6 — é o que faz o HSTS sair |
| `SESSION_ENCRYPT` | `true` | Encripta o conteúdo do cookie de sessão |
| `BCRYPT_ROUNDS` | `12` | |

> **Depois de qualquer alteração ao `.env` corra `php artisan optimize`.**
> Com a config cacheada o `.env` deixa de ser lido — os valores ficam congelados
> no momento do cache. Em caso de dúvida: `php artisan optimize:clear`.

---

## 4. Seeders — quais correr

```bash
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=ContentSeeder --force
```

Sempre **por classe** — nunca `php artisan db:seed` sem `--class`, que correria o
`DatabaseSeeder` e criaria as contas de demonstração.

| Seeder | Em produção? | Porquê |
|---|---|---|
| `RoleSeeder` | **Sim — obrigatório** | Cria os 4 papéis e as 18 permissões (Spatie). Sem ele nada autoriza nada. Correr **primeiro**. Idempotente. |
| `ContentSeeder` | **Sim — é o conteúdo do site** | Carrega os **113 registos reais** de `database/data/*.json`: 20 notícias, 9 projetos mineiros, 8 vagas, 8 fornecedores, 8 eventos, 8 legislações, 9 investigações, 9 entrevistas, 9 tecnologias, 8 empresas, 9 formações — mais 9 autores e 62 categorias. **Sem ele o site fica vazio.** Idempotente (`firstOrCreate` por `slug`). |
| `PainelSeeder` | **Não** | Cria as contas de demonstração `utilizador@pigea.ao` e `empresa@pigea.ao` — ambas com **password `password`** — e candidaturas, notificações e oportunidades de exemplo. |
| `DatabaseSeeder` | **Não** | Chama os três anteriores e acrescenta `test@example.com`. |

> O `ContentSeeder` **não** é conteúdo de lixo: é a extração do protótipo para a
> base de dados — os textos reais que o site vai publicar. Verificou-se por
> comparação de `slug` que as 11 tabelas de conteúdo ficam **idênticas** às
> originais (a única diferença na BD de desenvolvimento são 3 linhas
> "Vaga de teste temporaria" que ficaram de testes e que o seeder **não**
> reproduz — o seeder é mais limpo do que a BD de dev).

### Criar o primeiro administrador

O painel exige o papel `admin` para entrar, e o modal que cria utilizadores
está *dentro* desse painel — logo é preciso um admin antes de haver interface
para criar mais.

```bash
php artisan pigea:criar-admin direccao@pigea.ao --nome="Direcção PIGEA"
```

A password é pedida duas vezes em modo oculto e **não** é argumento de linha de
comandos (apareceria no `ps` e no histórico). O comando valida tudo antes de
gravar, atribui o papel `admin`, recusa emails repetidos e avisa se o
`RoleSeeder` ainda não correu.

---

## 5. Painel admin

O painel vive em `/{ADMIN_PATH}/…` em vez de `/admin`, para não ser óbvio a
varrimentos. **Só muda o prefixo da URL**: os nomes das rotas continuam
`admin.*`.

O caminho antigo `/admin` responde **404 sem redirect** — um redirect
denunciaria o novo caminho.

> **Isto não é uma barreira de segurança.** Quem decide o acesso é o papel
> `admin`. Trate o `ADMIN_PATH` como obscurecimento e escolha um valor que não
> venha deste repositório.

---

## 6. Servidor web e HTTPS

O *document root* aponta para **`pigea/public/`** — nunca para a raiz de
`pigea/`, senão `.env`, `vendor/` e `storage/` ficam descarregáveis.

```nginx
server {
    listen 443 ssl http2;
    server_name www.pigea.ao pigea.ao;

    root /var/www/pigea/public;
    index index.php;

    # O nginx só entrega o que estiver aqui — protege .env/.git de trocas.
    location ~ /\.(?!well-known) { deny all; }

    add_header X-Content-Type-Options nosniff always;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Permissões de escrita — o utilizador do servidor (ex. `www-data`) precisa de
escrever em `storage/` e `bootstrap/cache/`, e **nenhum dos dois pode ser
acessível pela web**:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

### HSTS — `TRUSTED_PROXIES`

Os cabeçalhos de segurança (incluindo `Strict-Transport-Security`) só saem
quando o PHP vê o pedido como HTTPS. Com um proxy (nginx, load balancer) a
terminar TLS, o PHP vê `http://` e **o HSTS nunca apareceria, sem nenhum erro
a indicar**. É o `TRUSTED_PROXIES` que resolve:

```env
TRUSTED_PROXIES=127.0.0.1
```

- proxy na mesma máquina → `127.0.0.1`
- proxy separado → a rede privada dele (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`)
- PHP directo, sem proxy → vazio

Com a lista vazia o HSTS **não sai mesmo que chegue `X-Forwarded-Proto: https`** —
é de propósito: qualquer cliente pode mandar esse cabeçalho, e a confiar nele à
cadastra ficava o browser preso em HTTPS num host que não o serve.

Verificar:

```bash
curl -sI https://www.pigea.ao/ | grep -i strict-transport
# esperado: strict-transport-security: max-age=31536000; includeSubDomains
```

---

## 7. Verificação após o deploy

```bash
curl -sI https://www.pigea.ao/up              # → 200  (health check do Laravel)
curl -sI https://www.pigea.ao/admin           # → 404  (comportamento correcto)
curl -sI https://www.pigea.ao/<ADMIN_PATH>    # → 302 → /login
curl -sI https://www.pigea.ao/ | grep -Ei 'content-security-policy|strict-transport|x-frame|x-content-type'
```

Checklist:

- [ ] `APP_DEBUG=false` (de forma nenhuma com `true`)
- [ ] `/{ADMIN_PATH}` → 302 → login → entra e vê o dashboard
- [ ] `Strict-Transport-Security` presente em HTTPS
- [ ] Email funciona (testar "Esqueci a password")
- [ ] Upload nas configurações → imagem visível (`storage:link`)
- [ ] Pesquisa devolve resultados (ex.: `geologia`)
- [ ] Nenhum utilizador de demonstração activo

---

## 8. Segurança activa por omissão

Cabeçalhos CSP, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`
e `Permissions-Policy` em **todas** as respostas (incluindo 404 e 500);
encriptação da cookie de sessão; cookies `secure`; autorização por papel e
permissão em cada rota admin; verificação de posse nos recursos; CSRF em todos
os formulários.

> A CSP está em modo **Report-Only** de propósito — os relatórios vão para
> `POST /csp-report`. Quando os relatórios limparem, pode passar a obrigatória
> em `app/Http/Middleware/SecurityHeaders.php`.

---

## 9. Testes

A suite corre contra uma base de dados `pigea_test` à parte (ver `phpunit.xml`):

```bash
php artisan test
```

Estado em 16/10/2026: **158 testes, 487 asserções, todos verdes.**

---

## 10. Estrutura

```
app/Console/Commands/     pigea:criar-admin
app/Http/Controllers/     rotas públicas, painel admin, painéis de utilizador/empresa
app/Http/Middleware/      SecurityHeaders (CSP, HSTS, X-Frame-Options…)
bootstrap/                arranque do Laravel (trustProxies lê config/trustedproxy.php)
config/                   app.php (admin_path, timezone) · trustedproxy.php · …
database/migrations/      esquema completo
database/seeders/         RoleSeeder (produção) · Content/PainelSeeder (demo)
lang/pt/                  traduções portuguesas do framework
public/                   document root — CSS, JS, imagens, uploads
resources/views/          Blade: site público, painel admin, painéis de utilizador
routes/web.php            rotas públicas + admin.* (prefixo por ADMIN_PATH)
tests/Feature/            158 testes de autorização, formulários, CSP, comandos
```

### Contas de demonstração — não as crie em produção

| Email | Papel | Password | Vem de |
|---|---|---|---|
| `test@example.com` | admin | `password` | `DatabaseSeeder` |
| `utilizador@pigea.ao` | utilizador | `password` | `PainelSeeder` |
| `empresa@pigea.ao` | empresa | `password` | `PainelSeeder` |

Se **não** correr `PainelSeeder` nem `DatabaseSeeder` (§4), estas contas nunca
existem na base de dados de produção — é a forma correcta de as evitar. Se já
existirem, remova-as.

---

## Licença

Propriedade do PIGEA.
