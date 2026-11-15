# PIGEA — Orientação de Deploy (otimizado a 2026-11-15)

Guia **profissional e manual** para colocar em produção toda a implementação concluída desde 14/11/2026 · 14:54 (patch de categorias incl. **Petróleo e Gás**) até agora (inclui a release `updates` ponta: `b1d69a2` merge + `18b9c82` feat: Equipa/Parceiros + Sobre Nós editável). **Deploy manual via FTP + cPanel (phpMyAdmin + File Manager), sem Git no servidor e sem `php artisan migrate` na produção** — toleramos `storage:link` / `optimize:clear` apenas.

> Este ficheiro fica na máquina do cliente (pasta `doc/`); **não faz parte do upload** do servidor.

---

## 1. O que esta release entrega (inventário completo `"**14:54 → agora**"`)

| Bloco | Conteúdo | Ficheiros-chave |
|---|---|---|
| **Pacote 15:00+** (`3248b8b`, em `b1d69a2`) | Home: «Últimas Notícias» renderizado no servidor (sem abas AJAX Mineração/Água/Ambiente/Investigação); Newsletter com templates HTML (subscrição/confirmação/cancelamento) e estados `pending/active/cancelled`; novos `Mailable`s; Diretório com emails transacionais (`registo-recebido/aprovado/não-aprovado`) via mailer `geral` + campo `email` obrigatório; `prod-patch-2026-11-16.sql` (29 migrações); `prod-categorias-news.sql` (Petróleo e Gás) | `home.blade.php`, `FragmentController` (apagado), `NewsletterController`, `RegistoEmails`, `config/mail.php`, `database/migrations/2026_11_*` |
| **Release `18b9c82`** | Módulo institucional — páginas públicas **/equipa** e **/parceiros**, gestão admin completa; **Sobre Nós** com pilares Missão/Visão/Valores alinhados + faixa «Explorar»; textos da página Sobre editáveis nas Definições do painel (Missão, Visão, Valores, vídeo, overlay); **«Escolha da Equipa» (título do widget publicitário) eliminado**; subcategoria redundante «Missão, visão e valores» do mega menu removida (apontava para a mesma página de «Quem somos») | `equipa.blade.php`, `parceiros.blade.php`, `TeamController`, `PartnerController`, `Institucional.php`, `team_members` + `partners` + `missao-visao-e-valores` (migrações `2026_11_18` + `2026_11_19`), `admin.configuracoes.sobre`, `components/destaque.blade.php` |

### O que NÃO muda nesta versão
- Esquema dos posts/artigos (só adições sempre); nada apaga `comments_count` em uso (verificado pelo cliente: a coluna está vazia; o patch remove-a porque o módulo nunca existiu).
- Newsletter/subscrições: nada é destruído; a migração de estados apenas **expande, converte e reduz** o ENUM (safe para strict mode).
- `.env` da produção **não vai no pacote** (nunca). Só se **acrescenta** as variáveis `MAIL_GERAL_*` (ver Secção 4).

---

## 2. Pré-requisitos antes de público

1. **cPanel → Conta de email `geral@pigea.ao`** existe; credenciais conhecidas.
2. **DB backup completo** (phpMyAdmin → Exportar → Base de dados, SQL). Guardar o `.sql` fora do servidor.
3. **zip do estado atual do site** em File Manager (ex.: `backup-pigea-2026-11-15.tar.gz`) dentro de `/` (raiz da app) ou numa pasta externa; **não apagar depois do deploy**.
4. Acesso FTP/SFTP a `pigea.ao` (Documento Root = `……/pigea-deploy/public` ou equivalente; confirma no `Path` do domínio).
5. Acesso a cPanel → **phpMyAdmin** do mesmo host; acesso a **SSH/Terminal** no host é o ideal para `optimize:clear` e `storage:link` (se não houver SSH, ver Secção 6).

---

## 3. Ordem completa (num relance)

1. Backup DB → `.sql`
2. **Backup completo da BD** (phpMyAdmin → Exportar → SQL) — única garantia de reversão do patch
3. **Secção 2** toda — 29 migrações (inclui remover `comments_count` — inofensivo, coluna vazia)
4. **Verificação final** → espera **"29+"**
5. **`prod-categorias-news.sql`** — só se o mega menu de produção ainda não tiver as categorias completas (incl. Petróleo e Gás + Academia/Oportunidades); confirma no phpMyAdmin com o `SELECT` da Secção 7
6. **Upload FTP** do pacote de código (Secção 5), preservando `.env`, `storage/`, `vendor/`
7. **Alterar `.env` de produção**: acrescentar apenas o bloco `MAIL_GERAL_*` (Secção 4)
8. **`php artisan optimize:clear`** (ou apagar cache compilada manualmente, Secção 6)
9. **`php artisan storage:link`** (executada uma vez; não falhará com "already exists")
10. **Testes de fumo** (Secção 8) → `https://pigea.ao` a funcionar igual a antes das 15h, mas com os módulos novos

