# 🗑️ Instrução Técnica — Remoção Completa do Sistema de Comentários

## 1. Frontend (Público)
- **Remover a secção de comentários** da página de visualização da notícia (`resources/views/news/show.blade.php` ou equivalente).
- Apagar o componente Livewire de comentários (ex: `app/Livewire/Frontend/Comments.php` e a sua view).
- Remover qualquer botão ou link que diga "Deixar comentário" ou que mostre a contagem de comentários nos cartões de notícias.

## 2. Backend e Base de Dados
- **Apagar a migração e a tabela `comments`** da base de dados (executar um `drop` se a tabela já existir no ambiente local).
- Apagar o Model `Comment` (`app/Models/Comment.php`) e remover qualquer relação `comments()` que exista no Model `News`.
- Remover as rotas (`routes/web.php`) e os métodos nos Controllers que geriam a submissão ou listagem de comentários.

## 3. Painel Administrativo (CMS)
- Remover do Painel Admin qualquer menu, tabela ou funcionalidade de "Moderação de Comentários".
- Nas estatísticas do Dashboard, remover o cartão ou contador de "Comentários".

## 4. Base de Dados (Campos Adicionais)
- Se a tabela `news` tiver a coluna `comments_count` (para mostrar o número de comentários ao lado da notícia), **remover essa coluna** da migração ou simplesmente deixar de a usar no frontend e no admin.
- O mesmo para a página de listagem de notícias: remover o ícone de balão de comentários e o número.