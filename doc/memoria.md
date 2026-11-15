# 🧠 Memória do Projeto — PIGEA

**Projeto:** PIGEA
**Localização:** `C:\Users\Jorge Dange\Documents\PROJECTOS DE CLIENTES\QNB\pigea\pigea-deploy\`
**Última atualização:** 15/11/2026 (esta revisão cobre **tudo** até esta data)
**Propósito:** Manter contexto de tudo o que foi analisado, instalado, decidido e planeado para não perder rastro do trabalho.

> Estado no fecho desta atualização (15/11/2026): **branch `updates` e `main` sincronizadas em `cdc6cf7`**; suíte local **261 testes / 884 assertions verdes**; `php artisan view:cache` limpo; tudo o que está em `main` foi push para o GitHub.

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

**Produção atual**: site live em `https://pigea.ao/` (Laravel serve raiz). O domínio `pigea.qnbangola.ao` deixou de ser alvo. O site usa mesmo servidor de Mail (mesma máquina cPanel, `mail.pigea.ao:465 smtps`).

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

**Deploy manual FTP/cPanel:** ver `deploy.md` — pacote via `git archive` ou lista mínima; sempre apagar `storage/framework/views/*` (exceto `.gitignore`) e `bootstrap/cache/config.php`/`services.php` e hard-refresh (Ctrl+F5). **25 migrações novas desde a primeira versão** ainda por correr no servidor (`2026_11_*`).

---

## 7. O Que Faltou / Pendências (15/11/2026)

| Item | Estado | Observação |
|------|--------|------------|
| Migrar as 25 migrações novas na produção | ❌ Pendente | O `php artisan migrate --force` não foi corrido no servidor; as 5 categorias de mudanças de dados existem apenas no git |
| `php artisan storage:link` na produção | ❌ Pendente | Necessário para servir uploads |
| `php artisan pigea:criar-admin` na produção | ❌ Pendente | Substituiria o admin local; necessário após migração |
| SMTP definitivamente funcional na produção | ✅ Funcional (login/autenticação 535 resolvida — password real do utilizador com caracteres especiais resolvida com **aspas simples** no `.env` do servidor) | Resta confirmar DKIM no cPanel (Email Deliverability) para evitar SPAM; SPF já OK |
| Migração de imagens para WebP na produção | Pendente de decision | Apenas news (capa) têm conversão WebP; imagens estáticas do tema até ~4 MB |
| Performance mobile PageSpeed 65 → alvo 80+ | Em progresso (otimização CLS medida 90/100/65 antes; pós-deploy re-medir) | §4 async-CSS revertida por FOUC (ver docs.md) |
| Ainda há comentários extensos em PHP/Blade/JS | ✅ Aplicativa para fases futuras | A migração para docs.md seria fase 2 — catálogo criado em 11/11/2026 (`docs.md`); migração completa não autorizada |
| CSP rigorosa | Pendente | Considerada na fase inicial; não aplicada em produção (compatibilidade com legado) |
| Deploy FTP automatizado | Pendente de decisão | Cliente prefere manual |

---

## 8. Próximos Passos (se/quando autorizados)

1. **No servidor de produção** (primeiro tudo o seguinte antes de anunciar o site):
   - `cd ~/public_html/.../pigea-deploy` (ou o caminho real)
   - `php artisan migrate --force` — as 25 migrações novas
   - `php artisan storage:link`
   - `php artisan role:p criar-admin <email-real>`
   - `php artisan optimize:clear`
   - `chmod -R 775 storage bootstrap/cache` e `chmod -R 755 public`
   - Confirmar: `curl -I https://pigea.ao/` 200; `.../entrar-7x2` 200 (ou o caminho opaco definido no servidor); `/login` 404
2. **DKIM**: ligar em cPanel → Email Deliverability → `pigea.ao` (evitar SPAM).
3. **Retormar password** do utilizador MySQL `qnbangolaco_pigea` e sobre a APP_KEY expostas no histórico inicial (recomendação anterior).
4. Verificar em produção que as imagens no hero continuam a funcionar pós-migração (imagens exclusivas à BD do cliente se algum artigo fora de fallback).
5. Reavaliar **PageSpeed** pós-deploy (imagens e async do hero).
6. (Pendente de decisão) Comprimir imagens estáticas do tema (banners, city-bg, bg/*.jpg) para WebP com redução de 60–75%.

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

---

*Este documento deve ser atualizado sempre que houver mudança significativa no projeto (nova funcionalidade, alteração de ambiente, decisão de arquitetura, etc.).*
