@extends('layouts.app')

@section('title', 'Investigação Científica | PIGEA')
@section('description', 'Estudos e publicações científicas sobre geologia, água, ambiente e mineração em Angola.')

@section('content')

                    <div class="breadcrumbs-header fl-wrap">
                        <div class="container">
                            <div class="breadcrumbs-header_url">
                                <a href="{{ route('home') }}">Início</a><span>Investigação</span>
                            </div>
                            <div class="scroll-down-wrap">
                                <div class="mousey">
                                    <div class="scroller"></div>
                                </div>
                                <span>Descubra mais</span>
                            </div>
                        </div>
                        <div class="pwh_bg"></div>
                    </div>
                    <!--section   -->
                    <section>
                        <div class="container">
                            <div class="main-container fl-wrap">
                                <div class="section-title">
                                    <h2>Investigação Científica</h2>
                                    <h4>Fique a par das últimas informações</h4>
                                    <div class="steader_opt steader_opt_abs">
                                        <select name="filter" id="list" data-placeholder="Categoria" class="style-select no-search-select">
                  <option>Todos os temas</option>
                  <option>Água</option>
                  <option>Mineração</option>
                  <option>Ambiente</option>
                  <option>Clima</option>
                </select>
                                    </div>
                                </div>
                                <!--grid-post-wrap-->
                                <div class="grid-post-wrap gallery-items">
                                    @foreach ($items as $item)
<!--grid-post-item-->
                                    <div class="gallery-item">
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
                        </div>
                    </section>
                    <!-- section end -->
                    <!-- section  -->
                    <div class="gray-bg ad-wrap fl-wrap">
                        <div class="content-banner-wrap">
                            <img src="{{ asset('images/all/banner.jpg') }}" class="respimg" alt="">
                        </div>
                    </div>
                    <!-- section end -->
                </div>
                
@endsection
