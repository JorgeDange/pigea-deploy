# 📄 Prompt — Implementação do Header e Mega Menu Estruturado

## 🎯 1. Missão
Substituir o menu de navegação atual pelo novo **Mega Menu** estruturado, conforme as imagens de referência fornecidas pelo cliente. O menu deve ser idêntico visualmente (cores, ícones, grelha) e funcional.

## 🎨 2. Estrutura Visual do Header (Topo)
- **Fundo:** Branco com uma linha fina cinzenta na base.
- **Logotipo (Esquerda):** Texto "PIGEA" em azul (`#0056b3` ou similar).
- **Badge (Direita):** Uma pílula verde clara com o texto "Portal de Geociências" em verde escuro.
- **Menu Principal:** Centralizado, com os 9 itens (Início, Notícias, Mineração, Petróleo e Gás, Geociências, Academia, Oportunidades, Diretório, Sobre Nós). Cada item tem um ícone de seta (`<i class="fa fa-chevron-down"></i>`) alinhado à direita.
- **Item Ativo:** O item da página atual deve ter fundo azul claro (`#e6f0ff`) e texto azul escuro.

## 🧩 3. Estrutura do Mega Menu (Painel Aberto)
- Quando um item do menu é clicado/hover, abre um painel branco (com sombra suave) logo abaixo.
- O painel tem a largura total da barra de menu.
- **Cabeçalho do Painel:** Título do menu (ex: "Mineração") à esquerda, e o link "Subcategorias" à direita (texto cinza pequeno).
- **Grelha de Links (3 colunas):** Abaixo do título, os links são organizados em grelha.
- **Estilo do Link na Grelha:** Fundo cinza muito claro, texto escuro, com um ícone de seta (`>`) à esquerda. O link ocupa 100% da sua célula.

## 💾 4. Base de Dados (Categorias de Notícias)
Para que o menu funcione e o Admin possa associar notícias a estas categorias, precisamos de criar/ajustar a tabela `categories` (ou semelhante).

- A tabela deve ter: `id`, `name`, `slug`, `parent_id` (nullable), `menu_group` (para associar ao grupo: 'noticias', 'mineracao', etc.), `order` (para ordenar os links na grelha).
- Executar um Seeder para popular os dados exatos das imagens:
  - **Mineração:** Prospecção e pesquisa, Exploração mineira, Avaliação de jazidas, Processamento mineral, Ambiente mineiro.
  - **Geociências:** Geologia, Geofísica, Geodesia e GNSS, Topografia e cartografia, SIG e geoprocessamento, Hidrogeologia, Geotecnia e ambiente.
  - *(Repetir para todas as outras secções conforme as imagens)*.

## ⚙️ 5. Ações Técnicas para a IA:
1. Criar o CSS para o Mega Menu, garantindo que o painel não quebre em ecrãs menores (mobile deve ter um comportamento de acordeão).
2. Atualizar o componente `header.blade.php` e `main-menu.blade.php` para incluir a nova estrutura.
3. Criar a migração e o seeder para as categorias do menu.
4. Garantir que ao clicar num link do Mega Menu (ex: "Prospecção e pesquisa"), o utilizador é levado para a página de Notícias já filtrada por essa categoria (ex: `/noticias?categoria=prospeccao-e-pesquisa`).