@props(['posicao' => 'topo', 'wrapper' => false])

@php
    // Banner publicitário estático no frontend (restrutura.md §3.5; fase_all.md
    // FASE 6). Renderiza o banner exibível mais recente da `posicao`
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
    // O slider (pub.md "Tipo Slider") fica para fase futura.
    $bannerPublicitario = \App\Models\Banner::ativos()
        ->posicao($posicao)
        ->orderByDesc('id')
        ->first();

    // imagem_url aceita 3 origens (restrutura.md §1 — upload OU link):
    //  - upload no admin  → "storage/banners/…" (via public/storage)
    //  - link externo     → "https://…" (usa-se diretamente)
    //  - assets do tema   → "images/…" (público)
    $urlImagem = $bannerPublicitario
        ? (str_starts_with($bannerPublicitario->imagem_url, 'http')
            ? $bannerPublicitario->imagem_url
            : asset($bannerPublicitario->imagem_url))
        : null;
@endphp

@if ($bannerPublicitario)
    @if ($wrapper)
        <div class="box-widget fl-wrap">
            <div class="box-widget-content">
    @endif
                <div class="banner-publicitario banner-publicitario--{{ $posicao }} fl-wrap">
                    <span style="display: block; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; opacity: 0.55; margin-bottom: 4px;">Publicidade</span>
                    @if ($bannerPublicitario->link_destino)
                        <a href="{{ $bannerPublicitario->link_destino }}" target="_blank" rel="noopener sponsored" title="{{ $bannerPublicitario->titulo }}">
                            <img src="{{ $urlImagem }}" alt="{{ $bannerPublicitario->titulo }}" loading="{{ $posicao === 'topo' ? 'eager' : 'lazy' }}" style="max-width: 100%; height: auto; display: block;">
                        </a>
                    @else
                        {{-- pub.md §5.5: sem link de destino o banner é só imagem (não clicável) --}}
                        <img src="{{ $urlImagem }}" alt="{{ $bannerPublicitario->titulo }}" loading="{{ $posicao === 'topo' ? 'eager' : 'lazy' }}" style="max-width: 100%; height: auto; display: block;">
                    @endif
                </div>
    @if ($wrapper)
            </div>
        </div>
    @endif
@endif
