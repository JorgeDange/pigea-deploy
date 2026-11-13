# 📄 Prompt de Implementação — Reestruturação Final, Segurança e Performance do PIGEA

## 🎯 1. Missão
Executar a limpeza final do projeto, implementar a arquitetura definitiva (Apenas Notícias + Diretório), blindar a segurança do Painel Administrativo e otimizar radicalmente o desempenho do frontend público (com Skeletons e otimização de imagens).

---

## 🗑️ 2. Limpeza e Reestruturação do Sistema
O projeto mudou. Empresas e Profissionais não têm login. Toda a gestão é da equipa interna.
1. **Apagar:** Rotas, Controllers, Models e Views de "Vagas", "Candidaturas", "Painel da Empresa" e "CMS de Empresa".
2. **Base de Dados:**
   - Criar tabela `companies` (Nome, NIF, Contactos, Áreas de Atuação [JSON], Status).
   - Criar tabela `professionals` (Nome, Formação, Contactos, Áreas de Atuação [JSON], Status).
   - Criar tabela `directory_documents` (Título, Descrição, Link, Áreas de Atuação [JSON]).
   - Criar tabela `banners` (Título, Imagem/Link, Link Destino, Posição, Datas, Ativo).
3. **Formulários Públicos:**
   - Criar `/registo-empresa` e `/registo-profissional`.
   - Regra: Ao submeter, o registo fica com `status = 'pendente'`. Mostrar mensagem "Aguarde aprovação".
4. **Gestão no Admin:**
   - Adicionar tabelas no painel `/admin` para Aprovar/Rejeitar Empresas e Profissionais.
   - Adicionar gestão de Banners e Documentos do Diretório.

---

## 🔐 3. Segurança do Painel Administrativo (Blindagem)
O painel `/admin` deve ser inacessível para o público geral.

1. **Rota Oculta:** Mudar o prefixo do painel de `/admin` para `/gestao-pigea`. (Não haverá links públicos para este URL).
2. **Middlewares de Autenticação:** Apenas utilizadores com a role `admin` ou `editor` podem aceder.
3. **Rate Limiting (Brute Force):** Configurar o Fortify/Login para bloquear o IP após 5 tentativas falhadas num minuto.
4. **Sessões Seguras:** No `.env` de produção, garantir `SESSION_SECURE_COOKIE=true` (apenas HTTPS) e `SESSION_ENCRYPT=true`.
5. **Audit Log:** Instalar `spatie/laravel-activitylog` para registar qualquer ação de criação, edição ou exclusão no painel.
6. **2FA (Two-Factor Authentication):** Ativar o suporte 2FA no Fortify. A equipa será encorajada a ativar na sua conta.

---

## ⚡ 4. Performance e Otimização do Frontend
O site deve ser extremamente rápido, mesmo em conexões móveis (3G/4G).

1. **Skeleton Loading:** 
   - Em todas as páginas que carregam dados via Livewire (Notícias, Diretório, Filtros), substituir o texto "Loading..." por *Skeletons* (blocos cinza animados que imitam o formato do conteúdo).
   - Usar `wire:loading` com classes CSS de esqueleto no lugar de spinners.
2. **Lazy Loading (Imagens):**
   - Garantir que TODAS as imagens no frontend têm o atributo `loading="lazy"` (nativo do HTML) e `decoding="async"`.
   - Usar o Spatie Media Library (já instalado) para gerar as versões WebP das imagens carregadas pela equipa.
3. **Otimização do CSS e JS:**
   - Não carregar bibliotecas inteiras (ex: jQuery) se não forem necessárias na página específica.
   - Usar o Vite para fazer o *minify* e *versionamento* (cache busting) do CSS e JS de produção.
4. **Cache de Consultas (Query Cache):**
   - Cachear as listagens de notícias, empresas e documentos do diretório por curtos períodos (ex: 5 minutos) usando `Cache::remember()`. Ao editar no admin, limpar a cache automaticamente.
5. **Paginação e Limites:**
   - Nunca carregar todos os registos de uma vez. Usar paginação (ex: 10 notícias por página) ou *Lazy Loading* (botão "Carregar mais").
6. **Otimização de Vídeos:**
   - Vídeos do YouTube/Vimeo devem usar o padrão de "fachada" (imagem de capa + botão play). O iframe pesado do vídeo só é carregado quando o utilizador clica no play.

---

## 💻 5. O que a IA deve gerar agora:
1. Executar a limpeza da base de dados e código.
2. Criar as novas Migrations e Models.
3. Configurar as rotas de login seguras (`/gestao-pigea`).
4. Aplicar as diretivas de performance no layout (Blade e Livewire), especialmente os Skeletons e Lazy Loading.