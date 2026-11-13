# 🚀 Instrução Técnica — Inteligência Editorial e Correções da Home

## 🎯 Objetivo
Corrigir os bugs de ordenação e manutenção da Home, e implementar um sistema onde a equipa editorial pode escolher as notícias de destaque (Hero e Zonas de Destaque), mantendo um fallback automático.

## 1. Correção Urgente de Bugs (Fase 1)
- **Ordenação:** Alterar `$listNews` e `$widgetNews` de `orderBy('id')` (ASC) para `latest('published_at')` (DESC). A Home deve mostrar as notícias mais recentes, não as mais antigas.
- **Abas AJAX (Últimas Notícias):** Remover a lógica de `whitelist` de slugs no `FragmentController`. As abas (Mineração, Água, Ambiente, Investigação) devem fazer queries à base de dados usando o campo `categories.menu_group`, ordenando por `published_at` desc e limitando a 5 notícias por aba.
- **Tags Populares (Sidebar):** Remover as 6 tags hardcoded. O sistema deve calcular e mostrar as 6 tags mais frequentes (ou as que forem definidas como "destacadas" no admin).
- **Banners Estáticos:** Substituir os 2 banners estáticos (`images/all/banner.jpg`) pelo componente `<x-banner-publicitario posicao="...">` para que o admin possa vender esse espaço publicitário.

## 2. Controlo Editorial (Fase 2)
- **Base de Dados:** Adicionar coluna `destaque_editorial` (boolean, default false) na tabela `news`.
- **CMS (Formulário de Notícias):** Adicionar um toggle (switch) na barra lateral direita: **"Destacar na Página Inicial"**.
- **Home (Lógica de Exibição):**
  - **Hero:** `News::where('status', 'published')->where('destaque_editorial', true)->latest()->take(4)->get()`. Se não houver 4 marcadas, completar com as mais recentes.
  - **Zona Destaques (2 cards + swiper):** `News::where('status', 'published')->where('destaque_editorial', true)->skip(4)->take(5)->get()`. Se não houver suficientes, completar com recentes.
- **CMS (Tabela de Notícias):** Adicionar uma coluna visual (ex: uma estrela amarela) para identificar rapidamente quais notícias estão marcadas para a Home.

## 3. Fallback de Imagem (Fase 3)
- Se uma notícia não tiver imagem de capa (`image`), o frontend deve usar uma imagem padrão do PIGEA (ex: `images/logo.png` ou um placeholder cinzento com a logo).

## ✅ Critérios de Aceitação
- [ ] A Home mostra primeiro as notícias mais recentes, não as mais antigas.
- [ ] A aba "Água" mostra as últimas notícias da categoria "Água" (sem depender de slugs fixos).
- [ ] As Tags na sidebar atualizam automaticamente.
- [ ] No CMS, o editor pode marcar uma notícia como "Destacar".
- [ ] Se nenhuma notícia for marcada como destaque, o Hero funciona na mesma (mostra as recentes).
- [ ] Se uma notícia não tiver foto, aparece a logo do PIGEA.