{{-- Fragmento AJAX dos separadores da home (scripts.js injeta isto em #ajax-content) --}}
<div class="ajax-inner fl-wrap">
    <div class="list-post-wrap">
        @foreach ($items as $item)
        <!--list-post-->
        <div class="list-post fl-wrap">
            <div class="list-post-media">
                <a href="{{ route('news.show', $item->slug) }}">
                    <div class="bg-wrap">
                        <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                    </div>
                </a>
                <span class="post-media_title">{{ $item->image_caption }}</span>
            </div>
            <div class="list-post-content">
                <a class="post-category-marker" href="{{ route('news.index') }}">{{ $categoria }}</a>
                <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                <p>{{ $item->excerpt }}</p>
                <ul class="post-opt">
                    <li><i class="far fa-comments-alt"></i> {{ $item->comments_count }} </li>
                    <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                </ul>
                <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar) }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
            </div>
        </div>
        <!--list-post end-->
        @endforeach
    </div>
</div>
