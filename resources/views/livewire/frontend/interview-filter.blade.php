<div class="main-container fl-wrap fix-container-init">
    <div class="section-title">
        <h3>Entrevistas:</h3>
        <div class="steader_opt steader_opt_abs" wire:ignore>
            <select name="filter" id="list" data-placeholder="Categoria" class="style-select no-search-select" wire:model.live="ordem">
                <option>Mais Recentes</option>
                <option>Mais Lidos</option>
                <option>Mais Vistos</option>
                <option>Mais Comentados</option>
            </select>
        </div>
    </div>
    <!--grid-post-wrap-->
    <div class="grid-post-wrap">
        <div class="row">
            @foreach ($items as $item)
<!--grid-post-item-->
            <div class="col-md-6">
                <div class="grid-post-item  bold_gpi  fl-wrap">
                    <div class="grid-post-media">
                        <a href="{{ route('news.show', $item->slug) }}" class="gpm_link">
                            <div class="bg-wrap">
                                <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                            </div>
                        </a>
                        <span class="post-media_title">{{ $item->image_caption }}</span>
                    </div>
                    <a class="post-category-marker purp-bg" href="{{ route('news.index') }}">{{ optional($item->category)->name }}</a>
                    <div class="grid-post-content">
                        <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                        <span class="post-date"><i class="far fa-clock"></i>  {{ $item->display_date }}</span>
                        <p>{{ $item->excerpt }}</p>
                    </div>
                    <div class="grid-post-footer">
                        <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar) }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                        <ul class="post-opt">
                            <li><i class="far fa-comments-alt"></i> {{ $item->comments_count }}</li>
                            <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--grid-post-item end-->
@endforeach
        </div>
    </div>
    <!--grid-post-wrap end-->
    <div class="clearfix"></div>
    <div class="load-more_btn"><i class="fal fa-undo"></i>Carregar mais</div>
    <!--pagination-->
    <div class="pagination">
        <a href="#" class="prevposts-link"><i class="fas fa-caret-left"></i></a>
        <a href="#">01.</a>
        <a href="#" class="current-page">02.</a>
        <a href="#">03.</a>
        <a href="#">04.</a>
        <a href="#" class="nextposts-link"><i class="fas fa-caret-right"></i></a>
    </div>
    <!--pagination end-->
</div>
