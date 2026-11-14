# Documentação Técnica Interna — PIGEA

> Catálogo único de arquitectura e decisões do projecto (2026-11-14).
> Complementa `doc/` (que contém o histórico de fases e decisões do cliente).
> É este ficheiro que tens de ler primeiro se fores novo no código.

---

## 1. Arquitectura global

| Camada | Tecnologia | Onde |
|---|---|---|
| Backend | Laravel 12 / PHP 8.2 | `app/` |
| Sessão | DB (`sessions`) | — |
| Autenticação | Fortify (login + 2FA + passkeys) | `config/fortify.php` |
| Autorização | Spatie Permission (`admin`, `editor`) | `config/permission.php` |
| Front-end público | Blade + jQuery (legacy) | `resources/views/`, `public/js/` |
| Admin | Blade + Livewire 3 | `resources/views/livewire/admin/`, `app/Livewire/Admin/` |
| Persistência | MariaDB 10.4+ (FULLTEXT MATCH … AGAINST) | `database/migrations/` |
| Busca pública | Scout `database` driver | `app/Providers/AppServiceProvider.php` |
| Cache | DB (`cache` + `jobs`) | `config/queue.php`, `config/cache.php` |
| Filas | DB (`jobs`) | `app/Console/Commands/PublishScheduled.php` |
| Imagem fallback | `images/logo.png` global | `Setting::imagem()` |

- Prefixo do painel: `ADMIN_PATH` (`.env`) — nomes de rota continuam `admin.*`.
- Login opaco: `PIGEA_LOGIN_PATH` — `/login` devolve 404.

---

## 2. Decisões de negócio que não podem ser adivinhadas no código

### 2.1 Projetos excluídos temporariamente (cliente, 2026-11-13)

`PROJECTS_PUBLICO=false` no `.env` (e no `.env` de produção).
- `/projetos` e `/projetos/{slug}` → **404** no público.
- Suite força `PROJECTS_PUBLICO=true` (`phpunit.xml`) → 261/885 verde.
- No admin, a área mantém-se (`/gestao-pigea/projetos` + `form novo`) — só o público não vê nada.
- Para religar: `PROJECTS_PUBLICO=true` (migração + menu já preparados).

### 2.2 Newsletter — opt-in de 2 passos (cliente, 2026-11-13)

`newsletter_subscribers` (`email` único, `token` 64 chars, `status` enum).

- Modal público em todas as páginas (`components/newsletter-modal`) — aparece
  3 s após o carregamento; some se o utilizador submeter.
- Confirmação por email antes de entrar na lista (`pending` → `confirmed`).
- Painel `/gestao-pigea/newsletter` — confirmar/cancelar/reabilitar, export CSV.

### 2.3 Anti-bot em todos os contadores

`App\Support\ContadorDeVisitas::contar($request, $modelo, $prefixo)` — bot se:
- user-agent vazio;
- user-agent bate com a lista ( Googlebot / Bingbot / Yandexbot / espaço… );
- a sessão já leu ESTA página em < 5 min.

Contagem por sessão; não é uma garantia de unique views; é intencional.

### 2.4 Pin editorial na home

`AppServiceProvider::heroNews()` — a home mostra primeiro pins com `destaque_editorial=true`, depois por `published_at` DESC com fallback se não há 4 pins.

### 2.5 Destaque editorial

Coluna `news.destaque_editorial` (boolean, cast). Toggle no editor (`admin/noticia-editor`); admin mostra coluna
no table; ordenada por ID na tabela de listagem.

### 2.6 Notificações persistidas (2026-11-13)

Tabela `notifications` (UUID morph). Cada evento relevante gera uma notificação
por utilizador `admin`+`editor` restante; o sino no painel mostra as não lidas.

Gatilhos: `Company::created`, `Professional::created`, `User::created`,
`News::created/updated` (status transições).

### 2.7 Banners — posição `largo` (2026-11-13)

Substituiu todas as referências estáticas a `images/all/banner.jpg` (~15
ficheiros Blade) pelo componente `<x-banner-publicitario posicao="largo" />`.
Admin tem botão de «Largo» (com ENUM fixo no SGBD).

### 2.8 Sessão + CSRF

Laravel CSRF padrão activo em todas as POST; Livewire publica via `livewire.init`.
Contagem de visitas do site pelo middleware `CountPublicVisits` — nunca conta
páginas de admin ou `/livewire`; bots filtrados.

---

## 3. Vídeo — convenção `modelo.method()`

Três locais ligam ao mesmo mecanismo de estado para "mostra/esconde":

JavaScript vanilla (`public/js/menu.js`, `public/js/destaque.js`,
`public/js/banner-lateral.js`, `public/js/newsletter-modal.js`) — sem jQuery
nem framework. Tudo é um DOM querySelector + classList.

**Configuração do admin → `settings.redes sociais`** são pares
`<rede>` (url) e `<rede>_nome` (handle). Social icons só aparecem se houver
url ou nome — nunca um placeholder fantasma.

---

## 4. Imagens — onde vive cada uma

- `public/images/logo.png` / `logo2.png` — splash inicial; também usado como fallback global.
- `public/images/favicon.png` — idem.
- `public/images/banners/` — banners laterais do admin (upload).
- `public/storage/` — storage:link; único destino persistente dos uploads de admin.
- `public/.gitignore` — `storage/` **só existe localmente** (link). Em produção: `storage:link` novamente.

---

## 5. Twig/Blade — armadilhas

- `image_url($image, 'images/logo.png')` — sempre ter segundo argumento; sem ele devolve nulo.
- `latest('published_at')->orderByDesc('id')` — mais novo primeiro (o framework defaulted ASC, por isso está explícito).

```blade
<x-banner-publicitario posicao="largo" /> {{-- nada aparece se não há banner ativo --}}
```

---

## 6. Testes

`php artisan test` — isolado em `pigea_test` (`DB_CONNECTION=mysql` + `pigea_test`).
249+ testes; todos verdes.
`phpunit.xml` força `PROJECTS_PUBLICO=true` para a cobertura pública.

---

## 7. Deploy manual

`deploy.md` — auditoria, checklist, FTP/Guia de instalação cPanel.
`deploy.md` descreve os 7 passos pós-upload e o que NÃO enviar.
