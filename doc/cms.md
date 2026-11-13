# 🖥️ PIGEA — CMS (Painel de Administração) — **v2.0**

**Documento:** cms.md  
**Versão:** 2.0 · 11/11/2026  
**Âmbito:** descrição visual e funcional do CMS **tal como está implementado** após a Fase 10 (`cms_pro.md`). Complementa v1 (retirada)
**Contrato visual:** apenas classes/estilos existentes (`dashboard.css`, `color.css`, `fonts.css`). Nenhuma nova cor hexadecimal nem nova folha de estilos foi introduzida.

> Screenshots: sem tooling de browser no ambiente; capturas reais devem ser feitas numa máquina com browser (`artisan serve` → porta 8000).

---

## 1. Acesso e permissões

| | |
|---|---|
| **URL (dev)** | `http://127.0.0.1:8000/gestao-pigea` — prefixo vindo de `ADMIN_PATH` (`.env`; antes era `/admin`) |
| **Entrada** | `/login` (Fortify, sem registo público) → redireciona para `/gestao-pigea` |
| **Papéis** | `admin` (tudo) · `editor` (PRODUÇÃO/DIRETÓRIO/COMERCIAL; sem Configurações, Utilizadores, moderação) |
| **Criar admin** | `php artisan pigea:criar-admin email@empresa.com` |
| **Proteção** | grupo `auth` + middleware `role:admin,editor`; por rota `can:<permissão>`; Livewire com `Gate::authorize` |

### 1.1 Matriz de acesso

| Página | Bloco | Permissão | admin | editor |
|---|---|---|---|---|
| Dashboard | `admin.index` | (grupo) | ✅ | ✅ |
| Notícias (lista + editor) | `admin.noticias` / `admin.noticia-nova` / `admin.noticia-editar` | `news.manage` | ✅ | ✅ |
| Empresas | `admin.empresas` | `company.moderate` | ✅ | ❌ 403 |
| Profissionais | `admin.profissionais` | `company.moderate` | ✅ | ❌ 403 |
| Documentos | `admin.documentos` | `document.manage` | ✅ | ✅ |
| Publicidade | `admin.banners` | `banner.manage` | ✅ | ✅ |
| Utilizadores | `admin.utilizadores` | `user.manage` | ✅ | ❌ 403 |
| Configurações | `admin.configuracoes` | `settings.manage` | ✅ | ❌ 403 |

Menu **oculta o grupo SISTEMA** para quem não é `admin` (ocultação é cosmética; proteção real é o middleware).

---

## 2. Estrutura visual (wireframe)

```
┌──────────────┬──────────────────────────────────────────────────────────┐
│ SIDEBAR      │ TOPBAR sticky (66px, branco)                              │
│ 260px, #1A1A │  [☰ Menu]                 [Ver site]  🔔 [N/O]  Avatar  ⏻  │
│              ├───────────────────────────────────────────────────────────┤
│ [logo PIGEA] │ BREADCRUMB (Admin › …)                                    │
│ nav em       ├───────────────────────────────────────────────────────────┤
│ GRUPOS:      │ MAIN: quick action + 4 stat cards + gráficos + atividade  │
│              │                                                           │
│ PRINC.       │  ┌─────────────────┐   ┌──────┐ ┌──────┐                  │
│  Dashboard   │  │  "+ Nova Notícia"│   │ card │ │ card │  …                  │
│ PRODUÇÃO     │  └─────────────────┘   └──────┘ └──────┘                  │
│  Notícias    │                                                          │
│ DIRETÓRIO    │  ┌──────────────────────────────────────────────────┐  │
│  Empresas    │  │  Gráficos: visitas 7d (linha) | utilizadores (bar)│  │
│  Profissionais│ │  | Província (pie)                                  │  │
│  Documentos  │  └──────────────────────────────────────────────────┘  │
│ COMERCIAL    │  ┌──────────────────────────────────────────────────┐  │
│  Publicidade │  │  Atividade recente (empresas pendentes, notícias, │  │
│ SISTEMA*     │  │  utilizadores, oportunidades)                     │  │
│  Utilizadores│  └──────────────────────────────────────────────────┘  │
│  Configurações│                                                        │
│ Sair         │                                                          │
│              │ * SISTEMA só aparece para `admin`                        │
└──────────────┴──────────────────────────────────────────────────────────┘
```

- **Sidebar** fixa ≥1065px; off-canvas ≤1064px (☰ Menu abre/fecha via `dashboard.js`).
- **Topbar**: branco `#FFF` sticky; borda inferior `#E0E0E0`; **botão "Ver site"** (abre frontend em novo separador); **sino com badge real** (Empresas+Profissionais pendentes; sem badge a 0); avatar (fallback `images/avatar/1.jpg`); logout ⏻.
- **Breadcrumb** `Admin › <Página>`.

---

## 3. Design system

Idêntica à v1 (ver `dashboard.css`): sidebar `#1A1A1A`; vermelho `#C8102E` (hover `#A00D25`); dourado `#F2A900`; verde `#2E7D32`; perigo `#8B0000`; fundo `#F5F5F5`; cards `#FFF`; bordas `#E0E0E0`; texto `#1A1A1A`/`#666`. Fontes **Playfair Display** (títulos) / **Poppins** (UI). Botões `.btn-primary` (vermelho sólido), `.btn-ghost`, `.btn-secondary`, `.btn-success`, `.icon-btn` (quadrado 30px com ícone). Badge de sino: pílula branca c/ texto `#C8102E`. `status-badge`: `active` verde / `pending` dourado / `rejected` vermelho. Tipografia de cartões de estatística: `.stat-card`. `charts-grid` usa Chart.js 12 meses + 7 dias para visitas.

