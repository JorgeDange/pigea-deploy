# 🧠 Memória do Projeto — PIGEA

**Projeto:** PIGEA
**Localização:** `C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\`
**Última atualização:** 15/11/2026 (noite — inclui a **release «Produção 2026-11-16»**, o deploy, o **incidente/correção do route cache** em produção, a **reparação da árvore de categorias de notícias** (news=37/menu=16) e a **análise dos 3 uploads de imagem** no editor de notícias)
**Propósito:** Manter contexto de tudo o que foi analisado, instalado, decidido e planeado para não perder rastro do trabalho.

> Estado no fecho desta atualização (16/11/2026, noite): **branch `updates` e `main` sincronizadas**; suíte local **295 testes / 1015 assertions verdes**; **produção restaurada, validada pelo cliente e Fase B FECHADA** (columna `must_change_password` confirmada `col=1` no phpMyAdmin; permissões 2/3 verificadas). Tudo em produção e verde.

---

## 🎯 DIREÇÃO ATUAL DO PROJETO (RESUMO 15/11/2026)

O PIGEA é um portal de notícias + directório (Empresas/Profissionais/Documentos) com painel interno (Spatie roles `admin` e `editor`). Não há registo de empresas/profissionais para uso público — submetem formulários públicos e aguardam aprovação.

**Estado por fases:**
| Fase | Estado |
|---|---|
| 0–10 (restruturação: limpeza, base de dados, formulários, CMS, frontend, publicidade, testes) | ✅ Concluídas 10–11/11/2026 |
| 10.1/10.2 (editor Blade tradicional, CMS profissional, a11y) | ✅ 11/11/2026 |
| 11 A (editor de upload/capacete/preview/URL) | ✅ 11/11/2026 |
| 12/13 (agendamento, OCR/auto-save) | ✅ 11/11/2026 |
| S (ordenamento, AJAX tabs, pin editorial, hero, destaques, banners Largo) | ✅ 13/11/2026 |
| A/B/C/E1–E6 (melhorias painel, visitas, anti-bot, províncias, rodapé, Acompanhamento) | ✅ 13/11/2026 |
| Administração de vendas (formato editor, uploads Livewire) | ✅ 12/11/2026 |
| **Fase 11B/16 (upload com progresso, modais próprios)** | ✅ 12/11/2026 |
| **Deploy manual + .env produção** | ✅ Documentado em `deploy.md`; produção em `https://pigea.ao` (raiz, não prefixo `/laravel`) |
| **Docs/engenharia** | ✅ `docs.md` (catálogo), `deploy.md`, `dimensao.md`, `email.md`, `otimizacao.md` |
| **BOM no admin.css** (regra universal `border-box` descartada pelo browser) | ✅ Resolvido 14/11/2026 (`b9d7601`) |
| **Performance/CLS** (otimização) | ✅ 15/11/2026 (`90c1f96`; §4 async-CSS revertida por FOUC) |
| **Autosave (trava, bónus por fechada sem guardar)** | ✅ 15/11/2026 (`b9dee36`, Opção B) |
| **Email (gatilho SMTP de produção + email HTML do contacto)** | ✅ 14–15/11/2026 (`d5985d7`) |
| **Newsletter simplificada** | ✅ 15/11/2026 — 3 templates HTML (subscrição/confirmação/cancelamento); estados `pending`/`active`/`cancelled` (migrados: `confirmed`→`active`, `unsubscribed`→`cancelled`, ENUM widened→converter→estreitar); mailables `NewsletterConfirmacao`/`NewsletterCancelamento`; rota `/newsletter/resubscrever/{token}`; migração `2026_11_16_000000_newsletter_simplificar_statuses` |
| **Home — "Últimas Notícias" server-rendered** | ✅ 15/11/2026 — abas AJAX (Mineração/Água/Ambiente/Investigação) **removidas**; lista das 5 mais recentes por `published_at` (reusa `$listNews`, view direto no `home.blade.php`) |
| **Widget publicitário sem título** | ✅ 15/11/2026 — "Escolha da Equipa" apagada de `components/destaque.blade.php`; textos admin/docs/tests ajustados |
| **Mega menu — "Missão, visão e valores" removida** | ✅ 15/11/2026 — saiu do grupo sobre (redundante com /sobre); migração `2026_11_19_000000_remove_missao_menu_item` remove a subcategoria e renumera o grupo 1–4 (**sobre = 4 itens**, 43 items no total no teste MegaMenuTest); `MenuCategorias::DESTINOS` limpo |
| **Institucional — Equipa + Parceiros** | ✅ 15/11/2026 (`18b9c82`) — páginas públicas `/equipa` e `/parceiros`; CRUD Livewire+FormRequest admin completo (Cargo select "Outros" + input `cargo_outro` obrigatório); sidebar admin institucional + rotas `admin.equipa*`/`admin.parceiros*`; `TeamMember`/`Partner` models; `App\Support\Institucional` |
| **Sobre Nós editável** | ✅ 15/11/2026 — painel definido em Admin → Configurações (subtitle "Sobre Nós"): Missão, Visão, Valores, vídeo institucional, overlay; defaults aplicados; **suite 279/962 verde** |
| **Diretório — emails transacionais** | ✅ 15/11/2026 — mailables `RegistoConfirmacao`/`RegistoAprovado`/`RegistoRejeitado` (templates `emails/registo/*`) via novo mailer **`geral`** (host `mail.pigea.ao:465 smtps`, remetente `geral@pigea.ao`) **separado** do mailer da newsletter (`newsletter@pigea.ao`, **intocado**); `replyTo` do contacto = visitante; campo `email` **obrigatório** no formulário de registo; migração `2026_11_17_000000_add_email_to_companies_professionals` |
| **`config/mail.php`** | ✅ 15/11/2026 — nova chave `mailers.geral` + `geral_from` (endereço/nome); nada do bloco `mail` atual alterado |
| **Deploy manual FTP — release 2026-11-16** | ✅ 15/11/2026 — backup pré-deploy; patch **`prod-patch-2026-11-16.sql` aplicado no phpMyAdmin do servidor** (Secção 1 diagnóstica; índice `companies_nif_unique` protegido via information_schema; **migrações marcadas como executadas = 29**); upload FTP; ~~`prod-categorias-news.sql` fica opcional~~ → **aplicado de facto e verificado** (news=37/menu=16) porque o select do editor mostrava «poucas»; ver linha abaixo |
| **INCIDENTE de produção 15/11 20:59 — RESOLVIDO** | ✅ 15/11/2026 — "Route [equipa] not defined. (View: …/components/main-menu.blade.php)" em todas as páginas públicas. **Causa:** o `routes/web.php` no servidor estava desatualizado / a route cache (`bootstrap/cache/routes-v7.php`/`config.php`) sombreava as rotas novas (a home renderiza `home.blade.php` → componente `main-menu` → `MenuCategorias::grupos()` → `DESTINOS['sobre']['equipa']` → `route('equipa')` → exceção). **Correção:** completar o deploy FTP da release (`routes/web.php` + código novo) + apagar `bootstrap/cache/routes-v7.php`/`config.php` (ou `php artisan optimize:clear` com SSH) + limpar `storage/framework/views/*.php` + Ctrl+F5. Detalhe completo em `doc/fix-equipa-route-2026-11-15.md`. **Resultado: site volta 200; smoke tests do cliente em curso.** |
| **Categorias de notícias — produção REPARADA** | ✅ 15/11/2026 (noite) — o select «Categoria» no «Criar Notícia» mostrava «poucas»: produção tinha **news=30** (vs local 37) e **menu=16**. **Causa:** na produção os 7 topos de news (`Água`, `Ambiente`, `Internacional`, `Investigação`, `Mineração`, `Petróleo e Gás`, `Geociências`) **não existiam** como `type='news'`; o `prod-categorias-news.sql` usava `WHERE NOT EXISTS (slug=…)` **sem filtrar o tipo** (a tabela tem `UNIQUE(type,slug)`) → os 7 topos não foram criados e os 17 filhos de Mineração/Petróleo e Gás/Geociências ficaram órfãos (`parent_id NULL`, `menu_group` correto). Academia/Oportunidades escaparam porque o patch `2026_11_11_220000` os promoveu. **Correção (phpMyAdmin, executada e verificada):** 7 INSERTs de topos + 3 UPDATEs (religar 17 órfãos, `Affected rows 5/5/7`) → **news=37, menu=16** ✔ |
| **Uploads de imagem no «Criar Notícia» — análise dos 3 campos** | ⚠️ Analisado 15/11/2026 (decisão do cliente pendente) — **Foto do autor convidado** e **Imagem de capa** aceitam ficheiro OU link (URL guardado direto nas colunas `guest_author_avatar` / `news.image`). **«Imagens de contexto» não aceita link:** vai **sempre** para a coleção MediaLibrary `contexto` com conversão WebP `nonQueued` (validação só de ficheiro `image/mimes/max:4096` em `NewsEditorController@validar`, loop `processarMidia` com `$request->file('contexto')`); não existe campo `contexto_link` nem validação de URL (nunca implementado). **Nota extra:** a coleção `contexto` **não é renderizada em nenhuma página pública hoje** (código morto para o front). Pedido do cliente: aceitar link também aqui — opções: **(a)** `addMediaFromUrl()` (baixa + converte; save mais lento, falível); **(b)** guardar URLs (coluna JSON `contexto_links`) e `<img src>` direto (rápido, sem WebP) |

