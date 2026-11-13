{{-- FASE 15 — Destaque publicitário rotativo NO WIDGET «Escolha da Equipa»
     (home e /projetos/{slug} — mesmo componente). Rotaciona a cada 4s
     (destaque.js), largura/altura exatamente as do widget atual.

     Regras:
      - escopo «site»     → aparece NAS DUAS colocações (home + /projetos)
      - escopo «inicio»   → só na home
      - escopo «sidebar»  → só na página de projeto (interior)
      - sem destaque ativo e válido → o widget segue a mostrar as notícias
        (comportamento original, sem autoplay).
      - altura/estrutura (`.single-grid-slider slider_widget`, `.grid-post-media
        gpm_sing`, `.slider_widget_title`, `.sgs-pagination`) — tudo como era.
      - o Swiper do tema não pega no modo banner (classe `destaque-slider`
        fora do seletor dele) — o autoplay 4s é aplicado pelo destaque.js
        DEPOIS do initGmag.
      - Acessibilidade: a imagem é background CSS (padrão do tema), pelo que
        cada slide tem `role="img"` + `aria-label` com o título (leitor de
        ecrã não vê CSS background). O link de publicidade leva
        `rel="noopener sponsored"`.
--}}
@props(['contexto' => 'inicio']) {{-- 'inicio' (home) | 'sidebar' (/projetos) --}}

@php
    use App\Models\Destaque;

    try {
        $destaques = Destaque::ativos()
            ->escopo($contexto)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();
    } catch (\Throwable $e) {
        $destaques = collect(); // durante migrações o componente cai no editorial
    }

    $modoBanner = $destaques->isNotEmpty();
    $urlImagem = fn ($destaque) => str_starts_with($destaque->imagem_url, 'http')
        ? $destaque->imagem_url
        : asset($destaque->imagem_url);
@endphp
<div class="single-grid-slider slider_widget">
    <div class="slider_widget_title">Escolha da Equipa</div>
    <div class="{{ $modoBanner ? 'swiper-container destaque-slider' : 'swiper-container' }}" @if ($modoBanner) data-autoplay="4000" @endif>
        <div class="swiper-wrapper">
            @if ($modoBanner)
                @foreach ($destaques as $destaque)
<!-- destaque-banner -->
                <div class="swiper-slide">
                    <div class="{{ $loop->index === 0 ? 'grid-post-item     fl-wrap' : 'grid-post-item  bold_gpi  fl-wrap' }}">
                        <div class="grid-post-media gpm_sing destaque-equipa-slide" role="img" aria-label="Publicidade: {{ $destaque->titulo }}" title="{{ $destaque->titulo }}">
                            @if ($destaque->link_destino)
                                <a class="destaque-link" href="{{ $destaque->link_destino }}" target="_blank" rel="noopener sponsored" title="{{ $destaque->titulo }}" aria-label="Publicidade: {{ $destaque->titulo }} (abre numa nova janela)">
                                    <div class="bg-wrap">
                                        <div class="bg" data-bg="{{ $urlImagem($destaque) }}"></div>
                                        <div class="overlay"></div>
                                    </div>
                                </a>
                            @else
                                <div class="bg-wrap">
                                    <div class="bg" data-bg="{{ $urlImagem($destaque) }}"></div>
                                    <div class="overlay"></div>
                                </div>
                            @endif
                            <span class="destaque-tag">Publicidade</span>
                        </div>
                    </div>
                </div>
<!-- destaque-banner end -->
                @endforeach
            @else
                @foreach ($listNews->take(3) as $item)
<!-- swiper-slide-->
                <div class="swiper-slide">
                    <div class="{{ $loop->index === 0 ? 'grid-post-item     fl-wrap' : 'grid-post-item  bold_gpi  fl-wrap' }}">
                        <div class="grid-post-media gpm_sing">
                            <div class="bg-wrap">
                                <div class="bg" data-bg="{{ image_url($item->image, 'images/logo.png') }}"></div>
                                <div class="overlay"></div>
                            </div>
                            <div class="grid-post-media_title">
                                <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                <h4><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                <span class="video-date"><i class="far fa-clock"></i>{{ $item->display_date }}</span>
                                <ul class="post-opt">
                                    <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
<!-- swiper-slide end-->
                @endforeach
            @endif
        </div>
        <div class="sgs-pagination sgs_hor "></div>
    </div>
</div>