---

## 4. Sidebar — grupos e itens

| Grupo | Itens (id da permissão) |
|---|---|
| **Principal** | Dashboard (grupo) |
| **Produção** | Notícias (`news.manage`) |
| **Diretório** | Empresas (`company.moderate`) · Profissionais (`company.moderate`) · Documentos (`document.manage`) |
| **Comercial** | Publicidade / Banners (`banner.manage`) |
| **Sistema** *(só `admin`)* | Utilizadores (`user.manage`) · Configurações (`settings.manage`) |
| — | Sair (POST logout, `fa-sign-out`) |

*Nos grupos oculta-se o que o papel não tem `can:` (cosmético).* Item ativo: fundo vermelho `#C8102E` + texto e ícones brancos.

---

## 5. Páginas

### 5.1 Dashboard
```
┌──────────────────────────────────────────┐
│ [+ Nova Notícia] ← botão grande/vermelho  │
├──────────┬──────────┬──────────┬─────────┤
│ Notícias │ Empresas │ Visitas  │ Banners │
│ public.  │ pendentes│ no site  │ ativos  │
├──────────┴──────────┴──────────┴─────────┤
│ Gráficos: Visitas 7d (linha) | Utiliz./  │
│ mês (barras) | Províncias (pie)          │
├──────────────────────────────────────────┤
│ Atividade recente (5 itens: pendentes,   │
│ notícias, utilizadores, oportunidades)   │
└──────────────────────────────────────────┘
```
- **4 cartões** vermelhos: `+N este mês` para Notícias; **link → página de aprovação** no cartão de Pendentes; **total histórico + hoje** em Visitas (fonte: `page_views`); total de Banners Ativos.
- **Ação rápida** com título Playfair + botão `.btn-quick`.

### 5.2 Notícias (lista) / Editor de notícias
- Lista: inalterada: `content-table` Livewire (6/pág., filtros Categoria/Estado/busca data).
- **Editor (CMS Pro — o ecrã mais importante)** em 2 colunas (70/30):
  - Esquerda: **Título**, **Subtítulo** (linha fina), **Corpo** (TinyMCE self-hosted v7.9.3) com **Negrito · Itálico · Sublinhado · Listas · Inserir link · Inserir imagem** e **Drop Cap · Blockquote · Duas colunas** (estilos especiais).
  - Direita: **ações** — Pré-visualizar (modal sem gravar) · **Guardar rascunho** / **Publicar**; **Categoria** (select `type=news`); **Imagem de capa** (upload ≤2MB · help 1200x675px 16:9); **Imagens de contexto** (multiple, help 800px); **Vídeo (YouTube)** · **Documento (PDF)**.
  - **Autosave**: guarda rascunho automaticamente 5s depois de para de escrever (`wire:model.live.debounce.5000ms`) — o indicador inline diz "Guardado às HH:MM".
  - **Publicar exige** título + conteúdo + categoria; rascunho aceita texto parcial.
- Publicar redireciona para `/gestao-pigea/noticias`. Edição via o mesmo componente (rota `gestao-pigea/noticias/{id}/editar`).

### 5.3 Moderação (Empresas / Profissionais) · 5.4 Documentos · 5.5 Banners · 5.6 Utilizadores · 5.7 Configurações
Inalterados (ver v1 do cms.md / o código): tabelas Livewire com filtros e ações (aprovar/rejeitar/restabelecer/eliminar; documentos = só link; banners = upload ou link + período + toggle ativo; utilizadores → modal "Adicionar utilizador" só editor/admin; configurações → cards Geral/Contactos/Redes/SEO/Idiomas/Notificações).

---

## 6. Componentes partilhados

`.card`, `.dashboard-table`, `.dashboard-filters`, `.row-actions`, `.status-badge`, `.btn-primary/ghost/secondary/success`, `.icon-btn`, `.status-badge`, `.upload-box`, `.filter-search`, `.filter-search`, `.dashboard-pagination`, `.dashboard-msg`, `.autosave`, `.help-text`, `.quick-action`, `.stat-card`, `.charts-grid`, `.notification-list`, `.dashboard-form-*` — todos existentes; regras novas adicionadas a `dashboard.css` (paleta intacta).

---

## 7. Notas e limitações

- **Perfis dos utilizadores**: somente `editor`/`admin` atribuíveis.
- **Sino**: badge = Empresas+Profissionais pendentes (clique no número de moderação pendente) — não é um sistema de notificações completo.
- **Idiomas/notificações por email**: decorativos (sem i18n nem envio).
- **Pré-visualizar/Publicar** no editor são reais (Livewire) — ao contrário do protótipo.
- **`Ver tudo`** removido do Dashboard junto com a tabela decorativa de conteúdo pendente.
- **CSS inerte** `.main-register-*`/`.hnt_title` mantidos (decisão Fase 8, risco/benefício).
- Screenshots em falta (ambiente sem browser).

---
*Fim do documento. Toda a informação foi extraída do código (routes, blade views, componentes Livewire, CSS).*
