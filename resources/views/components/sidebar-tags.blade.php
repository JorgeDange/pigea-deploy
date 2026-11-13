{{-- Widget: Tags Populares (editoria_cms.md FASE 1 — substitui hardcoded).
     Calcula as 6 tags mais frequentes a partir de news.tags (vírgula-separadas),
     restrito a notícias publicadas. Liga a pesquisa por tag. --}}
@php
    $contagem = [];

    foreach (\App\Models\News::published()->whereNotNull('tags')->pluck('tags') as $linha) {
        foreach (explode(',', (string) $linha) as $tag) {
            $tag = trim($tag);
            if ($tag === '') {
                continue;
            }
            $contagem[$tag] = ($contagem[$tag] ?? 0) + 1;
        }
    }

    arsort($contagem);
    $tags = array_slice(array_keys($contagem), 0, 6);
@endphp

@if (count($tags) > 0)
<div class="box-widget fl-wrap">
    <div class="widget-title">Tags Populares</div>
    <div class="box-widget-content">
        <div class="tags-widget">
            @foreach ($tags as $tag)
                <a href="{{ route('search.index', ['se' => $tag]) }}">{{ $tag }}</a>
            @endforeach
        </div>
    </div>
</div>
@endif
<!-- box-widget  end -->
