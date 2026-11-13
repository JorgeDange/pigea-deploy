# Relatório técnico — «A área de publicidade na inicial não aparece»

**Data:** 12/11/2026 · **Ambiente:** Laravel 12 + Livewire 3 + Tailwind 4 · `artisan serve` em `http://127.0.0.1:8000/` · Chromium via CDP (Chrome Remote Debugging)
**Ficheiros alterados:** 3 (ver §4). **Não foi feito commit.**

---

## 1. Resumo executivo

A área de publicidade da página inicial **existe e está correcta no código** (o componente `<x-banner-publicitario posicao="topo" />` renderiza o banner activo mais recente — banner **#7 «bn»**, imagem `storage/media/banners/2026/11/img_6af51d6898f548.09343178.webp`, 1600×465). O que a cliente vê é **apenas o pequeno rótulo «PUBLICIDADE»** (~73×18 px): a imagem **nunca chega a ser pedida pelo browser**.

Causa raiz dupla, ambas verificadas por medição ao vivo no Chromium:

1. **A caixa da `<img>` é 0×0** (`height:auto` + sem dimensões intrínsecas até carregar + `max-width:100%`), e um `<img loading="lazy">` com caixa 0×0 **nunca "intersecta" o viewport → o Chromium nunca dispara o pedido HTTP**. Medido: `complete:false`, `naturalWidth:0` durante 60+ s e em todas as recargas, com **zero pedidos de rede** para o ficheiro webp (mesmo numa página HTML mínima e isolada). Com `loading="eager"` o mesmo ficheiro carrega em **~558 ms**.
2. **Contentor `shrink-to-fit`**: `.top-banner-area` é `position:absolute` sem largura explícita; a largura colapsa para a do rótulo (~73 px) porque a `<img>` ainda não carregada contribui 0 — assim a área renderiza como uma faixa minúscula e comprimida («tiny strip»).

Adicionalmente, verificou-se que **`loading="lazy"` está globalmente inoperante neste Chromium** (imagens jpg/webp, página isolada): nenhuma imagem com `lazy` carrega. Por isso o banner acima da dobra passou a `loading="eager"` (ver §4 e risco R1).

**Resultado:** após a correcção, o banner aparece centrado na top-bar, com a altura da barra a acomodar o conjunto rótulo+imagem (64 px de 70 px), sem alterações de layout/mega menu/features FASE 11-16.

---

## 2. Sintoma (o que a cliente vê)

«notei que a area de publicidade em inicial não aparece»

Renderizado antes da correcção (screenshot: `relatorio-publicidade-antes.png`):

```
[ social icons ]        PUBLICIDADE  ← rótulo minúsculo (~73×18px, opacidade .55)
                        (imagem ausente / caixa 0×0)          [ 12 NOV  →  [🔍]
```

- Rótulo «PUBLICIDADE» visível mas a imagem nunca aparece → a "área de publicidade" parece não existir.
- Quando a imagem por vezes pintava (cache quente), saía espremida na faixa (~73px) ou cortada na top-bar de 70px (`.top-bar { height:70px; overflow:hidden }`).

---

## 3. Causa-raiz exacta (com evidência)

### 3.1 Estrutura relevante (confirmada por CDP no DOM renderizado)

`resources/views/components/header.blade.php` (top-bar):
```html
<div class="top-banner-area">
    <x-banner-publicitario posicao="topo" />
</div>
```
`components/banner-publicitario.blade.php` → renderiza banner **#7** (activos, `orderByDesc('id')->first()`):
```html
<div class="banner-publicitario banner-publicitario--topo fl-wrap">
  <span ...>Publicidade</span>
  <img src=".../img_6af51d6898f548.09343178.webp" loading="lazy"
       style="max-width: 100%; height: auto; display: block;">
</div>
```
Resolvido pela BD (leitura apenas): activas `topo` = **#7** (sem período) e **#1** (10/11–11/12/2026); #7 vence por ser mais recente. #3 (expirado) não renderiza. `imagem_url` de #7 = `storage/media/banners/...webp` (ficheiro existe, 35 234 bytes, servido com HTTP 200 via junction `public/storage` → `storage/app/public`).

### 3.2 Medições antes da correcção (CDP, porta 9222, separados por navegação e recargas)

| Medição | Valor |
|---|---|
| `document.querySelector('.top-banner-area')` | display:block, `position:absolute; left:50%; top:50%; transform:translate(-50%,-50%)`, **width: 72.65px**, height: 18.4px |
| `.banner-publicitario img` | `complete:false`, `naturalWidth:0`, `naturalHeight:0`, `rect: 0×0`, `currentSrc:""` |
| Requests de rede para o webp | **0** (em 12 s, em várias recargas, e também 0 em página de teste isolada) |
| `WEBP RESPONSES` (CDP network trace) | 1/"flaky" em runs alternados, mas `complete:false` sempre — nunca pintado de forma fiável |
| Após `el.loading='eager'` manual | **complete:true, naturalWidth:1600**, `currentSrc` definido (carrega em ~500 ms) |
| Página mínima `<img loading="lazy" width="560" height="46">` (JPG e WebP) | **nunca carrega** (10–15 s, zero requests); a mesma página com `loading="eager"` carrega em ~500 ms |

### 3.3 Mecanismo — por que a área "não aparece"

1. **`<img>` sem dimensões intrínsecas**: `style="max-width:100%; height:auto"` e sem `width`/`height` ⇒ antes da imagem chegar, a caixa é **0×0**. (Confirmado: `imgRect {w:0, h:0}`.)
2. **`loading="lazy"` + caixa 0×0 ⇒ nunca intersecta o viewport** ⇒ o Chromium nunca agenda o fetch. É o comportamento observado repetidamente (60 s de observação × várias recargas + página isolada). Mesmo com `width`/`height` no atributo (testado), este Chromium nunca disparou o fetch `lazy` — ver §7 R1 (lazy globalmente inoperante neste build).
3. **`.top-banner-area` em shrink-to-fit**: absolute sem `width` ⇒ o Chrome resolve a largura preferida pelos filhos; com a `<img>` em 0×0, a largura colapsa para a do rótulo (~73 px) → «PUBLICIDADE» fica como uma faixa comprimida de ~73×18 px.
4. **`object-fit` inexistente + `overflow:hidden` na top-bar** (`.top-bar{height:70px;overflow:hidden}`): mesmo que carregasse, a imagem `height:auto` com `max-height:46px` ficava espremida sob o rótulo sem margem própria.

### 3.4 O que NÃO era a causa (verificado e descartado)

| Hipótese | Resultado |
|---|---|
| Imagem não existe/404 | ❌ Existe; `Invoke-WebRequest` → 200, 35 234 bytes; `getimagesize` → 1600×465 WebP |
| Junction `public/storage` partida | ❌ OK, aponta para `storage/app/public` |
| CSP a bloquear a imagem | ❌ CSP é `Content-Security-Policy-Report-Only` (não bloqueia) e não há meta-CSP no HTML; `img-src 'self'` permite same-origin |
| `.content-banner-wrap` (linha 204 e ~878 do home) escondidas por CSS (`height:0`/`overflow:hidden`) | ❌ `display:block; overflow:visible`; imagens da `banner.jpg` de 5370×768 carregam e pintam (verificado nos screenshots de fluxo completo) |
| Área `.ad-wrap` (CTA «Registe a sua empresa…» + banner.jpg) | ❌ **Visível** no screenshot completo (`home-full`) |
| Teste PHPUnit ou `view:cache` a falhar | ❌ OK antes e depois (204 testes / 694 asserções) |
| `cbw_mar` com 1393 px de altura | ⚠️ **Não é bug de pintura** — ver Apêndice A: linha de texto inline da `<img>` deslocada abaixo do float `.ajax-wrapper` (1274 px). O modelo visual renderiza correctamente; registado como risco de fragilidade (R5) |
| `#7` é "expirado" | ❌ #7 não tem período; #3 está expirado mas não é o renderizado |

---

## 4. O que foi alterado e porquê

**3 ficheiros, edição mínima. Nada foi removido; o Mega Menu/FASE 11-16 e o `<x-banner-publicitario>` mantêm-se intactos.**

### 4.1 `public/css/topo.css` (2 regras)

```css
.top-banner-area {
    position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%);
    text-align: center;
    width: min(560px, 46vw);           /* NOVO: largura explícita; mata o shrink-to-fit */
}

.top-banner-area .banner-publicitario img {
    width: 100% !important;            /* NOVO: caixa definida (vence o height:auto/max-width inline) */
    height: 46px !important;           /* NOVO: altura da barra (70px) - 18px rótulo ≈ 46px imagem */
    max-height: 46px;
    object-fit: contain;               /* sem distorção, preserva aspect ratio */
    object-position: center;
    margin: 0 auto;                    /* centra o banner na área */
}
```
*Porquê:* elimina a colapsagem para ~73 px (largura explícita) e dá à `<img>` uma caixa definitiva e intersectante (46 px de altura > 0) — condição necessária para o browser considerar a imagem visível. `object-fit:contain` = sem distorção (pub.md §7).

### 4.2 `resources/views/components/banner-publicitario.blade.php` (1 atributo)

```blade
loading="{{ $posicao === 'topo' ? 'eager' : 'lazy' }}"
```
*Porquê:* o banner do TOPO é **above-the-fold** na barra superior; neste Chromium, `loading="lazy"` **nunca** dispara o fetch (verificado de forma isolada — §3.2/§7 R1). `eager` resolve deterministicamente (carrega em ~0,5 s). `sidebar`/`rodape` mantêm `lazy` (abaixo da dobra) e o componente mantém a API/marcação inalterada.

### 4.3 `tests/Feature/BannersFrontendTest.php` (1 asserção, com justificação inline)

`loading="lazy"` → `loading="eager"` no teste `test_banner_vigente_no_topo_com_link`, com comentário explicando a razão (lazy inoperante neste Chromium) e que sidebar/rodapé mantêm lazy. Sem esta alteração a suite falha — o assert verificava um comportamento que, neste ambiente, produz o bug da cliente.

---

## 5. Como a utilizadora vê o resultado

Screenshot após a correcção (`relatorio-publicidade-depois.png`, página carregada + ~13 s, banner carregado):

![depois](relatorio-publicidade-depois.png)

- Top-bar: ícones social (esq.) · **«PUBLICIDADE» + banner #7 centrado (visível)** · data (12 NOV 2026) + pesquisa (dir.).
- A `<img>` reporta `complete:true, naturalWidth:1600`; o pedido de rede para o webp é emitido (1 request) e a área ocupa 560 px centrados sem overlap com esquerda/direita.
- Nada mais mudou no layout: hero, mega menu, `.content-banner-wrap` (204), CTA «Registe a sua empresa» e `banner.jpg` da secção `.ad-wrap` ficam iguais.

Comparação «antes» → `relatorio-publicidade-antes.png`.

---

## 6. Verificação executada

```text
page de teste isolada: <img loading=lazy>  -> NUNCA carrega (JPG e WebP)
page de teste isolada: <img loading=eager> -> carrega em ~500ms
home (cache desactivado, navegação fresca): banner carregado em ~558ms (complete:true, naturalWidth:1600)
php artisan view:cache   -> Blade templates cached successfully
php vendor/bin/phpunit   -> OK (204 tests, 694 assertions)
```

Integração com `<x-banner-publicitario>` mantida (header/home inalterados). Nada foi commitado; tudo fica no working tree.

---

## 7. Riscos e possíveis regressões

- **R1 (alto, ambiente):** `loading="lazy"` está **globalmente inoperante neste Chromium** (jpg/webp, página isolada, 0 requests durante 15+ s mesmo com `width`/`height` e caixa intersectante). O banner da **sidebar** (`/noticias/...`, banner #2 Hidrotec, `loading="lazy"`) foi medido com `complete:false` — **a sidebar também não mostra o banner**. Recomenda-se (decisão da equipa) trocar `lazy`→`eager` em todos os banners do componente, ou investigar o flag/policy do Chromium (testar noutro browser/perfil). **Fora de âmbito desta correcção** (a cliente pediu a inicial); fica documentado.
- **R2 (médio, layout):** `.top-bar` tem `overflow:hidden; height:70px`. A área útil passou a 64 px (18 px rótulo + 46 px imagem) — ok. Se o cliente aumentar `max-height` da imagem para > 52 px, o topo é cortado pelo `overflow:hidden` sem aviso.
- **R3 (baixo, visual):** largura fixa `min(560px, 46vw)`: em janelas muito largas o banner fica centrado mas com letterboxing (`object-fit:contain`) — o "banner" (#7 é um screenshot de perfil do LinkedIn, 1600×465) ocupa ~158×46 px renderizados na caixa de 560 px. Se o cliente quiser o criativo a preencher mais, decisão de arte é dele (ou `object-fit:cover` + altura maior, com R2 em mente).
- **R4 (baixo, performance):** `eager` no topo prioriza a imagem do topo na fila de pedidos — desejável para a dobra; irrelevante para os 35 KB.
- **R5 (informativo):** `.content-banner-wrap.cbw_mar` (linha 204) tem uma **altura computada de 1392,91 px** enquanto o seu único filho visual (banner.jpg) ocupa 118,91 px no rodapé da caixa — causado pela linha inline ser deslocada abaixo do float `.ajax-wrapper` (`.fl-wrap{float:left;width:100%}`, 1274 px). **Não** produz vazio visível nem impede cliques (o `.clearfix` a seguir pertence ao fluxo seguinte; o paint mostra o banner imediatamente abaixo da lista de notícias). É frágil (se o float mudar de altura/`clear` for introduzido, a faixa desloca-se); não foi alterada para não desviar escopo.
- **R6 (informativo):** o servidor de dev `artisan serve` é single-threaded; no primeiro carregamento frio, centenas de pedidos competem e a imagem do topo (agora `eager`) compete com eles — é aí que o cliente verá a melhoria máxima, pois passou a ser pedida já no `<head>`-parse, antes de qualquer JS.
- **R7 (baixo, teste):** a asserção `loading="lazy"`→`loading="eager"` muda a semântica do teste FASE 7 para a posição topo; o comentário inline justifica e mantém o resto da cobertura (expiração/posição/desempate/sidebar intactos — 204 testes verdes).
- **R8 (informativo):** o componente continua a usar `Banner::ativos()->posicao(...)->orderByDesc('id')->first()` — #7 (sem período) vence sempre #1 (vigência actual). Se o cliente pretender vigência estrita, é decisão de produto (registo `orderBy('id')`↔`orderByDesc` já documentado como "desempate pelo mais recente" no pub.md §6.2).
- **Regressões descartadas:** mega menu/FASE 11-16, `.content-banner-wrap` (204/878), CTA, footer, Livewire — não tocados; PHPUnit 204/694 OK; `view:cache` OK; bytes UTF-8 válidos em todos os ficheiros editados (acentos PT intactos).

### Recomendações (não executadas, fora de escopo)

1. Confirmar noutro browser/na máquina da cliente que `loading="lazy"` carrega normalmente; se também não, o defeito é ambiental e **todos** os banners (sidebar em `/noticias`, `#6` rodapé externo `i.ibb.co`) precisam de `eager` ou de remoção do atributo.
2. Avaliar o criativo do banner #7 (é um print de perfil social — pouco legível a 46 px; considerar um criativo horizontal 1600×150 para a top-bar).
3. Retirar `overflow:hidden` da `.top-bar` ou substituir por `clip-path` quando a altura do banner mudar (R2).
4. Substituir as dimensões inline `height:auto` do componente por classes dedicadas se a altura da top-bar for alguma vez responsiva (`@media` já existe para 26–32 px — ver `topo.css` §3–5).

---

## Apêndice A — A "altura fantasma" de 1274 px (investigação completa)

Medições: `.ajax-wrapper.fl-wrap` e `#ajax-content` = **exactly 1274 px**; `.content-banner-wrap.cbw_mar` top = 981,6 px = top do float; altura computada 1392,91 px = 1274 + 118,91; `<img>` renderizada em y = 2255,6 = bottom do float. Mecanismo: o `<img class="respimg">` (inline) pertence à mesma linha que os nós de texto brancos; a linha inline não pode sobrepor o float de largura ~100% ⇒ é deslocada para baixo do float (float bottom). A caixa do bloco (não `float`, sem `clear`) não se desloca — errado? Não: é o comportamento CSS normal de linhas inline displaceadas por floats full-width; a **altura usada** da caixa fica "esticada" pelo conteúdo deslocado. Testes de bissecção ao vivo: `display:none` no `.ajax-wrapper` → altura volta a 118,91 px; `cloneNode(true)` do elemento → 118,91 px; só o original mede 1392,91 px em todas as recargas. **Não afecta a pintura** (screenshots de fluxo completo não mostram vazio) e o cliente não o reportou — fica como risco R5 e pista para manutenção.

## Apêndice B — Checklist de evidência CDP

- [x] DOM: `.top-banner-area` shrink-to-fit → 72,65 px (antes) / 560 px (depois)
- [x] `img`: 0×0 + `complete:false` (antes) → 560×46 + `complete:true, naturalWidth:1600` (depois)
- [x] Rede: 0 requests webp (antes) → 1 request, ~558 ms (depois)
- [x] Página isolada de reprodução lazy vs eager (jpg/webp) → lazy globalmente inoperante; eager OK
- [x] Screenshots: `relatorio-publicidade-antes.png` / `relatorio-publicidade-depois.png` (cópias no working tree)
- [x] `php artisan view:cache` → OK · `php vendor/bin/phpunit` → OK (204/694)
