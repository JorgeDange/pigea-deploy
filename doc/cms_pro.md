# 🚀 Prompt de Implementação — Interface Profissional do CMS PIGEA

## 🎯 1. Objetivo
Construir a interface visual e a experiência de utilizador (UX) do Painel Administrativo (CMS). O painel deve ser moderno, limpo, organizado por contextos e focado na produtividade da equipa editorial. Deve também incluir regras claras de dimensões de imagem e fluxos de segurança.

## 🏗️ 2. Estrutura Global do Layout
O layout (desktop) deve ser dividido em três zonas fixas:
1.  **Sidebar (Esquerda):** Agrupada por categorias com cabeçalhos pequenos (ex: "CONTEÚDO", "GESTÃO"). Deve usar as cores e estilos já definidos no sistema atual (variáveis CSS existentes).
2.  **Topbar (Topo):** Deve conter:
    - Botão para abrir/fechar sidebar em mobile.
    - À direita: Botão "Ver Site" (abre o frontend), Ícone do Sino (Notificações), Avatar e Nome do Utilizador, e Ícone de Logout.
3.  **Área Principal (Main):** Contém o Breadcrumb (ex: `Admin > Notícias > Nova`) e os cartões de conteúdo. Deve herdar o fundo e as bordas definidas no `dashboard.css` atual.

## 🗂️ 3. Estrutura da Barra Lateral (Sidebar)
A barra lateral deve esconder menus de acordo com o papel do utilizador (Spatie Permission).
- **Grupo: PRINCIPAL**
  - 📊 Dashboard
- **Grupo: PRODUÇÃO**
  - 📰 Notícias
- **Grupo: DIRETÓRIO**
  - 🏢 Empresas
  - 👔 Profissionais
  - 📁 Documentos
- **Grupo: COMERCIAL**
  - 📢 Publicidade (Banners)
- **Grupo: SISTEMA (Apenas visível para `admin`)**
  - 👥 Utilizadores
  - ⚙️ Configurações

## 🖥️ 4. Ecrãs Principais

### 4.1 Dashboard (Visão Geral)
- **Cartões de Estatísticas (Topo):** Notícias Publicadas, Empresas Pendentes, Visitas no Site, Banners Ativos. *(Os cartões de pendentes devem ter um link direto para a página de aprovação).*
- **Gráfico Central:** Linha de visitas dos últimos 7 dias (Chart.js).
- **Ações Rápidas:** Botão grande "+ Nova Notícia".
- **Atividade Recente:** Lista (ex: "Editor João publicou X há 10 min").

### 4.2 O Editor de Notícias (O Ecrã Mais Importante)
O formulário deve ser dividido em **duas colunas**:
- **Coluna Esquerda (70% - Conteúdo):**
  - Campo de Título e Subtítulo.
  - **Editor Rico (TinyMCE):** Com botões para Negrito, Itálico, Listas, Inserir Link, Inserir Imagem, e os **Estilos Especiais** (Drop Cap, Blockquote, Duas Colunas).
- **Coluna Direita (30% - Opções e Mídia):**
  - **Botões de Ação:** "Pré-visualizar" (Abre modal), "Guardar Rascunho", "Publicar".
  - **Categoria:** Select para Mineração, Geociências, etc.
  - **Imagem de Capa:** Campo de Upload ou Link.
  - **Links Extras:** Vídeo (YouTube) e Documento (PDF).

### 4.3 Gestão de Diretório (Aprovações)
- Ecrãs para Empresas e Profissionais.
- Tabela com Filtros (Pesquisa, Estado).
- Ações na linha: Botão de Aprovar (✓), Botão de Rejeitar (✗) e Ícone de Lixo (Apagar). *As cores destes botões devem seguir o padrão de "sucesso" e "perigo" já definido no CSS global do sistema.*

## 🖼️ 5. Aviso de Dimensões de Imagem (Regra Crucial)
Sempre que houver um campo de upload de imagem, deve haver um texto de ajuda (abaixo do campo) a indicar a dimensão exata recomendada.

- **Notícias (Imagem de Capa):** Texto: *"Recomendado: 1200x675px (16:9). Formatos: JPG, PNG, WEBP. Máx: 2MB."*
- **Notícias (Imagens de Contexto):** Texto: *"Recomendado: 800px de largura. Formatos: JPG, PNG, WEBP."*
- **Banners de Publicidade:** Texto: *"Dimensão exata: 970x250px (Leaderboard). Formatos: JPG, PNG. Máx: 4MB."*
- **Logótipo (Configurações):** Texto: *"Recomendado: 400x100px (Fundo transparente PNG)."*

## ⚙️ 6. Funcionalidades Técnicas (Livewire e Laravel)
1.  **Autosave:** Implementar `wire:model.live.debounce.5s` no editor de texto para gravar automaticamente o rascunho na base de dados enquanto o utilizador escreve.
2.  **Preview Modal:** O botão "Pré-visualizar" deve abrir um modal `wire:click="abrirPreview"`. Este modal deve renderizar a view pública (`news.show`) passando os dados atuais do formulário sem gravar na base de dados.
3.  **Segurança:** A rota base do painel deve ler a variável `ADMIN_PATH` do `.env`. O utilizador não logado ou sem permissão recebe erro 403.

## 🎨 7. Design System (Integração com o Existente)
- **REGRA DE OURO:** Não criar novas folhas de estilo ou definir novas cores hexadecimais. A IA deve reutilizar **exclusivamente** as classes, variáveis e estilos já presentes no ficheiro `dashboard.css` e no `color.css` do projeto atual.
- **Fontes:** Usar as classes tipográficas já definidas (ex: Playfair Display para títulos, Poppins para UI).
- **Componentes:** Reutilizar os cartões (`.card`), botões (`.btn-primary`, `.btn-ghost`), inputs e tabelas que já existem no sistema.