---

## 4. `.env` da produção — o que acrescentar e NADA mais

Confirmar que já existem as linhas `MAIL_MAILER=smtp, MAIL_HOST=mail.pigea.ao, MAIL_PORT=465, MAIL_SCHEME=smtps, MAIL_USERNAME=newsletter@pigea.ao, MAIL_FROM_ADDRESS=newsletter@pigea.ao` (a newsletter continua a ser a remetente da newsletter — **intocada**). Acrescentar bloco (não tocar no que há):

```env
# Transacional (registo de empresa/profissional + contacto do site)
# — correio separado, remetente diferente do da newsletter.
MAIL_GERAL_TRANSPORT=smtp
MAIL_GERAL_HOST=mail.pigea.ao
MAIL_GERAL_PORT=465
MAIL_GERAL_SCHEME=smtps
MAIL_GERAL_USERNAME=geral@pigea.ao
MAIL_GERAL_PASSWORD=<password da caixa geral@pigea.ao>
MAIL_GERAL_FROM_ADDRESS=geral@pigea.ao
MAIL_GERAL_FROM_NAME=PIGEA
```

Regras duras (aprendidas a 14/11 às 22:21):
- `MAIL_GERAL_USERNAME` **tem de ser idêntico** a `MAIL_GERAL_FROM_ADDRESS` (o relay recusa remetente não autorizado).
- Laravel 12 **ignora** `MAIL_ENCRYPTION`; a chave certa é `MAIL_GERAL_SCHEME` (`smtps` em 465 / `smtp` em 587).
- Nunca empurrar `.env` ao FTP nem ao Git.

Se em dev os emails forem todos escritos para `storage/logs/laravel.log` (NORMAL): confirmar que `MAIL_GERAL_TRANSPORT=log` está lá e **nunca** em produção.

---

## 5. Upload FTP — o que levar e o que apagar do servidor

### 5.1 O que **LEVAR** (todo o código actualizado, na prática o que mudou; ver a lista do 5.2)
**Aplica tudo**: `app/`, `bootstrap/`, `config/`, `database/` (incluindo `database/migrations` inteiro), `public/css/`, `public/js/`, `public/images/` (inalterado), `public/fonts/` (inalterado), `resources/`, `routes/web.php`.

### 5.2 Ficheiros **MUDARAM** (bloco relevante — sem precisar inventar)
<details><summary>Lista completa de alterações nesta versão (cópia técnica)</summary>

```
M  app/Http/Controllers/Admin/NewsletterAdminController.php
M  app/Http/Controllers/Admin/SettingsController.php
D  app/Http/Controllers/Frontend/FragmentController.php   ← APAGAR no servidor
M  app/Http/Controllers/Frontend/NewsletterController.php
M  app/Support/RegistoEmails.php              (novo)
M  app/Livewire/Admin/CompaniesTable.php
M  app/Livewire/Admin/ProfessionalsTable.php
M  app/Livewire/Frontend/ContactForm.php
M  app/Livewire/Frontend/RegistoEmpresa.php
M  app/Livewire/Frontend/RegistoProfissional.php
M  app/Mail/ContactMessage.php
D  app/Mail/NewsletterConfirmation.php                     ← APAGAR no servidor
M  app/Models/Company.php
M  app/Models/NewsletterSubscriber.php
M  app/Models/Professional.php
M  app/Support/MenuCategorias.php
M  app/Support/Institucional.php                          (novo)
M  app/Providers/AppServiceProvider.php
M  config/mail.php
M  database/migrations/2026_11_16_000000_newsletter_simplificar_statuses.php
M  database/migrations/2026_11_17_000000_add_email_to_companies_professionals.php
M  database/migrations/2026_11_18_000000_create_team_members_and_partners_tables.php   (novo)
M  public/js/scripts.js
M  public/css/style.css
M  resources/views/about.blade.php
M  resources/views/admin/configuracoes.blade.php
M  resources/views/admin/newsletter.blade.php
M  resources/views/components/sidebar-admin.blade.php
M  resources/views/home.blade.php
M  resources/views/fragments/news.blade.php                            (apagado → ver 5.3)
M  resources/views/emails/registo/confirmacao.blade.php                (novo)
M  resources/views/emails/registo/aprovado.blade.php                   (novo)
M  resources/views/emails/registo/rejeitado.blade.php                  (novo)
M  resources/views/emails/newsletter/                                   (3 templates novos)
M  resources/views/livewire/frontend/registo-empresa.blade.php
M  resources/views/livewire/frontend/registo-profissional.blade.php
M  resources/views/equipa.blade.php                                     (novo)
M  resources/views/parceiros.blade.php                                  (novo)
M  routes/web.php
M  tests/Feature/*.php (10 ficheiros)
```