**Produção atual**: site LIVE em `https://pigea.ao/` — **restaurada em 15/11/2026 à noite** após o incidente do route cache (ver tabela acima). Domínio de produção é `pigea.ao` (raiz). `pigea.qnbangola.ao` deixou de ser alvo. O site usa mesmo servidor de Mail (mesma máquina cPanel, `mail.pigea.ao:465 smtps`). O `.env` de produção mantém o bloco `MAIL_*` da newsletter **intocado** e acrescenta apenas `MAIL_GERAL_*` (transacional: `geral@pigea.ao`); `MAIL_GERAL_PASSWORD` é a password real da caixa geral (nunca comitada). **Pendente de confirmação pelo cliente:** `MAIL_GERAL_*` efetivamente presente no `.env` do servidor + smoke tests pós-recuperação.

---

## 1. O Que É o PIGEA

Aplicação web construída com **Laravel 12**, **Livewire 3**, **MariaDB 10.4+** (FULLTEXT no produto — SQLite só não suporta FULLTEXT no dev), **Bootstrap-like Gmag theme (legado) + admin.css único**, TinyMCE self-hosted com sanitização HTMLPurifier, SEO Jinja/`Setting::todas()` de dados SEO/menu, agendamento `news:publish-scheduled`, anti-bot nos contadores de visitas, dupla confirmação (opt-in 2 passos) na newsletter.

Áreas:
- Notícias (mineração, água, ambiente, investigação, internacional + petroleo e geociências), projectos (ocultos do público por decisão do cliente), eventos, legislação, entrevistas, formação, fornecedores, pesquisa, directório de empresas/profissionais/documentos
- Painel admin (`ADMIN_PATH`): notícias, vídeos, empresas, profissionais, documentos, destaque de artigos, publicidade (banners Lateral/Largo/Topo/Rodapé), newsletter, notificações live, acompanhamento (sala de controlo), configurações, utilizadores
- Autenticação Fortify (2FA + passkeys + rate-limiting), login escondido em `PIGEA_LOGIN_PATH` (`/login` responde 404)

---

## 2. Análise Inicial (28/10/2026)

### Estado encontrado
- Código-fonte completo do Laravel já presente
- `composer.lock` e `package.json` existentes
- Node.js `v24.22.0` instalado no Windows
- **Nada instalado:** sem PHP, sem Composer, sem `vendor/`, sem `node_modules/`, sem `.env`, sem `database.sqlite`

### Arquivos de configuração do projeto
| Arquivo | Conteúdo principal |
|---------|-------------------|
| `composer.json` | Laravel 12, Fortify, Livewire 3, Spatie Permissions, Scout, etc. |
| `package.json` | Vite 7, Tailwind 4, Axios, Concurrently |
| `vite.config.js` | Configuração do build frontend |

---

## 3. Instalação Realizada (Concluída)

### Ambiente do sistema
| Ferramenta | Versão | Caminho | Status |
|------------|--------|---------|--------|
| PHP | 8.2.12 | `C:\xampp\php\php.exe` | ✅ Instalado |
| Composer | 2.10.3 | `pigea-deploy/composer` (local) | ✅ Instalado |
| Node.js | v24.22.0 | Sistema | ✅ Instalado |

> **Nota:** PHP não está no PATH. Usar caminho absoluto.

### Dependências instaladas
| Tipo | Quantidade | Comando |
|------|------------|---------|
| PHP | 160 pacotes | `C:\xampp\php\php.exe composer install` |
| JS | 87 pacotes | `cmd /c "npm install"` |

### Build frontend
- `npm run build` executado (primeiro build do projeto)
- Assets em `public/build/`

### Configuração Laravel (10/11/2026)
- `.env` criado a partir de `.env.example`
- `APP_KEY` gerada
- 21 migrations originais (SQLite); depois, MySQL XAMPP (dev passou para MySQL 8.x; testes em `pigea_test`)

