# 🚀 Prompt Técnico — Otimização de Performance (PageSpeed Mobile)

## 🎯 1. Objetivo
Melhorar a pontuação do PageSpeed Insights para mobile (atualmente 65) e corrigir os problemas críticos de estabilidade visual (CLS de 1,007) e renderização.

## 🔴 2. Correção Crítica: CLS (Cumulative Layout Shift)
O layout está a "saltar" porque as imagens não têm dimensões definidas. Isto é a prioridade máxima.

**Ação:**
1.  **Imagens no Frontend:** Em todas as views Blade (`home`, `noticias`, `projetos`, `empregos`, `banners`, etc.), adicionar os atributos `width` e `height` explícitos a todas as tags `<img>`.
    *   Exemplo: `<img src="..." width="1200" height="675" alt="...">`
    *   Para imagens dinâmicas (notícias, banners), usar as dimensões recomendadas:
        *   Capa de notícia: `width="1200" height="675"`
        *   Banner largo: `width="970" height="250"`
        *   Banner lateral: `width="600" height="600"`
        *   Logo: `width="400" height="100"` (ou a dimensão real do ficheiro).
2.  **Logos:** No `header.blade.php` e `footer.blade.php`, garantir que a tag `<img>` do logo tem `width` e `height` que correspondam à **dimensão real de exibição** (ex: se o CSS mostra a 25px de altura, o atributo `height="25"` deve ser usado, mas o ficheiro pode ser maior; o browser reserva o espaço correto).
3.  **CSS:** Adicionar `aspect-ratio` aos contentores de imagens (ex: `.news-card-img { aspect-ratio: 16/9; }`) para reservar o espaço antes da imagem carregar.

## 🖼️ 3. Otimização de Imagens
**Ação:**
1.  **Logos:** Converter `images/logo.png` e `images/logo2.png` para **WebP** (ou garantir que são servidos no tamanho exato). Atualizar as referências no HTML.
2.  **Lazy Loading:** Adicionar `loading="lazy"` a todas as imagens que estão **abaixo da dobra** (listagens de notícias, imagens de contexto, banners no rodapé). **Não** aplicar lazy loading à imagem de capa do Hero ou ao logo do header (devem carregar imediatamente).
3.  **Imagens de Contexto:** Garantir que o Spatie Media Library está a gerar a conversão WebP (já configurado) e que as views usam a conversão correta (`getFirstMediaUrl('capa', 'capa-webp')`).

## 🎨 4. Eliminar CSS/JS Bloqueantes
**Ação:**
1.  **CSS Crítico:** No `layouts/app.blade.php`, mover o carregamento do `plugins.css` e `style.css` para o fim do `<body>` ou usar a técnica de carregamento assíncrono:
    ```html
    <link rel="stylesheet" href="{{ asset('css/plugins.css') }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ asset('css/plugins.css') }}"></noscript>
---
## Estado real da secao 4 (atualizado apos teste)

A tecnica media=print onload foi implementada e validada, mas gerava FOUC no site (pagina sem estilo por alguns segundos). Por decisao do cliente (Opcao A), a secao 4 foi **revertida** - o CSS voltou a ser <link> classico. As restantes secoes (2 CLS, 3 WebP/logos/lazy) mantem-se aplicadas.

Se o alojamento ativar compressao (mod_deflate/brotli no .htaccess), o mesmo ganho existe sem FOUC.

