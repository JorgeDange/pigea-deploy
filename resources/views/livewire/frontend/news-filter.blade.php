<div class="main-container fl-wrap fix-container-init">
    <div class="section-title">
        <h2>@if ($catNome) {{ 'Notícias de ' . $catNome }} @else Notícias de Geociências @endif</h2>
        <h4>@if ($catNome) As notícias mais recentes em {{ $catNome }} @else Fique a par das últimas informações @endif</h4>
        <div class="steader_opt steader_opt_abs" wire:ignore>
            <select name="filter" id="list" data-placeholder="Categoria" class="style-select no-search-select" wire:model.live="ordem">
                <option>Mais Recentes</option>
                <option>Mais Lidos</option>
                <option>Mais Vistos</option>
            </select>
        </div>
    </div>
    <div class="list-post-wrap">
        @forelse ($items as $item)
<!--list-post-->
        <div class="list-post fl-wrap">
            <div class="list-post-media">
                <a href="{{ route('news.show', $item->slug) }}">
                    <div class="bg-wrap">
                        <div class="bg" data-bg="{{ image_url($item->image, 'images/logo.png') }}"></div>
                    </div>
                </a>
                @if ($item->image_caption)
                <span class="post-media_title">{{ $item->image_caption }}</span>
                @endif
            </div>
            <div class="list-post-content">
                <a class="post-category-marker" href="{{ route('news.index', ['cat' => optional($item->category)->slug]) }}">{{ optional($item->category)->name }}</a>
                <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                <p>{{ $item->excerpt }}</p>
                <ul class="post-opt">
                    <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                </ul>
                @if ($item->author)
                <div class="author-link">
                    <a href="{{ route('news.index') }}"><img src="{{ $item->author->avatar ? asset($item->author->avatar) : asset('images/avatar/1.jpg') }}" alt="">  <span>{{ $item->author->name }}</span></a>
                </div>
                @endif
            </div>
        </div>
        <!--list-post end-->
        @empty
        <div class="empty-state">
            <i class="far fa-newspaper empty-state-icon"></i>
            <p class="empty-state-title">Ainda não há notícias publicadas.</p>
            <p class="empty-state-text">{{ $catNome ? 'Não há artigos publicados em ' . $catNome . '. Volta a consultar em breve.' : 'Em breve novas publicações.' }}</p>
        </div>
        @endforelse
    </div>
    <div class="clearfix"></div>
    {{-- Paginação funcional (Livewire) --}}
    @if ($items->hasPages())
        <nav class="pagination" aria-label="Paginação">
            @if ($items->onFirstPage())
                <span class="prevposts-link disabled" aria-disabled="true"><i class="fas fa-caret-left"></i></span>
            @else
                <a href="#" wire:click.prevent="previousPage('page')" class="prevposts-link"><i class="fas fa-caret-left"></i></a>
            @endif

            @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                @php $pagina = (int) $page; @endphp
                @if ($pagina === $items->currentPage())
                    <a href="#" class="current-page" aria-current="page">{{ str_pad($pagina, 2, '0', STR_PAD_LEFT) }}.</a>
                @else
                    <a href="{{ $items->url($pagina) }}" aria-label="Página {{ $pagina }}">{{ str_pad($pagina, 2, '0', STR_PAD_LEFT) }}.</a>
                @endif
            @endforeach

            @if ($items->hasMorePages())
                <a href="#" wire:click.prevent="nextPage('page')" class="nextposts-link"><i class="fas fa-caret-right"></i></a>
            @else
                <span class="nextposts-link disabled" aria-disabled="true"><i class="fas fa-caret-right"></i></span>
            @endif
        </nav>
    @endif
</div>