### Seeders executados
- `RoleSeeder`, `ContentSeeder`, `PainelSeeder` (e mais tarde `MenuSeeder`)

---

## 4. Documentos Criados (lista mestre a 15/11/2026)

| Arquivo | Conteúdo |
|---------|----------|
| `doc/restrutura.md` | (Fornecido pelo cliente) instruções da reestruturação |
| `doc/fase_all.md` | Plano-mestre Fases 0–10 |
| `doc/orienta_deploy.md` | Manual de deploy da release 2026-11-16 (FTP, patch SQL no phpMyAdmin, `.env` MAIL_GERAL_*, apagar 4 ficheiros obsoletos, smoke tests §8) |
| `prop.md` (raiz do workspace) | **Proposta — Gestão e Perfil de Utilizadores** (cliente, 2026-11-15, Rev 1.1 16/11): inventário do painel de utilizadores/perfil, falhas encontradas («Bloquear» cosmético, sem auditoria, sem senha no perfil, paginação 6), modelo de permissões confirmado (editor = auto-serviço senha/nome/foto; admin = gestão total) e 3 fases de correção (A perfil / B utilizadores a sério / C painel de utilização) com estimativas |
| `doc/fix-equipa-route-2026-11-15.md` | Post-mortem do erro «Route [equipa] not defined» em produção (diagnóstico, correção passo a passo, prevenção de recorrência) |
| `prod-patch-2026-11-16.sql` | Patch SQL idempotente para produção (29 migrações; Secção 1 diagnóstica) — na raiz do projeto, **nunca** em `public/` |
| `prod-categorias-news.sql` | Patch SQL idempotente para repor a árvore news na produção (10 topos + 27 filhos; guard `type='news' AND slug=…` desde a v5.6) — na raiz do projeto, **nunca** em `public/` |
| `doc/modificar.md` | Implementação e correções detalhadas de cada fase |
| `doc/pub.md` | Módulo de publicidade e tabela banners |
| `doc/fases_e.md` | Estado e decisões das fases E1–E6 + N |
| `doc/newsletter.md` | Módulo de newsletter (opt-in 2 passos, painel de gestão) |
| `doc/notificacoes.md` | Módulo de notificações (bell, tabela notifications) |
| `doc/fase_agora.md` | Estado técnico completo (stack, dados, deploy, fase atual) |
| `doc/verificar_1.md` | Prompt do cliente para auditoria final e fecho de funcionalidades |
| `deploy.md` | Guia de deploy manual FTP/cPanel + auditoria pré-deploy |
| `docs.md` | Catálogo técnico interno (arquitetura + decisões de negócio) |
| `dimensao.md` | Especificações de imagem para o designer (capas, banners, logo, avatar) |
| `email.md` | Histórico completo do problema SMTP de produção e solução |
| `otimizacao.md` | Otimização PageSpeed (CLS, lazy, async-CSS revertido) |
| `memoria.md` | Este documento |

---

## 5. Decisões Tomadas (principais)

