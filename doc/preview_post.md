## 🖥️ Funcionalidade de Pré-visualização (Preview) de Notícias

### Objetivo
Permitir que a equipa interna (Editores/Admin) veja como a notícia ficará no site público antes de a publicar, evitando erros de formatação, links quebrados ou imagens erradas.

### Como Implementar no Livewire:
1. **Botão "Pré-visualizar" no Formulário:** Adicionar um botão ao lado do "Guardar Rascunho" e "Publicar".
2. **Modal de Preview (Livewire):** 
   - Ao clicar em "Pré-visualizar", abrir um modal em tela cheia (ou usar uma diretiva `wire:click` para renderizar uma view parcial).
   - O modal deve passar os dados atuais do formulário (título, imagem, conteúdo, links) para a view de preview.
3. **A View de Preview:**
   - Deve renderizar a página pública da notícia exatamente como ela é (cabeçalho do site, título, imagem de capa, texto formatado, links de vídeo/documentos).
   - No topo do Preview, deve haver uma barra de aviso: *"Modo de Pré-visualização - Esta notícia ainda não foi publicada"*.
4. **Sair do Preview:** Um botão "Fechar Pré-visualização" volta ao formulário de edição com os dados mantidos.