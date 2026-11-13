# 👤 Instrução Técnica — Gestão de Autores, Avatares e Assinatura de Artigos

## 🎯 Objetivo
Permitir que as notícias tenham uma assinatura de autor no final do artigo, com foto e nome. O sistema deve suportar autores internos (editores/admins) e autores externos (convidados sem conta), garantindo uma imagem de fallback (logo PIGEA) caso não exista foto.

## 🗄️ 1. Base de Dados (Migrations)
- **Na tabela `users`:** Garantir que a coluna `avatar` (string, nullable) já existe para armazenar a foto do editor.
- **Na tabela `news`:** Adicionar/Verificar as colunas:
  - `author_id` (foreignId, nullable, references users).
  - `guest_author_name` (string, nullable) — para autores sem conta.
  - `guest_author_avatar` (string, nullable) — para a foto de autores sem conta.

## 🎛️ 2. O Painel Administrativo

### 2.1 Perfil do Utilizador (`/gestao-pigea/perfil`)
- Criar uma página de "O Meu Perfil" acessível a todos os Editores e Admins.
- Campo para atualizar o Nome Completo.
- Campo para Upload de Foto de Perfil (Avatar). O sistema deve converter para uma miniatura (ex: 150x150px) e guardar em `storage/app/public/avatars`.
- Adicionar o texto de ajuda: *"Recomendado: 150x150px. Formatos: JPG, PNG. Máx: 1MB."*

### 2.2 Formulário de Notícias (Criação/Edição)
- Adicionar um painel lateral chamado **"Autor do Artigo"**.
- **Opção A (Autor Interno):** Um select que lista todos os utilizadores do sistema (Editores/Admins). Por defeito, seleciona o utilizador logado.
- **Opção B (Convidado):** Se o autor não estiver na lista, o utilizador pode escrever o nome manualmente (campo de texto) e fazer upload de uma foto. 

### 2.3 Regra de Prioridade de Autor
Ao guardar a notícia:
1. Se o `author_id` estiver preenchido, usa o `user->name` e `user->avatar`.
2. Se `author_id` estiver vazio e `guest_author_name` estiver preenchido, usa o nome e a foto convidada.
3. Se ambos estiverem vazios, o autor é "Equipa Editorial PIGEA" e usa a logo padrão.

## 🖥️ 3. Exibição no Site Público (Frontend)
- No final do artigo (em `news/show.blade.php`), criar um bloco de "Sobre o Autor" com o seguinte layout:
  - **Imagem:** Avatar do autor (ou foto convidada).
  - **Fallback:** Se não houver nenhuma foto, renderizar a imagem `images/logo.png` (ou a logo definida no sistema).
  - **Texto:** "Escrito por **{Nome do Autor}**".
  - **Design:** Usar as classes CSS já existentes no tema (um cartão simples, com a foto à esquerda e o nome à direita).