1. **Stack:** Laravel 12 + Livewire 3 + MariaDB (FULLTEXT no produto).
2. **Admin:** papéis Spatie `admin` e `editor` apenas. Painel em `ADMIN_PATH` (`gestao-pigea` no dev; `painel-gestao-7f3a` no `.env` do servidor).
3. **Login:** `/login` devolve 404; `PIGEA_LOGIN_PATH` opaco.
4. **Ambiente:** dev MySQL XAMPP; testes contra `pigea_test`; `phpunit.xml` força `PROJECTS_PUBLICO=true`.
5. **Newsletter:** module com opt-in 2 passos; painel de gestão com exportar CSV por estado; `pigea_newsletter_ok` em localStorage.
6. **Projetos no público:** desligados desde 2026-11-13 (`PROJECTS_PUBLICO=false`); `/projetos` responde 404; decisão reversível.
7. **URL de produção final:** raiz de `pigea.ao` (sem `/laravel`).
8. **Email:** SPF presente no DNS; DKIM a confirmar no cPanel; SMTP `mail.pigea.ao:465 smtps` autentica com a conta `newsletter@pigea.ao`.
9. **BOM/admin.css:** a regra `*,*::before,*::after { box-sizing: border-box }` estava a ser descartada pelo browser porque o `admin.css` continha um BOM UTF-8 a meio do ficheiro — corrigido byte-a-byte.
10. **Autosave (Opção B):** o rascunho é gravado tal como está; o purificador só corre no caminho Publicar/Pré-visualizar. A decisão do cliente desativa a regra «purifica ao guardar» do `cms_v.md`.
11. **beforeunload nativo:** removido; a saída nunca lança aviso — o `pagehide` envia o rascunho via `navigator.sendBeacon`.
12. **Modal do site:** `window.PIGEAConfirm` substitui todos os `alert()/confirm()` nativos em ações Livewire e Blade.
13. **Redes sociais:** `urlAbsoluta()` aplicada no render e na gravação — links sempre externos e absoluos.
14. **Screenshots:** QA screenshots para o cliente (`qa-home-desktop-1440.png`, `qa-home-mobile-390.png`, etc.) guardados fora do repositório (`QNB\pigea\`).
15. **Pasta `storage/`:** apenas esqueleto no git; uploads reais vivem exclusivamente em `storage/app/public/` (ignorado).
16. **`otp:build`:** não aplicável — o projeto não usa Vite build no deploy (ver `deploy.md`).
17. **Mailer transacional separado da newsletter:** `MAIL_*` (`newsletter@pigea.ao`) intocado; todos os emails da loja Diretório/Contacto saem como `PIGEA <geral@pigea.ao>` via novo mailer `geral`. `replyTo` do contacto continua o visitante.
18. **Deploy nunca parcial:** release multi-ficheiro subida «timidamente» derruba o site inteiro (o `main-menu` é incluído por todas as páginas públicas e um `route()` em falta é 500 instantâneo). Regra nova: subir `app/`+`routes/`+`resources/`+`config/`+`database/` sempre em conjunto e, no fim, **apagar route cache** (`bootstrap/cache/routes-v7.php`/`config.php`) + views compiladas (ou `optimize:clear` por SSH) antes do Ctrl+F5.
19. **`MenuCategorias::grupos()` sem `Route::has()`:** propositadamente mantido — o mega menu confia no `routes/web.php` completo da release; a defesa em profundidade foi deferida (release testada 279/279 inalterada para não introduzir risco na véspera do deploy).

---

## 6. Estado Atual do Projeto — 15/11/2026

### Como executar (comandos atuais)

```cmd
:: Backend (Laravel)
cd pigea-deploy
C:\xampp\php\php.exe artisan serve

:: Testes
cd pigea-deploy
C:\xampp\php\php.exe artisan test

:: Suite completa
C:\xampp\php\php.exe artisan test --stop-on-failure

:: Status das migrações
C:\xampp\php\php.exe artisan migrate:status

:: Limpar caches
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan view:clear
C:\xampp\php\php.exe artisan cache:clear
```

### Acesso ao servidor
`http://127.0.0.1:8000/` (dev). Produção: `https://pigea.ao/` (alojamento cPanel).

**Deploy manual FTP/cPanel:** ver `deploy.md` — pacote via FTP, preservando `.env`, `storage/`, `vendor/`; sempre apagar `storage/framework/views/*.php` e `bootstrap/cache/routes-v7.php`/`config.php` (route cache sombreia rotas novas!) e hard-refresh (Ctrl+F5). **Release 2026-11-16 (15/11/2026):** patch `prod-patch-2026-11-16.sql` **aplicado** no servidor (29 migrações marcadas); ver `doc/orienta_deploy.md` (lista de upload + apagar) e `doc/fix-equipa-route-2026-11-15.md`.

---

## 7. O Que Faltou / Pendências (15/11/2026, noite)

| Item | Estado | Observação |
|------|--------|------------|
| **Contacto falha no envio («Não foi possível enviar a mensagem»)** | 🔧 Em análise — fix local aplicado | Causa provável (classe «cache de configuração desatualizada», como o route cache desta manhã): `bootstrap/cache/config.php` antigo sem o mailer `geral`, OU `MAIL_GERAL_PASSWORD` errada no `.env`. Diagnóstico exato agora possível: `ContactForm` passou a `report($e)` (antes engolia a exceção em silêncio — **zero log**). Passos no servidor: ① apagar `bootstrap/cache/config.php`/`routes-v7.php` (ou SSH `php artisan optimize:clear`) ② testar login webmail `geral@pigea.ao` ③ reenviar e, se falhar, ler `storage/logs/laravel.log` (`[contacto] falha ao enviar`) |

| Item | Estado | Observação |
|------|--------|------------|
| ~~Migrar migrações novas na produção~~ | ✅ Feito (15/11/2026) | `prod-patch-2026-11-16.sql` aplicado via phpMyAdmin; verificação `SELECT COUNT(*) FROM migrations WHERE migration LIKE '2026_11_%'` devolveu **29** |
| **Incidente «Route [equipa] not defined»** | ✅ Resolvido (15/11/2026) | Causa: route cache desatualizada / `routes/web.php` antigo no servidor. Fix: FTP completo da release + remoção de `bootstrap/cache/routes-v7.php`/`config.php` + Ctrl+F5. Ver `doc/fix-equipa-route-2026-11-15.md` |
| `php artisan storage:link` na produção | ❓ Por confirmar | Necessário para servir uploads; logs não indicam falha de symlink |
| `.env` de produção — bloco `MAIL_GERAL_*` | ✅ Em produção (mailer `geral` funciona — contacto/registos chegam a `geral@pigea.ao`) | Newsletter stays untouched; mailer transacional separado |
| Smoke tests pós-recuperação | ✅ Cliente valida: home ok, admin ok, emails ok, editor/login ok | `/`, `/sobre`, `/equipa`, `/parceiros`, `/contactos`, `/entrar-7x2` 200; `/login` 404 |
| **«Imagens de contexto» — link no 3º upload** | ⏳ Decisão do cliente pendente | Capa e foto de autor aceitam link; contexto só ficheiro (MediaLibrary). Opções: (a) `addMediaFromUrl()` (baixa e converte; lento, falível) (b) coluna JSON de URLs + `<img>` direto (rápido, sem WebP). Nota: a coleção `contexto` não é renderizada publicamente hoje |
| SMTP definitivamente funcional na produção | ✅ Funcional — SPF validado, **DKIM também (confirmado pelo cliente 16/11)** | Mailer transacional separado `geral` operacional; newsletter intocada |
| Migração de imagens para WebP na produção | Pendente de decision | Apenas news (capa) têm conversão WebP; imagens estáticas do tema até ~4 MB |
| Performance mobile PageSpeed 65 → alvo 80+ | Em progresso (otimização CLS medida 90/100/65 antes; pós-deploy re-medir) | §4 async-CSS revertida por FOUC (ver docs.md) |
| Ainda há comentários extensos em PHP/Blade/JS | ✅ Aplicativa para fases futuras | A migração para docs.md seria fase 2 — catálogo criado em 11/11/2026 (`docs.md`); migração completa não autorizada |
| CSP rigorosa | Pendente | Considerada na fase inicial; não aplicada em produção (compatibilidade com legado) |
| Deploy FTP automatizado | Pendente de decisão | Cliente prefere manual |

---

## 8. Próximos Passos (se/quando autorizados)

1. **Imediato (cliente, no servidor de produção — todos concluídos ou redundantes):**
   - ~~Confirmar no `.env` do servidor as linhas `MAIL_GERAL_*`~~ ✅ **Feito:** mailer `geral` operacional para contacto/registos (validado pelo cliente).
   - Smoke tests: ✅ o cliente validou `/`, admin, emails, editor.
   - ~~Se o mega menu vier incompleto~~ ✅ **Feito (15/11/2026):** `prod-categorias-news.sql` corrigido e a árvore verificada no servidor (news=37, menu=16) — os 7 topos e os 17 filhos industriais religados via phpMyAdmin.
   - ✅ **Coluna `must_change_password` confirmada em produção (16/11):** `col=1` — «Repor senha» operacional com troca obrigatória. **Fase B FECHADA.**
2. ~~DKIM~~ — **Confirmado atualizado pelo cliente (16/11).**
3. **Retomar password** do utilizador MySQL `qnbangolaco_pigea` e sobre a APP_KEY expostas no histórico inicial (recomendação anterior).
4. Verificar em produção que as imagens no hero continuam a funcionar pós-migração (imagens exclusivas à BD do cliente se algum artigo fora de fallback).
5. Reavaliar **PageSpeed** pós-deploy (imagens e async do hero).
6. (Pendente de decisão) Comprimir imagens estáticas do tema (banners, city-bg, bg/*.jpg) para WebP com redução de 60–75%.
7. **Regra permanente de deploy FTP:** nunca subir parcialmente `app/Support/` + `routes/` sem os respectivos controllers/views; no fim de cada release, apagar `bootstrap/cache/routes-v7.php`/`config.php` + `storage/framework/views/*.php` (ou `php artisan optimize:clear`) e fazer Ctrl+F5 + smoke test da home. Foi isto que custou o incidente de 15/11/2026 às 20:59.
8. **Regra permanente — `bootstrap/cache/` NUNCA sobe:** o git ignora-o (`.gitignore` aninhado), mas os zips de pasta inteira não. `packages.php`/`services.php` são caches do vendor local (com dev deps: pail/pint/sail) — no servidor são `--no-dev` e o boot parte. Admitidos na produção apenas como **cache derivada** (apagar e deixar regenerar). Apos cada upload: `php artisan tinker --execute='echo "ok";'` antes de testar o site. Foi isto que partiu o boot em 16/11 (PailServiceProvider not found).

---

## 9. Observações Importantes

- **NUNCA** commitar `.env` nem `.env.production` — sempre estão no `.gitignore`.
- O site de produção usa **raiz `https://pigea.ao`** (`APP_URL` actualizado em `.env.production`).
- **Deploy manual** via FTP/cPanel; nunca há `composer install` online sem ssh (podendo fazer `composer install --no-dev --optimize-autoloader` se o alojamento tiver SSH).
- `vendor/` e `node_modules/` nunca fora do git; `storage/` apenas o esqueleto.
- O botão 🡢«Guardar no servidor» de cada fase é o `/modulo/publicidade/imagem` ou os endpoints próprios; o admin distribui as permissões por `can:*`.
- Backups manuais pré-migração: `backups/pigea_*.sql` (pg_dump).
- Se a suíte falhar localmente, primeiro confirmar `pigea_test` existe e está migrada (`php artisan migrate --database=pigea_test --path=...` ou `migrate:fresh --seed` se necessário).
- A chave de local não está no git: `.env` com `APP_KEY=base64:...` (local, não versionado).

---

## 10. Controle de Versão desta Memória

| Versão | Data | Alteração |
|--------|------|-----------|
| 1.0 | 10/11/2026 | Criação inicial com todo o contexto do projeto até o momento |
| 1.1 | 10/11/2026 | Reorganização do menu |
| 1.2–1.4 | 10/11/2026 | Erros Livewire, restrutura.md, fase_all.md |
| 1.5 | 10/11/2026 | FASE 0 concluída |
| 1.6 | 10/11/2026 | FASE 1 concluída |
| 1.7 | 10/11/2026 | FASE 2 concluída |
| 1.8 | 10/11/2026 | FASE 3 concluída |
| 1.9 | 10/11/2026 | FASE 4 concluída |
| 2.0 | 10/11/2026 | FASE 5 concluída |
| 2.1 | 11/11/2026 | FASE 6 concluída; ajustes top-bar |
| 2.2 | 11/11/2026 | FASE 7 concluída — Backups, suíte 130/447 |
| 2.3 | 11/11/2026 | FASE 5.1 concluída + ajustes do cliente |
| 2.4 | 11/11/2026 | FASE 8 concluída — gate final PASS COM RESERVAS |
| 2.5 | 11/11/2026 | FASE 9 concluída — sistema de comentários removido |
| 2.6 | 11/11/2026 | FASE 10 concluída — CMS profissional; suite 159/572 |
| 2.7 | 11/11/2026 | Fase 10 revalidada pós-a11y |
| 2.8 | 11/11/2026 | FASE 10.2: bugs TinyMCE/Livewire identificados |
| 2.9 | 11/11/2026 | FASE 10.1 + cms_v concluídas; editor Blade tradicional |
| 2.10 | 11/11/2026 | Fase 11B: extras do editor (link genérico, capa URL); suite 185/643 |
| 2.11 | 12/11/2026 | FASE 16: admin Publicidade/Banner/Destaque refeitos; XHR assíncrono; suite 204/694 |
| 2.12 | 12/11/2026 | FASE 13: agendamento `news:publish-scheduled`; sidebar agrupada |
| 3.0 | 13/11/2026 | **FASE S completa** (commits `ed4cf09`, `a785326`, `aa16b2d`, `ffe2aa7`, `2e7db3e`): ordenamento `latest('published_at')` + AJAX tabs; painel admin único via `admin.css`
| 3.1 | 13/11/2026 | **FASE E3** — rodapé: logo 44px, copyright #1A1A1A + texto branco; 5 redes fixas (FB/IG/LI/YT/X + X só quando configurado) (`52bd4cf`, `17cf884`) |
| 3.2 | 13/11/2026 | **FASE E1** — 18 províncias oficiais + Nacional + Outros; componente `x-provincia-select`; `province` 120 chars (`0515b37`) |
| 3.3 | 13/11/2026 | **FASE E2** — `ContadorDeVisitas` anti-bot centralizado; »Top 5 mais lidas« (`9358f84`, `38d4c8f`) |
| 3.4 | 13/11/2026 | **FASE E4** — página Acompanhamento (`/gestao-pigea/acompanhamento`); banner «Tudo em ordem / N pontos a rever» (`931ca62`, `e3ecf93`, `949665d`) |
| 3.5 | 13/11/2026 | **FASE N1 Newsletter** (sub-opção B dentro do site): dupla confirmação, painel de gestão + CSV, exclusão de Projetos do público, modal de subscrição simplificado, imagem responsiva hero/fallback (`9a4a58b`, `04f909a`, `d6906bd`, `4f626b2`, `9a4a58b`, `949665d`) |
| 3.6 | 13/11/2026 | **FASE S + N:** notificações persistidas (sino com contagem real); `PIGEA_LOGIN_PATH` (`/login` → 404); banner #largo através de `<x-banner-publicitario posicao="largo" />`; **Projetos excluídos do público temporariamente**; solicitações de `/login` default `entrar-equipa` (dev) / `entrar-7x2` (`.env.production`, nunca comitado) (`a785326`, `aa16b2d`, `43e2e0d`) |
| 3.7 | 14/11/2026 | **PROBLEMA DO EMAIL DE PRODUÇÃO — RESOLVIDO:** SMTP `smtp.pigea.ao` (não existe) → `mail.qnbangola.ao` → `mail.pigea.ao:465 smtps`; `535` autenticação → **password com caracteres especiais resolvido via aspas simples no `.env` do servidor**; confirmações da Newsletter já chegam (`email.md` documenta o historial completo). SPF OK; DKIM a confirmar |
| 3.8 | 14/11/2026 | **Deploy:** `deploy.md` (FTP/cPanel completo; lista de upload; migrações 2026_11_13_*; `storage:link`; `pigea:criar-admin`); `.env.production` criado localmente (gitignored); `.env.example` limpo (APP_NAME=PIGEA, locale pt_PT, Africa/Luanda, PROJECTS_PUBLICO=false); screenshots de regressão apagadas; todos os commits e pushes feitos com autorização do cliente; repositório `updates` ≡ `main` |
| 3.9 | 14/11/2026 | **CATÁLOGO TÉCNICO** criado (`docs.md`): arquitetura, decisões de negócio (Projetos 404, Newsletter opt-in 2 passos, anti-bot, banner Largo, pin editorial), imagens, testes, deploy |
| 4.0 | 14/11/2026 | **`dimensao.md`** — especificações para o designer: capas 1200×675 16:9, contexto 800 px, destaques 1200×600, banners (970×250 / 970×90 / Lateral 1:1 / Rodapé) / logo 400×100 / favicon 32×32 / avatar 150×150 |
| 4.1 | 14/11/2026 | **FIX BOM admin.css/dashboard.css:** a regra universal `* { box-sizing: border-box }` era descartada pelo browser porque o ficheiro começava com bytes UTF-8 BOM mal-liberados — todo o painel estava a renderizar `content-box`; inputs com `padding:12px 14px` + `width:100%` ficavam **30px mais largos** e vazavam do card; layout é agora correctivo (`b9d7601`) |
| 4.2 | 15/11/2026 | **PERFORMANCE/CLS (otimização PageSpeed):** desktop 90 / SEO 100 / mobile 65 → investigação → implementadas (a) `width`/`height` explícitos em todas as `<img>` públicas; (b) `aspect-ratio` para imagens; (c) `loading="lazy"` abaixo da dobra; (d) logos convertidas para WebP + referências; (e) CSS não crítico para `media="print" onload`, mas **revertido** porque gerava **FOUC** (página era carregada do zero); recomendação final: manter CSS clássico + `.htaccess` com compressão e cache (`a→90c1f96`). **Measured nav:** Dashboard 750 ms TTFB |
| 4.3 | 15/11/2026 | **AUTOSAVE (opção B do cliente):** purificador removido do autosave — o rascunho é gravado tal como está; `ArticleSanitizer::clean()` continua no caminho Publicar/Pré-visualizar. Guards no cliente: `emVoo` impede autosaves sobrepostos; timeout 45s (AbortController); Enter em inputs de uma linha já não submete o formulário; **`beforeunload` removido** (nunca mais aviso nativo) — o `pagehide` faz `navigator.sendBeacon` do rascunho; navegação dentro do admin com alterações → **modal `window.PIGEAConfirm` do site** (nunca `alert`/`confirm()` nativo). Suite atualizada para o novo contrato (261/884) (`b9dee36`) |
| 4.4 | 15/11/2026 | **REDES SOCIAIS — Link sempre externo:** helper `urlAbsoluta()` aplicado no render e na gravação; links que faltavam esquema viram `https://`; `target="_blank" rel="noopener noreferrer"` já estava presente em todos os componentes públicos |
| 4.5 | 15/11/2026 | **EMAIL DE CONTACTO profissional:** novo Mailable `ContactMessage` — HTML com identidade PIGEA (header escuro + barra vermelha, logo embutido via CID, replyTo do remetente) + versão texto alternativo; `ContactForm` usa este Mailable (nunca mais `Mail::raw()`); suite verde (`d5985d7`) |
| 4.6 | 15/11/2026 | **Admin Consulta REDES no painel de Presets:** o "Social Links" em Configurações aceitava valores sem esquema; agora grava `https://` automaticamente e nunca gera caminhos relativos |
| 4.7 | 15/11/2026 | **REGISTO DE EMPRESA/PROFISSIONAL corrigidos:** inputs `type="url"` passam a ter o estilo do formulário (antes eram browser-defaults, colados e sem padding); **áreas de atuação em chips** (`.areas-grid`/`.area-chip`) — era uma classe `.filter-tags` de um contexto externo (`.main-register`); e o `.areas-grid` colapsava para `width=0` dentro do `<fieldset>` no Chrome (quirk de `min-content`); forçado `width: 100%` explícito; form layout alinhado perfeitamente (`cdc6cf7`) |
| 4.8 | 15/11/2026 | **PERFORMANCE:** suite completa verde (261/884); `view:cache` limpo; Chrome DevTools confirma TTFB Dashboard ≈750 ms local (a maioria do tempo é boot do Laravel — o `.htaccess` com `mod_deflate` e `mod_expires` ainda pode reduzir isso; a recomendação está na secção 8) |
| 4.9 | 15/11/2026 | **Submission autorização do cliente:** "commit e push" `fix(forms)` → `cdc6cf7` — BOTH `updates` e `main` no GitHub atualizados |
| 5.0 | 15/11/2026 | **RELEASE «PRODUÇÃO 2026-11-16»:** Newsletter simplificada (estados `pending`/`active`/`cancelled`, 3 templates HTML, mailables `NewsletterConfirmacao`/`NewsletterCancelamento`, rota `/newsletter/resubscrever/{token}`); Home «Últimas Notícias» server-rendered (5 recentes, abas AJAX removidas); widget publicitário sem título ("Escolha da Equipa" apagada); mega menu sobre sem «Missão, visão e valores» (migração `2026_11_19`, grupo renumerado 1–4); **módulo institucional Equipa + Parceiros** (`/equipa`, `/parceiros` públicos; CRUD Livewire admin; cargo select "Outros" + `cargo_outro`); **Sobre Nós editável** (Missão/Visão/Valores/vídeo/overlay nas Admin → Configurações via `App\Support\Institucional` com defaults); **Diretório — emails transacionais** (mailer `geral`, remetente `geral@pigea.ao`, separado da newsletter; `RegistoConfirmacao`/`Aprovado`/`Rejeitado`; campo email obrigatório; migração `2026_11_17`); emails transacionais ContactMessage mantêm replyTo = visitante; `config/mail.php` com `mailers.geral`; suite local **279/962 verde** |
| 5.1 | 15/11/2026 | **GIT:** `updates` e `main` fast-forward para **`046930f`** (`18b9c82` feat Equipa/Parceiros + Sobre Nós editável; `046930f` merge) — pushed; sem force-push |
| 5.2 | 15/11/2026 | **PATCH DE PRODUÇÃO APLICADO:** `prod-patch-2026-11-16.sql` colado no phpMyAdmin do hosting → **migrações marcadas como executadas = 29** (verificação `migr_execucoes` devolveu 29); `prod-categorias-news.sql` fica opcional (só se mega menu incompleto); `.env` de produção: bloco `MAIL_*` intocado, acrescentar apenas `MAIL_GERAL_*` |
| 5.3 | 15/11/2026 (noite) | **INCIDENTE PRODUÇÃO + CORREÇÃO:** após upload FTP, o site devolveu 500 generalizado; `storage/logs/laravel.log` mostrou `Route [equipa] not defined. (View: .../components/main-menu.blade.php)` às 20:59:10. **Causa:** deploy parcial — o servidor tinha `app/Support/MenuCategorias.php` novo (que aponta `sobre→equipa ⇒ route('equipa')`) mas o `routes/web.php` ficou antigo / havia route cache (`bootstrap/cache/routes-v7.php`) a sombrear as rotas novas. **Correção:** upload FTP da release completa (foco em `routes/web.php`) + remoção de `bootstrap/cache/routes-v7.php` e `config.php` no File Manager + hard refresh; o cliente reporta **«Já funcionou»**. Cronologia completa e regras anti-repetição em `doc/fix-equipa-route-2026-11-15.md`; erro guardado em `pigea/erro.md` na raiz do workspace |
| 5.4 | 15/11/2026 (noite) | **DEPLOY DO CLIENTE — como foi feito na prática:** o servidor **não corre `composer install`** (vendor não mudou); GitHub não é o deploy (só FTP/cPanel); testes pós-recuperação ficaram a cargo do cliente (`/`, `/equipa`, `/parceiros`, `/sobre`, `/contactos`) |
| 5.5 | 15/11/2026 (noite) | **CATEGORIAS PRODUÇÃO REPARADAS:** cliente reporta «poucas» no select de Categoria do «Criar Notícia». Prova: `news=30` (10 topos+20 filhos; só Academia/Oportunidades corretamente ligadas) vs local `37`. **Mecanismo:** `prod-categorias-news.sql` Passo 1/2 com guard `WHERE NOT EXISTS (slug=…)` global → 7 topos silenciosamente saltados; 17 filhos industriais órfãos de pai; mega menu continuava «todas» (lê `menu_group`) enquanto o select lê `type='news'`+`parent_id`. Fix executado pelo cliente no phpMyAdmin: 7 INSERTs de topos news (`agua`,`ambiente`,`internacional`,`investigacao`,`mineracao`,`petroleo-e-gas`,`geociencias`, com `menu_group NULL` como taxonomia) + 3 UPDATEs `parent_id=<topo>` onde `parent_id IS NULL` e `menu_group ∈ {mineracao, petroleo-e-gas, geociencias}` → **news=37, menu=16 confirmados** |
| 5.6 | 15/11/2026 (noite) | **`prod-categorias-news.sql` CORRIGIDO (repo):** os 37 guards `WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug=…)` passam a `… WHERE type='news' AND slug=…` (substituição byte-level, 37/37). Agora o ficheiro é seguro de re-executar noutros servidores e cria os topos+filhos completos (37 news) de uma vez só |
| 5.7 | 15/11/2026 (noite) | **ANÁLISE DOS 3 UPLOADS DE IMAGEM (editor «Criar Notícia»):** documento para o cliente decidir o que fazer com o 3º campo — «Imagens de contexto» é ficheiro-only por design (`$request->file('contexto')` → MediaLibrary `contexto` + WebP `nonQueued`); capa e foto de autor convidado gravam URL diretamente nas colunas. A coleção `contexto` não tem nenhuma renderização pública hoje (potencial código morto). Pedido do cliente de aceitar link aí também: aguarda escolha (a)/(b) |
| 5.8 | 15/11/2026 (noite) | **CSP Report-Only — relatórios img-src externos ruído:** log de produção 22:54:52 mostra dezenas de relatórios `img-src` para imagens `https://i.ibb.co/…` (admins colam URLs externos nos artigos). Report-Only nunca bloqueia; corrigido para `img-src 'self' data: https:` + `media-src 'self' https:` (sem efeito no bloqueio — só acaba com o ruído). **ContactForm silencioso:** o `catch` de envio do formulário de contacto engolia a exceção sem log nenhum → agora `report($e)`. **`RegistoEmails` já logava** (`[registo] falha ao enviar email …`). Suite 27/27 nos testes de CSP/contacto/registos |
| 5.9 | 16/11/2026 (00:31) | **«Mailer [geral] is not defined» — causa raiz provada por md5:** tinker no servidor mostrou só os 9 mailers por omissão; `bootstrap/cache/` SEM `config.php` (nada de cache — só packages/services rebuilt às 00:31); logo o disco manda. `md5sum config/mail.php` no servidor = `18601a92…` ≠ local `02b70817…` (com `geral`, 5.134 bytes). **Conclusão: o `config/mail.php` novo NUNCA chegou ao servidor (ou ficou noutra pasta), apesar de o cliente o dar como presente.** Regra: após CADA upload por FTP, verificar `md5sum` (+ `php artisan optimize:clear`) antes de testar. Ainda por subir na produção: `ContactForm.php` (064fc141…) e `SecurityHeaders.php` (75a73e04…) |
| 5.10 | 16/11/2026 | **LOGO DOS EMAILS → i.ibb.co/jvhNs1zv/logo.png em TODOS os 7 templates** (pedido do cliente): os 6 que apontavam `https://pigea.ao/images/logo.webp` + o `contacto.blade.php` (que usava CID embed com `height="63"` fixo — distorção possível em alguns clientes). Agora todos: `<img src="https://i.ibb.co/jvhNs1zv/logo.png" width="150" … height:auto; border:0>` (sem altura fixa → nunca estica). URL verificada (responde `image/png`). Nota: clientes de email que bloqueiam imagens remotas mostram o `alt` ("PIGEA") — comportamento esperado. Suite: 28/28 (RegistoEmails/Newsletter/Formulários) |
| 5.11 | 16/11/2026 | **ÍCONES DOS EMAILS (círculo colorido) — causa e correção:** a área circular com fundo diferente em cada template usava **SVG inline** (relógio/visto/envelope/X); os clientes de email (Gmail/Outlook) **não renderizam SVG** → o ícone não aparecia na receção. Correção: SVG removido e substituído por **glifos Unicode** dentro do círculo (× `00D7`, ✓ `2713`, ✉ `2709`, ⏳ `23F3`) + estilo na célula (`font-size:34px; line-height:80px; color:#FFFFFF; font-weight:700`). Verificado byte-a-byte nos 6 templates; suite 28/28. **Limite conhecido:** no Outlook desktop (motor Word) `border-radius:50%` desenha quadrado — o glifo continua visível; para círculos perfeitos lá, alternativa = PNGs hospedados (i.ibb.co como a logo) |
| 5.12 | 16/11/2026 | **CICLO DE EMAIL FECHADO — confirmado pelo cliente: «já está a funcionar».** Produção: `config/mail.php` reenviado + md5 validado; `Mailer [geral] is not defined` resolvido (o ficheiro no servidor era o antigo — o upload tinha ido «às cegas»); contacto envia; logo i.ibb.co + glifos Unicode nos círculos confirmados no recebimento. **md5sum após CADA upload FTP ficou lei manual (§8-7).** Dívidas menores: DKIM no cPanel (Email Deliverability) |
| 5.13 | 16/11/2026 | **PROPOSTA GESTÃO/PERFIL — FASE A IMPLEMENTADA (local):** decisões do cliente 16/11 integradas em `prop.md` Rev 1.1→1.2 (Institucional só admin via `can:institucional.manage`; Newsletter admin+editor via `can:newsletter.manage`; telefone/província editáveis pelo admin; reset com troca obrigatória no próximo login; Desativar principal; Fase C separada; retenção 90d; aprovação por fases A→B→C). **FASE A:** `ProfileController::senha()` (auto-serviço admin+editor; `current_password:web`; `Password::default()`; `throttle:6,1`; **termina todas as outras sessões**), cartões «Informação da conta» (último acesso, conta criada, 2FA) e «A minha senha» em `/admin/perfil`, rota `admin.perfil.senha`, 5 testes novos (suite **284/976 verde**) |
| 5.14 | 16/11/2026 (fim do dia) | **FASE B IMPLEMENTADA (local) — suite 295/1015 verde.** **B1:** página `utilizador-editar` (nome/email/papel/telefone/província; proteção: sem auto-rebaixar nem rebaixar o último admin). **B2:** `POST /utilizadores/{user}/senha` → reset + `must_change_password=true` + **mata todas as sessões**; `EnsureMustChangePassword` tranca o painel (`routeIs('admin.perfil*')`/logout livres) até trocar (flag limpa em `ProfileController::senha()`); migração `2026_11_16_010000_add_must_change_password_to_users_table`. **B3:** botão «Desativar/Reativar» com efeito real — `status=blocked` + sessões apagadas na hora; novo middleware **`EnsureUserIsActive`** (grupo web, pós-auth) desloga no pedido seguinte; último admin protegido; eliminação em **dois passos** (pedir → escrever o email da conta; bloqueada com artigos associados). **B4/B5:** paginação 10/25/50/100 (fim do «6 fixos»), coluna «Criado», badge «Desativado». **Permissões (decisões do cliente):** novo `institucional.manage` (**só admin**) → Equipa/Parceiros escondidos do editor na sidebar **e** rotas 403; novo `newsletter.manage` (**admin + editor**) → newsletter visível/editável ao editor. **Novo `prod-patch-2026-11-16-b.sql`** aplica tudo na produção. **Nota de processo:** um replace global descuidado em `routes/web.php` alinhou 24 `whereNumber('id')`→('user') — detetado e revertido (29 linhas verificadas). **Nota testes:** `pigea_test` teve de ser re-semeada (`migrate:fresh --seed`) por ficar atrás do RoleSeeder; teste NewsletterTest atualizado ao novo contrato (editor acede ao painel) |
| 5.15 | 16/11/2026 (noite) | **«Class Laravel\Pail\PailServiceProvider not found» — vendor-manifest do servidor poluído.** Causa: os caches locais `bootstrap/cache/packages.php`/`services.php` (regenerados 15/11 19:31; conferido: referenciam `Laravel\Pail\`, dep de desenvolvimento `laravel/pail` só instalada localmente) foram para o servidor via upload de pasta/zip inteira — o vendor de produção é `--no-dev` e não tem o pacote → o Laravel quebra **no boot**, antes de qualquer comando (inclusive `optimize:clear`) e **antes de qualquer página** (site cai junto). **Correção (File Manager):** apagar `public_html/bootstrap/cache/{packages.php,services.php}` — o Laravel reconstrói-os a partir de `vendor/composer/installed.json` (só prod) e o Pail desaparece. **Regra nova (§8-8):** o `bootstrap/cache/` **nunca** sobe por FTP; sobem apenas `bootstrap/app.php` e `bootstrap/providers.php` (ficheiros do projeto). Git já ignorava `bootstrap/cache/*` via `.gitignore` aninhado — o perigo são os zips manuais |
| 5.16 | 16/11/2026 (noite) | **«CONFLITO DE ROTAS» (editor redirecionado sempre para perfil) — RESOLVIDO pelo cliente.** Causa: o middleware **`EnsureMustChangePassword`** a funcionar como projetado — a conta de teste do editor tinha `must_change_password=1` (senha reposta pelo admin durante o teste de «Repor senha» da Fase B2). O cliente **mudou a própria senha** e o painel abriu. **Bug real encontrado:** o middleware trancava TODAS as rotas, incluindo as públicas (`/` · `/noticias` etc.). Correção (commit pendente de decisão do cliente): tranca restringida a `admin.*` — site público fora do bloqueio. **md5 do ficheiro:** `1b4ffa1305eaa6672442416b4ce7091f`. **Decisão do cliente:** middleware corrigido fica de fora da produção por ora (não vai ser subido) — só entra no próximo deploy |
| 5.17 | 16/11/2026 (noite) | **README.md atualizado (suite, roadmap, §12 deploy discipline, release 2026-11-16):** números corretos (295/1015), tabela «Release 2026-11-16 — o que mudou em produção» (todos os itens, incluindo Honesty markers: ⚠️ sobre a verificação da coluna `must_change_password` e ⚠️ sobre upload do middleware), secção de erros do painel (Pail/route), pacote completo refresha §12. **Nota produtiva a manter:** o `prod-patch-2026-11-16-b.sql` foi verificado pelo cliente no phpMyAdmin (16/11): permissões 2 rows/3 links; coluna `must_change_password` confirmada `col=1`. **Fase B 100% aplicada em produção.** **DKIM confirmado atualizado pelo cliente (16/11).** Memória entregue em commit junto a outros docs |
| 5.18 | 17/11/2026 | **«AVISO DE NOVO ARTIGO» (Fase N2, opção B do cliente) — IMPLEMENTADO.** Mailable `NewsletterArtigo` (capa+título+excerto+botão «Ler no site»+unsubscribe único por destinatário); tabela `newsletter_editions`/`newsletter_edition_items` (migração `2026_11_17_100000_*`); ecrã `/gestao-pigea/newsletter/enviar` (escolhe 1 dos 10 artigos publicados, **100 activos por clique**, modo «Enviar teste» só para a caixa do admin, histórico das últimas 5 edições); sidebar «Aviso de artigo» + botão no ecrã de subscritores; falhados registrados e retomados no clique seguinte. **Suite: 299/1032 ✔**. **Produção pendente:** criar as duas tabelas via **SQL manual inline** (sem import) + upload dos 10 ficheiros (md5s) + `optimize:clear`. Cliente com muita dificuldade repetida com imports → SQL fornecido colado direto no separador SQL |
| 5.19 | 17/11/2026 | **LOGO DOS EMAILS TROCADA — agora: `https://docs.pigea.ao/imag/logo.png`** (pedido do cliente; substituída a i.ibb.co/jvhNs1zv/logo.png em **todos os 8 templates** de email + README). Suite 24/24 nos testes de email ✔ |

---

*Este documento deve ser atualizado sempre que houver mudança significativa no projeto (nova funcionalidade, alteração de ambiente, decisão de arquitetura, etc.).*