(Também se sobe para a raiz `prod-patch-2026-11-16.sql` apenas para depois de o aplicares em phpMyAdmin — **nunca dentro de public/**; pode ficar na raiz do projeto, a qual tem de estar fora do DocumentoRoot.)

### 5.3 Ficheiros para **APAGAR** no servidor (por FTP)
```
app/Http/Controllers/Frontend/FragmentController.php
app/Mail/NewsletterConfirmation.php
resources/views/fragments/news.blade.php
resources/views/emails/newsletter-confirmacao.blade.php
```
Se algo não existir no servidor, ignora (era arquivo inexistente — não acontecer).

### 5.4 NUNCA subir/apagar
`.env`, `.env.production`, `storage/`, `vendor/` (nesta release o vendor não muda; só com SSH e `composer install --no-dev` quando houver mudança de dependências), `node_modules/`, `public/public.zip`, `storage/run-hidden.vbs`, `prod-categorias-news.sql` e `prod-patch-2026-11-16.sql` (ficheiros de trabalho — nunca dentro de `public/`; ficam na raiz do projeto, que tem de estar fora do DocumentoRoot).

---

## 6. Cache e symlink sem SSH (ou com)

**Com SSH/Terminal (cPanel para terminal cloud):**
```bash
cd /home/<user>/pigea-deploy   # a raiz exacta do site
php artisan optimize:clear
php artisan storage:link       # aceita "already exists"; ignorar o erro
```

**Sem SSH** (fallback, cPanel → File Manager):
- `storage/framework/views/` → apagar os ficheiros compilados (Laravel volta a compilar no primeiro request).
- `storage/framework/cache/data/` → apagar apenas os ficheiros.
- Hard refresh **Ctrl+F5** no browser.

**Nunca** rodar `php artisan migrate` na produção neste deploy — as 29 migrações estão no patch. **Nunca** `migrate:rollback`. O seeder permitido (opcional, se faltar algo no mega menu) é apenas `MenuSeeder`: `php artisan db:seed --class=MenuSeeder`. **Nunca** `DatabaseSeeder`/`ContentSeeder`/`DirectorySeeder`.

---

## 7. Como aplicar o patch no phpMyAdmin — ordem

1. **CPanel → phpMyAdmin → `pigea` → Importar** ou **SQL** (colar).
2. **Sempre** com o dump fresco feito **mesmo antes** (export completo no phpMyAdmin ou mysqldump).
3. **Nota — Secção 1** é agora apenas diagnóstica (o ficheiro imprime um aviso informativo). A Secção 2 está protegida: a criação do índice `companies_nif_unique` é salada via information_schema — não rebenta se ele já existir. Opcionalmente, para veres NIFs duplicados existentes:
   ```sql
   SELECT COUNT(*) AS tabela_existe FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'companies';
   SELECT nif, COUNT(*) c FROM companies GROUP BY nif HAVING c > 1;
   ```
4. **Secção 2 completa** (colar/importar a totalidade de `prod-patch-2026-11-16.sql`).
5. Script de **verificação final**: `migracoes_marcadas_como_executadas` tem de devolver **≥ 29**.
6. **`prod-categorias-news.sql`** — só se o mega menu de produção ainda não tiver as categorias completas (incl. Petróleo e Gás e os grupos Academia/Oportunidades). Isto confirma-se correndo, no phpMyAdmin:

```sql
SELECT menu_group, COUNT(*) FROM categories
WHERE menu_group IS NOT NULL
GROUP BY menu_group;
```

Se a resposta listar as 9 secções (`noticias, mineracao, petroleo-e-gas, geociencias, academia, oportunidades, directorio, sobre…`), salta este passo. Caso contrário, cola `prod-categorias-news.sql` na janela SQL.
7. Com SSH disponível, o ÚNICO seeder permitido (opcional, se o passo 6 já estiver satisfeito) é: `php artisan db:seed --class=MenuSeeder`.

---

## 8. Testes pós-deploy («smoke tests») — nesta ordem

1. `https://pigea.ao/` → 200 (home ok; slides, Últimas Notícias com 5+ cards sem as abas Mineração/Água/Ambiente/Investigação).
2. `https://pigea.ao/entrar-7x2` → 200 (ou o PATH configurado em `PIGEA_LOGIN_PATH` no `.env`), nunca `/login` (404).
3. `https://pigea.ao/` no browser limpando cache: favicon, **Logo webp**, **Mega Menu** com Petróleo e Gás no menu; **Institucional** nas listas dos menus (Equipa/Parceiros/Contactos: todos 200).
4. `https://pigea.ao/sobre` → 200, textos **renomeados** (editáveis no painel); `imagem` com «Equipa PIGEA».
5. `https://pigea.ao/equipa` → 200, **nem uma secção vazia**; membro interno com cargo correto.
6. `https://pigea.ao/parceiros` → 200, **Empresas** e **Profissionais** com a mesma linguagem.
7. `https://pigea.ao/registo-empresa` → 200 + email obrigatório no formulário; submeter com `email@exemplo.ao` → confirmar **«Registo Recebido!»** chega a `geral@pigea.ao` (não venda-se: o membro não recebe email — o admin sim), FROM=PIGEA <geral@pigea.ao>.
8. Aprovar a mesma empresa no painel `/gestao-pigea/empresas` → **«Registo Aprovado!»** chega na caixa de email do candidato, FROM=PIGEA <geral@pigea.ao>.
9. `https://pigea.ao/contactos` → submeter mensagem → chega a `geral@pigea.ao`, reply-to = visitante.
10. Newsletter: confirmar que o email «Falta só um passo!» ainda sai com `MAIL_FROM_ADDRESS=newsletter@pigea.ao` **intocada** desde 14/11.
11. Dashboard: `https://pigea.ao/gestao-pigea/` → avisos/painel (sininho) sem erros; **/gestao-pigea/equipa/parceiros** ok.
12. Se alguma página mostrar 500: `storage/logs/laravel.log` para a linha exacta; **não tocar em nada antes de ler o erro**; o rollback seguro é restaurar o dump SQL pré-deploy.
13. Se o browser ainda mostrar o site antigo: **Ctrl+F5** (ou abrir com `?cache=1`).

---

## 9. Plano B (rollback) — se o deploy partir o site

**Opção A — DB (preferida):** phpMyAdmin → importar o `.sql` do backup feito antes de aplicar o patch. Isto repõe o estado anterior completo (esquema + dados).
**Opção B — Ficheiros:** extrair `backup-pigea-2026-11-15.tar.gz` no servidor (Secção 2), depois `.env` actual e `storage/app/public` são recuperados a partir do backup.

**Regra mínima:** se não tens o `.sql` do dump pré-deploy, **não toques em nada** antes de contratar alguém que o tenha — que o site é o activo da plataforma (postagens, assinantes, registos e log de produção), não pode ser perdido.

---

## 10. Dúvidas frequentes (respostas honestas)

**«Posso rodar `migrate` na produção?»** — Não correr neste deploy. Se tudo ficar pronto resta apenas o acesso `Route: optimize:clear/storage:link`; migrações estão no patch. **Nunca `rollback`**.
**«`comments_count` foi removido — perdi dados?»** — A coluna foi vazia no seu site (confirmado). Nenhum post foi perdido/alterado.
**«Pedro submeteu uma empresa antes do deploy — onde está a submissão dele?»** — Está na tabela `companies` como `pendente` (nada se perde no deploy; a tabela continua intacta).
**«Os emails param de chegar?»** — Se `MAIL_GERAL_*` for omitida, o envio transacional/contact falha silencioso (fica `storage/logs/laravel.log`). A newsletter não depende dela.
**«O mega-menu dos sublinks apareceu vazio»** — Rode `SELECT slug, menu_group FROM categories WHERE menu_group IS NOT NULL ORDER BY menu_group, order;` no phpMyAdmin; se faltar, `php artisan db:seed --class=MenuSeeder` (único seeder permitido).
**«Já apliquei o patch duas vezes — deu erro»** — O script é idempotente; o erro esperado é «DUPLICATE KEY …» ignorável. Se cair num ponto inesperado, restaura o dump pré-deploy e reporta.
**«Overwriting/FTP tímido… posso confiar?»** — Mantém os 6-8 ficheiros em `storage/app/public` que o site serve (imagens, PDFs). Nada mais merece proteção especial.
**«Mesmo com o browser limpo, ainda vejo a home antiga»** — Hard refresh: **Ctrl+F5**; os ficheiros compilados em `storage/framework/views/*.php` têm de ser apagados (Secção 6).

---

> **Documento vivo.** Em qualquer deploy futuro: atualize a data do dump e a Secção 3; mantenha a `.env` dos segredos **nunca** aqui; repita este protocolo como estado inicial de cada deploy.
>
> Autor: QNBAngola — Jorge Dange (orientação escrita 2026-11-15). Ver também: `prod-categorias-news.sql`, `prod-patch-2026-11-16.sql`, `doc/newsletter.md`, `doc/deploy.md`.
