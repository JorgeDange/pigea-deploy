@props(['posicao' => 'topo', 'wrapper' => false])

@php
    // Banner publicitário estático no frontend (restrutura.md §3.5; fase_all.md
    // FASE 6). Renderiza o(s) banner(s) exibível(eis) da `posicao`
    // (pub.md §6.2 — desempate pelo mais recente) entre os do scope
    // `Banner::ativos()` — ativo e dentro do período data_inicio..data_fim.
    //
    // Regras do pub.md mantidas na renderização (§7):
    //  - rótulo discreto "Publicidade";
    //  - `alt` com o título do banner (acessibilidade);
    //  - lazy loading e imagem responsiva sem distorção;
    //  - §5.5: sem `link_destino` → imagem NÃO clicável.
    //
    // `wrapper` (usado nas sidebars): embrulha o banner no box-widget do tema.
    // Sem banner exibível, o componente não gera HTML.
    //
    // FASE 18 — posição `lateral` (vertical): roda até 4 banners ativos
    // (os mais recentes) num slider com troca automática a cada 5s
    // (banner-lateral.js, mesmo padrão do destaque.js da FASE 15).
    // Com 1 só banner renderiza estático (comportamento das outras posições).
    // O slider (pub.md "Tipo Slider") para `topo`/`sidebar`/`rodape` fica
    // para fase futura.
    $bannersPublicitarios = \App\Models\Banner::ativos()
        ->posicao($posicao)
        ->orderByDesc('id')
        ->limit($posicao === 'lateral' ? 4 : 1)
        ->get();

    $rotacaoLateral = $posicao === 'lateral' && $bannersPublicitarios->count() > 1;

    // imagem_url aceita 3 origens (restrutura.md §1 — upload OU link):
    //  - upload no admin  → "storage/banners/…" (via public/storage)
    //  - link externo     → "https://…" (usa-se diretamente)
    //  - assets do tema   → "images/…" (público)
    $urlImagem = fn ($bannerPublicitario) => str_starts_with($bannerPublicitario->imagem_url, 'http')
        ? $bannerPublicitario->imagem_url
        : asset($bannerPublicitario->imagem_url);
@endphp

@if ($bannersPublicitarios->isNotEmpty())
    @if ($wrapper)
        <div class="box-widget fl-wrap">
            <div class="box-widget-content">
    @endif
                <div class="banner-publicitario banner-publicitario--{{ $posicao }} fl-wrap">
                    <span style="display: block; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; opacity: 0.55; margin-bottom: 4px;">Publicidade</span>
    @if ($rotacaoLateral)
                    {{-- FASE 18 — rotação 2..4 slides (autoplay 5s via banner-lateral.js).
                         Slides com `eager`: pré-carregam todos para a troca não fazer flash. --}}
                    <div class="swiper-container banner-lateral-slider" data-autoplay="5000">
                        <div class="swiper-wrapper">
                            @foreach ($bannersPublicitarios as $bannerPublicitario)
                            <div class="swiper-slide">
                                @if ($bannerPublicitario->link_destino)
                                <a href="{{ $bannerPublicitario->link_destino }}" target="_blank" rel="noopener sponsored" title="{{ $bannerPublicitario->titulo }}">
                                    <img src="{{ $urlImagem($bannerPublicitario) }}" alt="{{ $bannerPublicitario->titulo }}" loading="eager" style="max-width: 100%; height: auto; display: block;">
                                </a>
                                @else
                                {{-- pub.md §5.5: sem link de destino o banner é só imagem (não clicável) --}}
                                <img src="{{ $urlImagem($bannerPublicitario) }}" alt="{{ $bannerPublicitario->titulo }}" loading="eager" style="max-width: 100%; height: auto; display: block;">
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
    @else
                    @php $bannerPublicitario = $bannersPublicitarios->first(); @endphp
                    @if ($bannerPublicitario->link_destino)
                        <a href="{{ $bannerPublicitario->link_destino }}" target="_blank" rel="noopener sponsored" title="{{ $bannerPublicitario->titulo }}">
                            <img src="{{ $urlImagem($bannerPublicitario) }}" alt="{{ $bannerPublicitario->titulo }}" loading="{{ $posicao === 'topo' ? 'eager' : 'lazy' }}" style="max-width: 100%; height: auto; display: block;">
                        </a>
                    @else
                        {{-- pub.md §5.5: sem link de destino o banner é só imagem (não clicável) --}}
                        <img src="{{ $urlImagem($bannerPublicitario) }}" alt="{{ $bannerPublicitario->titulo }}" loading="{{ $posicao === 'topo' ? 'eager' : 'lazy' }}" style="max-width: 100%; height: auto; display: block;">
                    @endif
    @endif
                </div>
    @if ($wrapper)
            </div>
        </div>
    @endif
@endif
