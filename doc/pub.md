# 📢 Sistema de Publicidade — PIGEA (banners)

**Estado:** ✅ **Implementado** (Fase 6 da reestruturação, 11/11/2026 — ver `modificar.md` §13 e §15).  
**Data desta revisão:** 11/11/2026 (Fase 8)  
⚠️ O documento original (`pub.md` pré-11/2026) descrevia outro sistema (tabelas `banners` **e** `banner_slides`, componentes Livewire frontend com slider automático) que **nunca foi implementado** — é obsoleto e foi substituído por este.

---

## 1. Visão Geral

O PIGEA publica publicidade através de **uma única tabela `banners`** e um **componente Blade estático** (`<x-banner-publicitario />`, não Livewire) — render server-side, zero JS, zero queries no cliente. Sem banner vigente para uma posição, não gera HTML nenhum (o espaço fica vazio/livre).

---

## 2. Modelo de Dados — tabela `banners`

| Coluna | Tipo | Notas |
|--------|------|-------|
| `id` | pk | |
| `titulo` | string | título interno + `alt` da imagem |
| `imagem_url` | string | upload (`storage/banners/…` via public/storage) **ou** link externo (`https://…`) |
| `link_destino` | string, **nullable** | `null` → imagem **não clicável** (pub original §5.5 mantida) |
| `posicao` | enum `topo|sidebar|rodape` | |
| `data_inicio` | date, nullable | sem data → sempre vigente |
| `data_fim` | date, nullable | sem data → sempre vigente |
| `ativo` | bool, default true | desativado → ignorado |
| `created_at`/`updated_at` + `deleted_at` | timestamps | **SoftDeletes** (Fase 4) |

- `Banner::ativos()` — scope: `ativo = true` AND (`data_inicio` IS NULL OR `<= today`) AND (`data_fim` IS NULL OR `>= today`).
- `Banner::posicao($posicao)` — scope por posição.
- **Desempate** (pub original §6.2): quando há mais de um banner vigente na mesma posição, vence o **mais recente por `id`** (`orderByDesc('id')->first()`).
- Upload de imagem faz `php artisan storage:link` (criado na Fase 2). Validação do upload: imagem real (`intervention` via `image:webp`, limite de tamanho).

---

## 3. Posições e locais em uso

| Posição | Local | Nota |
|---------|-------|------|
| `topo` | **Top-bar, centro** (pedido do cliente, 11/11/2026) | largura/área limitada pela CSS (`topo.css`: `max-height` 46px, breakpoints 1279/1064/630px) |
| `sidebar` | Sidebars de `/noticias` e detalhe de notícia | embrulhado no box-widget do tema quando usado com `wrapper` |
| `rodape` | Rodapé (pronto; **sem posição inserida ainda**) | pronto para ativação quando o cliente definir o local |

---

## 4. Componente frontend `<x-banner-publicitario />`

```blade
<x-banner-publicitario posicao="topo" />
<x-banner-publicitario posicao="sidebar" wrapper />   {{-- embrulha no box-widget --}}
```

Comportamento:
1. Lê `Banner::ativos()->posicao($posicao)->orderByDesc('id')->first()`.
2. Sem banner vigente → **sem HTML**.
3. Rótulo discreto **"Publicidade"** visível.
4. `alt` = `titulo` do banner.
5. `loading="lazy"`, `max-width:100%`, `height:auto` (sem distorção).
6. `link_destino` → `<a target="_blank" rel="noopener sponsored">`; sem link → imagem sem `<a>`.
7. `imagem_url` aceitando `http(s)://…` (usa direto) ou caminho relativo (`asset()`).

---

## 5. Administração (`/admin/banners…`)

| Rota | Ação |
|------|------|
| `admin.banners` (GET `/banners`) | `BannersTable` — listagem (filtro por posição/estado), soft-delete, toggle `ativo` |
| `admin.banners.novo` / `editar` | `BannerFormController` — formulário |
| Armazenamento/atualização | Validação: `titulo` obrigatório; **criação exige imagem** (`required_without:imagem_upload,imagem_url`), edição não (mantém a atual se nada for enviado) |
| `posicao` | `in:topo,sidebar,rodape` |
| `link_destino` | `nullable`, url, max 255 |
| Permissão | `banner.manage` — `admin` e `editor` (ver `RoleSeeder`) |

A equipa gere os banners em **Admin → Publicidade → Novo banner**. Sem necessidade de tocar no código para trocar imagem/link/período.

---

## 6. Regras de negócio (resumo operacional)

1. Uma posição mostra **um único banner** (o mais recente vigente). Para alternar dois, alternar `ativo`/datas manualmente — **slider é feature futura**, não implementada.
2. Banner futuro (`data_inicio > hoje`) não aparece; expirado (`data_fim < hoje`) não aparece.
3. Rótulo "Publicidade" é **obrigatório** (não remover para "esconder" o banner pago).
4. SoftDeletes: banner eliminado no admin não reaparece nem quebra nada; restauração via DB se necessário.

---

## 7. Notas e histórico

- O **spec original** (com `banner_slides`, slider por tempo, componentes Livewire `<livewire:frontend.banner />`) foi reformulado para o paradigma único do cliente (`pub.md` pré-restruturação §6–7); decisões registadas em `modificar.md` §13 (decisão: componente Blade, não Livewire) e §15.
- **Não há** medição de impressões/cliques (Analytics) no módulo — se o cliente quiser, é fase futura.
- Imagens grandes devem estar otimizadas (o admin não comprime — usar ficheiros WebP/JPEG já reduzidos; a conversão WebP já é aplicada às capas das notícias via Spatie Media Library).

---
*Documento reescrito em 11/11/2026 (Fase 8) — substitui o `pub.md` original (obsoleto). Sistema validado: ver suíte `BannersFrontendTest` (7 testes) e navegação em `modificar.md` §13/§14/§15.*
