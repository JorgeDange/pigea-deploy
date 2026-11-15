# PIGEA — Correção do erro «Route [equipa] not defined» em produção (2026-11-15)

**Autor:** Jorge Dange / QNBAngola · **Data:** 2026-11-15 · **Fonte do erro:** `erro.md` (log de produção, 2026-11-15 20:59:10)

---

## 1. Diagnóstico (evidência)

**Erro:** `Route [equipa] not defined. (View: .../components/main-menu.blade.php)` em **todas** as páginas públicas (a `home` é renderizada por `View::make('home')` e o menu principal é um componente incluído nela).

**Causa raiz:** deploy parcial da release `18b9c82` («Equipa/Parceiros institucionais + Sobre Nós editável», 2026-11-15 18:49). O servidor recebeu **`app/Support/MenuCategorias.php`** (que aponta `DESTINOS['sobre']['equipa'] => route('equipa')`) **sem receber `routes/web.php`** com a nova rota pública:

```php
Route::get('/equipa', [TeamController::class, 'index'])->name('equipa');
```

(e sem `TeamController` / views / patch da BD — ou então com `route:cache`/views compiladas antigas; ambas as variantes produzem **exatamente** este erro).

**Prova local (2026-11-15):**
- `php artisan route:list --path=equipa` → rota `equipa` existe e aponta para `Frontend\TeamController@index` (mais `admin.equipa*` e `entrar-equipa`).
- `php artisan test` → **279 passed / 962 assertions**, incluindo `InstitucionalTest` (17 casos).
- Working tree limpo, `updates` == `origin/updates` — o código local está correto; **o problema é só no servidor**.

**O que a produção TEM / NÃO TEM (dedução pelo log):**
- TEM: `MenuCategorias.php` novo (só ele lança a exceção dentro de `grupos()` ao resolver o destino «equipa»).
- NÃO TEM (ou tem em cache antigo): `routes/web.php`, `TeamController`/`PartnerController`, views `equipa`/`parceiros`, migrações `2026_11_18_000000_create_team_members_and_partners_tables.php`, `Institucional.php`.

## 2. Correção (fazer pela ordem, no servidor — FTP + cPanel)

Isto **não é um patch local** — o código local já está correto. A correção é **completar o deploy parcial** seguindo `doc/orienta_deploy.md` (que já descreve esta release). Resumo executável:

1. **Backup:** dump completo da BD (phpMyAdmin → Exportar → SQL) + zip/tar do estado atual (`backup-pigea-2026-11-15.tar.gz`), fora do docroot. **Sem o dump fresco, não tocar em nada.**
2. **`prod-patch-2026-11-16.sql`** no phpMyAdmin (cria `team_members` + `partners` como migrações `2026_11_18/2026_11_19` e as outras 27 — verificação final: `migrations` com **≥ 29** registos).
3. **Upload FTP** (documento raiz `…/pigea-deploy/`; preservar `.env`, `storage/`, `vendor/`):
   - `routes/web.php` ← **este ficheiro em falta é a causa imediata do erro**
   - `app/Http/Controllers/Frontend/TeamController.php`, `PartnerController.php`
   - `app/Http/Controllers/Admin/TeamFormController.php`, `PartnerFormController.php`
   - `app/Models/TeamMember.php`, `Partner.php`, `app/Support/Institucional.php`, `app/Support/MenuCategorias.php`
   - `resources/views/equipa.blade.php`, `parceiros.blade.php`, `about.blade.php`, `home.blade.php`, `admin/equipa*.blade.php`, `admin/parceiro*.blade.php`, `admin/configuracoes.blade.php`, `components/sidebar-admin.blade.php`, `components/destaque.blade.php`
   - `public/css/style.css`, `public/js/scripts.js`
   - **APAGAR no servidor:** `app/Http/Controllers/Frontend/FragmentController.php`, `app/Mail/NewsletterConfirmation.php`, `resources/views/fragments/news.blade.php`, `resources/views/emails/newsletter-confirmacao.blade.php`
   - (Resto da lista completa: ver `doc/orienta_deploy.md` §5.2 — na dúvida, subir `app/`, `routes/`, `resources/`, `config/`, `database/`, `public/css/`, `public/js/` inteiros.)
4. **`.env` de produção:** acrescentar apenas o bloco `MAIL_GERAL_*` (§4 do orienta_deploy) — nada mais.
5. **Limpar caches** (com SSH: `php artisan optimize:clear` + `php artisan storage:link`; sem SSH: apagar `storage/framework/views/*.php` compilados e `storage/framework/cache/data/*` + hard refresh Ctrl+F5). **Este passo é obrigatório** — se a produção subiu o `routes/web.php` mas ficou com `bootstrap/cache/routes-*.php` ou views compiladas antigas, só isto resolve.
6. **Smoke tests** (orienta_deploy §8): `/` → 200 · `/equipa` → 200 · `/parceiros` → 200 · `/sobre` → 200 (textos novos) · Mega Menu com «Equipa PIGEA» · `/gestao-pigea` login sem erros.

## 3. Por que aconteceu — e como evitar

- Causa de processo: upload «tímido» (só alguns ficheiros) de uma release multi-ficheiro. O `main-menu` é incluído por **todas** as páginas públicas → um `route()` em falta derruba o site inteiro (white screen / 500 genérico).
- Regra para as próximas releases: **nunca** subir parcialmente `app/Support/` + `routes/` sem os respectivos controllers/views; sempre `optimize:clear` (ou apagar views compiladas) no fim; sempre smoke test da home antes de largar.
- Defesa em profundidade (opcional, fora de escopo desta correção): `MenuCategorias::grupos()` poderia usar `Route::has()` e cair para o link da secção em vez de rebentar — não aplicado agora para manter a release testada (279/279) inalterada.

## 4. Estado local (verificado)

- Branch `updates` == `origin/updates`, árvore limpa.
- Rotas `equipa`, `parceiros`, `admin.equipa*`, `admin.parceiros*` registadas (`route:list`).
- Suite verde: 279 testes / 962 asserções (inclui `InstitucionalTest`).
- Nada a commitar/push — o fix vive no servidor (passo 2–5).

---

## 5. Estado final (confirmado pelo cliente — 2026-11-15, noite)

O cliente confirmou: **«estava em route(r), não atualizei, agora sobi e tudo está em conformidade»** — a correção foi completar o upload FTP da release (com o `routes/web.php` atualizado) + limpar a cache de rotas no servidor + Ctrl+F5. O site voltou a responder normalmente. Smoke tests finais e confirmação do bloco `MAIL_GERAL_*` no `.env` do servidor ficaram a cargo do cliente.

**Lição registada (regra permanente):** nunca subir `routes/web.php`/recursos da release sem limpar as caches compiladas no servidor (`bootstrap/cache/routes-*.php`, `config.php`, `storage/framework/views/*.php`) — o site inteiro cai 500 mesmo com o código local 100% correto e suite verde. Ver `doc/memoria.md` §5.3 e §8 (item 7).
