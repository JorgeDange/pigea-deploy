# Instruções Definitivas — Reestruturação do PIGEA (Portal de Notícias e Diretório)

## 1. Novo Paradigma do Sistema
- O sistema é focado apenas em **Notícias** e **Diretório de Empresas/Profissionais**.
- **Não há login para empresas ou profissionais.** Eles apenas submetem um formulário público.
- A gestão de todo o conteúdo é feita **exclusivamente** pela equipa interna (Admin/Editor) via painel `/admin`.
- **Mídias:** Imagens suportam Upload e Link. Vídeos e Documentos suportam **apenas Link**.

## 2. Limpeza de Código e Estrutura
- Apagar rotas, controllers, modelos e views relacionados com "Painel da Empresa", "Vagas", "Candidaturas" e "Publicação por Empresas".
- O sistema de roles (Spatie) deve conter apenas: `admin`, `editor` (equipa interna). Empresas e Profissionais não são utilizadores do sistema de login.

## 3. Base de Dados e Modelos
Criar/Atualizar as seguintes tabelas:

### 3.1 `companies` (Diretório de Empresas)
- `id`, `nome_empresa`, `nif`, `contactos` (string), `areas_atuacao` (json), `redes_sociais` (json - opcional), `status` (enum: 'pendente', 'aprovado', 'rejeitado'), `timestamps`.

### 3.2 `professionals` (Diretório de Profissionais)
- `id`, `nome_completo`, `formacao_academica`, `contactos` (string), `areas_atuacao` (json), `status` (enum: 'pendente', 'aprovado', 'rejeitado'), `timestamps`.

### 3.3 `directory_documents` (Artigos/Documentos do Diretório)
- `id`, `titulo`, `descricao` (curta), `tipo` (enum: 'artigo', 'documento', 'lei'), `link_download` (string), `areas_atuacao` (json), `timestamps`.

### 3.4 `news` (Notícias - CMS Interna)
- Manter a estrutura atual (título, conteúdo, autor, categoria).
- Implementar **Spatie Media Library** para coleções `capa` e `contexto` (com conversão WebP).
- Adicionar campos `video_link` (string) e `documento_link` (string) para links externos.

### 3.5 `banners` (Publicidade)
- `id`, `titulo`, `imagem_url` (ou Spatie Media), `link_destino`, `posicao` (ex: 'topo', 'sidebar', 'rodape'), `data_inicio`, `data_fim`, `ativo` (boolean).

## 4. Formulários Públicos (Livewire)
Criar os seguintes componentes com validação e mensagem de sucesso:

### 4.1 Registo de Empresa (`/registo-empresa`)
- Campos: Nome da Empresa, NIF, Contacto, Áreas de Atuação (Checkboxes - JSON), Redes Sociais (opcional).
- Ao submeter: Guardar com `status = 'pendente'`. Mostrar mensagem "Registo submetido com sucesso. Aguarde a aprovação da nossa equipa."

### 4.2 Registo de Profissional (`/registo-profissional`)
- Campos: Nome Completo, Formação Académica, Contacto, Áreas de Atuação (Checkboxes - JSON).
- Ao submeter: Guardar com `status = 'pendente'`. Mostrar mensagem de sucesso.

## 5. CMS Admin (Equipa PIGEA)
No painel `/admin`, criar as seguintes secções:
1. **Dashboard:** Contadores (Notícias, Empresas pendentes, Profissionais pendentes).
2. **Notícias:** CRUD completo. Upload de capa (Spatie), upload de imagens de contexto, campos para link de vídeo e documento.
3. **Diretório de Empresas:** Tabela com botões "Aprovar" e "Rejeitar". Ao aprovar, a empresa fica visível na página pública.
4. **Diretório de Profissionais:** Tabela com botões "Aprovar" e "Rejeitar".
5. **Documentos/Artigos:** CRUD para adicionar links de PDFs e artigos.
6. **Publicidade:** CRUD de banners (com upload ou link, e datas de início/fim).
7. **Utilizadores:** Gerir a equipa interna.

## 6. Frontend Público
- **Header:** Atualizar o menu de acordo com a imagem do cliente (Início, Notícias, Mineração, Petróleo e Gás, Geociências, Academia, Oportunidades, Directório, Sobre Nós).
- **Página do Diretório (`/directorio`):**
  - Separar em duas abas: "Empresas" e "Artigos/Documentos".
  - **Aba Empresas:** Listar apenas as empresas com `status = 'aprovado'`. Mostrar Nome, Áreas de Atuação, Contactos (apenas os públicos) e Redes Sociais. Ocultar NIF.
  - **Aba Artigos:** Listar os documentos carregados pela equipa, com botão "Download".
  - Adicionar barra de pesquisa para filtrar por nome ou área de atuação.

## 7. Ações Imediatas para a IA
1. Eliminar modelos e controllers de "Vagas", "Candidaturas" e "Painel de Empresa".
2. Executar as migrations para as novas tabelas (`companies`, `professionals`, `directory_documents`, `banners`).
3. Criar os componentes Livewire para os dois formulários públicos.
4. Atualizar o menu público.
5. Implementar o Spatie Media Library apenas no modelo `News`.