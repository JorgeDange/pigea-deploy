{{-- Fase 1 (cms_v.md) — parcial ÚNICO do corpo de artigo.
     Usado pelo site público (news/show.blade.php), pela pré-visualização
     do editor (endpoint /noticias/pre-visualizar) e como referência do
     TinyMCE (content_css + body_class). O conteúdo é sanitizado ao guardar
     (Fase 3) — impressões em HTML não confiado são da responsabilidade
     do fluxo de gravação (NewsEditorController::guardarConteudo). --}}
<div class="article-body">{!! $content !!}</div>
