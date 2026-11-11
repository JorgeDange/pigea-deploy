@extends('layouts.app')

@section('title', 'Notícia | PIGEA')
@section('description', 'Notícia de geociências no PIGEA.')

@section('content')

                    <div class="breadcrumbs-header fl-wrap">
                        <div class="container">
                            <div class="breadcrumbs-header_url">
                                <a href="{{ route('home') }}">Início</a><span>Notícias</span>
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
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="main-container fl-wrap fix-container-init">
                                        <!-- single-post-header  -->
                                        <div class="single-post-header fl-wrap">
                                            <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($post->category)->name ?? 'Geral' }}</a>
                                            <div class="clearfix"></div>
                                            <h1>{{ $post->title }}</h1>
                                            <h4>Informação actualizada e verificada pela equipa PIGEA.</h4>
                                            <div class="clearfix"></div>
                                            <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($post->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($post->author)->name }}</span></a></div>
                                            <span class="post-date"><i class="far fa-clock"></i> {{ $post->display_date }}</span>
                                            <ul class="post-opt">
                                                <li><i class="fal fa-eye"></i>  {{ $post->views_count }} </li>
                                            </ul>
                                        </div>
                                        <!-- single-post-header end   -->
                                        <!-- single-post-media   -->
                                        <div class="single-post-media fl-wrap">
                                            <div class="single-slider-wrap fl-wrap">
                                                <div class="single-slider fl-wrap">
                                                    <div class="swiper-container">
                                                        <div class="swiper-wrapper lightgallery">
                                                            <!-- swiper-slide   -->
                                                            <div class="swiper-slide hov_zoom">
                                                                <img src="{{ asset('images/all/20.jpg') }}" alt="">
                                                                <a href="{{ asset('images/all/20.jpg') }}" class="box-media-zoom   popup-image"><i class="fas fa-search"></i></a>
                                                                <span class="post-media_title pmd_vis">© Image Copyrights Title</span>
                                                            </div>
                                                            <!-- swiper-slide end   -->
                                                            <!-- swiper-slide   -->
                                                            <div class="swiper-slide hov_zoom">
                                                                <img src="{{ asset('images/all/13.jpg') }}" alt="">
                                                                <a href="{{ asset('images/all/13.jpg') }}" class="box-media-zoom   popup-image"><i class="fas fa-search"></i></a>
                                                                <span class="post-media_title pmd_vis">© Image Copyrights Title</span>
                                                            </div>
                                                            <!-- swiper-slide end   -->
                                                            <!-- swiper-slide   -->
                                                            <div class="swiper-slide hov_zoom">
                                                                <img src="{{ asset('images/all/15.jpg') }}" alt="">
                                                                <a href="{{ asset('images/all/15.jpg') }}" class="box-media-zoom   popup-image"><i class="fas fa-search"></i></a>
                                                                <span class="post-media_title pmd_vis">© Image Copyrights Title</span>
                                                            </div>
                                                            <!-- swiper-slide end   -->
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ss-slider-controls2">
                                                    <div class="ss-slider-pagination pag-style"></div>
                                                </div>
                                                <div class="ss-slider-cont ss-slider-cont-prev"><i class="fas fa-caret-left"></i></div>
                                                <div class="ss-slider-cont ss-slider-cont-next"><i class="fas fa-caret-right"></i></div>
                                            </div>
                                        </div>
                                        <!-- single-post-media end   -->
                                        <!-- single-post-content   -->
                                        <div class="single-post-content spc_column fl-wrap">
                                            <div class="single-post-content_column">
                                                <div class="share-holder ver-share fl-wrap">
                                                    <div class="share-title">Partilhar <br> Article:</div>
                                                    <div class="share-container  isShare"></div>
                                                </div>
                                            </div>
                                            <div class="fs-wrap smpar fl-wrap">
                                                <div class="fontSize"><span class="fs_title">Tamanho da letra: </span><input type="text" class="rage-slider" data-step="1" data-min="12" data-max="15" value="12"></div>
                                                <a class="print-btn" href="javascript:window.print()" data-microtip-position="bottom"><span>Imprimir</span><i class="fal fa-print"></i></a>
                                            </div>
                                            <div class="clearfix"></div>
                                            {!! $post->content !!}
                                            <div class="single-post-footer fl-wrap">
                                                <div class="post-single-tags">
                                                    <span class="tags-title"><i class="fas fa-tag"></i> Tags : </span>
                                                    <div class="tags-widget">
                                                        <a href="#">Internacional</a>
                                                        <a href="#">Água</a>
                                                        <a href="#">Ambiente</a>
                                                        <a href="#">Lifestyle</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- single-post-nav"   -->
                                            <div class="single-post-nav fl-wrap">
                                                @if ($prev)

<a href="{{ route('news.show', $prev->slug) }}" class="single-post-nav_prev spn_box">
    
<div class="spn_box_img">
    
    <img src="{{ asset($prev->image ?? 'images/all/2.jpg') }}" class="respimg" alt="">
    
</div>
    
<div class="spn-box-content">
    
    <span class="spn-box-content_subtitle"><i class="fas fa-caret-left"></i> Prev Post</span>
    
    <span class="spn-box-content_title">{{ $prev->title }}</span>
    
</div>

</a>
@endif
@if ($next)

<a href="{{ route('news.show', $next->slug) }}" class="single-post-nav_next spn_box">
    
<div class="spn_box_img">
    
    <img src="{{ asset($next->image ?? 'images/all/20.jpg') }}" class="respimg" alt="">
    
</div>
    
<div class="spn-box-content">
    
    <span class="spn-box-content_subtitle">Next Post <i class="fas fa-caret-right"></i></span>
    
    <span class="spn-box-content_title">{{ $next->title }}</span>
    
</div>

</a>
@endif
                                            </div>
                                            <!-- single-post-nav"  end   -->
                                        </div>
                                        <!-- single-post-content  end   -->
                                        <div class="limit-box2 fl-wrap"></div>
                                        <!-- post-author-->                                   
                                        <div class="post-author fl-wrap">
                                            <div class="author-img">
                                                <img  src="{{ asset('images/avatar/2.jpg') }}" alt="">	
                                            </div>
                                            <div class="author-content fl-wrap">
                                                <h5>Escrito por <a href="{{ route('news.index') }}">{{ optional($post->author)->name }}</a></h5>
                                                <p>Conteúdo de demonstração do PIGEA sobre geociências em Angola.</p>
                                            </div>
                                            <div class="profile-card-footer fl-wrap">
                                                <a href="{{ route('news.index') }}" class="post-author_link">Ver Perfil <i class="fas fa-caret-right"></i></a>
                                                <div class="profile-card-footer_soc">
                                                    <div class="profile-card-footer_title">Seguir: </div>
                                                    <div class="profile-card-social">
                                                        <ul>
                                                            <li><a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                                                            <li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
                                                            <li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                                                            <li><a href="#" target="_blank"><i class="fab fa-vk"></i></a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--post-author end-->		
                                        <div class="more-post-wrap  fl-wrap">
                                            <div class="pr-subtitle prs_big">Artigos Relacionados</div>
                                            <div class="list-post-wrap list-post-wrap_column fl-wrap">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        @foreach ($listNews->take(1) as $item)
<!--list-post-->	
                                                        <div class="list-post fl-wrap">
                                                            <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                            <div class="list-post-media">
                                                                <a href="{{ route('news.show', $item->slug) }}">
                                                                    <div class="bg-wrap">
                                                                        <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                                    </div>
                                                                </a>
                                                                <span class="post-media_title">&copy; Foto: PIGEA</span>
                                                            </div>
                                                            <div class="list-post-content">
                                                                <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                                                                <span class="post-date"><i class="far fa-clock"></i>  {{ $item->display_date }}</span>
                                                            </div>
                                                        </div>
                                                        <!--list-post end-->
@endforeach						  
                                                    </div>
                                                    <div class="col-md-6">
                                                        @foreach ($listNews->skip(1)->take(1) as $item)
<!--list-post-->	
                                                        <div class="list-post fl-wrap">
                                                            <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                            <div class="list-post-media">
                                                                <a href="{{ route('news.show', $item->slug) }}">
                                                                    <div class="bg-wrap">
                                                                        <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                                    </div>
                                                                </a>
                                                                <span class="post-media_title">&copy; Foto: PIGEA</span>
                                                            </div>
                                                            <div class="list-post-content">
                                                                <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                                                                <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                                            </div>
                                                        </div>
                                                        <!--list-post end-->
@endforeach						  
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <!-- sidebar   -->
                                    <div class="sidebar-content fl-wrap fixed-bar">
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="box-widget-content">
                                                <div class="search-widget fl-wrap">
                                                    <form action="{{ route('search.index') }}">
                                                        <input name="se" id="se12" type="text" class="search" placeholder="Pesquisar notícias, projetos, empresas..." value="" />
                                                        <button class="search-submit2" id="submit_btn12"><i class="far fa-search"></i> </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- box-widget  end -->
                                        <!-- Banner publicitario sidebar (FASE 6) -->
                                        <x-banner-publicitario posicao="sidebar" wrapper />
                                        <!-- box-widget  end -->						
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="box-widget-content">
                                                <div class="banner-widget fl-wrap">
                                                    <div class="bg-wrap bg-parallax-wrap-gradien">
                                                        <div class="bg  " data-bg="{{ asset('images/bg/7.jpg') }}"></div>
                                                    </div>
                                                    <div class="banner-widget_content">
                                                        <h5>Registe a sua empresa no diretório PIGEA e aceda a novas oportunidades de negócio.</h5>
                                                        <a href="{{ route('registo-empresa') }}" class="btn float-btn color-bg small-btn">Registar Empresa</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- box-widget  end -->					
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="widget-title">Categorias</div>
                                            <div class="box-widget-content">
                                                <ul class="cat-wid-list">
                                                    <li><a href="#">Internacional</a><span>3</span></li>
                                                    <li><a href="#">Mineração</a> <span>6</span></li>
                                                    <li><a href="#">Água</a> <span>12</span></li>
                                                    <li><a href="#">Investigação</a> <span>4</span></li>
                                                    <li><a href="#">Ambiente</a> <span>22</span></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- box-widget  end -->
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="widget-title">Tags Populares</div>
                                            <div class="box-widget-content">
                                                <div class="tags-widget">
                                                    <a href="#">Internacional</a>
                                                    <a href="#">Mineração</a>
                                                    <a href="#">Água</a>
                                                    <a href="#">Ambiente</a>
                                                    <a href="#">Investigação</a>
                                                    <a href="#">Eventos</a>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- box-widget  end -->						
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="widget-title">Siga-nos</div>
                                            <div class="box-widget-content">
                                                <div class="social-widget">
                                                    <a href="#" target="_blank" class="facebook-soc">
                                                    <i class="fab fa-facebook-f"></i>
                                                    <span class="soc-widget-title">Curtidas</span>
                                                    <span class="soc-widget_counter">2640</span>
                                                    </a>
                                                    <a href="#" target="_blank" class="twitter-soc">
                                                    <i class="fab fa-twitter"></i>
                                                    <span class="soc-widget-title">Seguidores</span>
                                                    <span class="soc-widget_counter">1456</span>												
                                                    </a> 
                                                    <a href="#" target="_blank" class="youtube-soc">
                                                    <i class="fab fa-youtube"></i>
                                                    <span class="soc-widget-title">Seguidores</span>
                                                    <span class="soc-widget_counter">1456</span>												
                                                    </a> 												
                                                    <a href="#" target="_blank" class="instagram-soc">
                                                    <i class="fab fa-instagram"></i>
                                                    <span class="soc-widget-title">Seguidores</span>
                                                    <span class="soc-widget_counter">1456</span>												
                                                    </a> 														
                                                </div>
                                            </div>
                                        </div>
                                        <!-- box-widget  end -->						
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="box-widget-content">
                                                <!-- content-tabs-wrap -->
                                                <div class="content-tabs-wrap tabs-act tabs-widget fl-wrap">
                                                    <div class="content-tabs fl-wrap">
                                                        <ul class="tabs-menu fl-wrap no-list-style">
                                                            <li class="current"><a href="#tab-popular"> Notícias Populares </a></li>
                                                            <li><a href="#tab-resent">Notícias Recentes</a></li>
                                                        </ul>
                                                    </div>
                                                    <!--tabs -->                       
                                                    <div class="tabs-container">
                                                        <!--tab -->
                                                        <div class="tab">
                                                            <div id="tab-popular" class="tab-content first-tab">
                                                                <div class="post-widget-container fl-wrap">
                                                                    @foreach ($widgetNews->take(3) as $item)
<!-- post-widget-item -->
                                                                    <div class="post-widget-item fl-wrap">
                                                                        <div class="post-widget-item-media">
                                                                            <a href="{{ route('news.show', $item->slug) }}"><img src="{{ asset($item->image) }}"  alt=""></a>
                                                                        </div>
                                                                        <div class="post-widget-item-content">
                                                                            <h4><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                                            <ul class="pwic_opt">
                                                                                <li><span><i class="far fa-clock"></i> {{ $item->display_date }}</span></li>
                                                                                <li><span><i class="fal fa-eye"></i> {{ $item->views_count }}</span></li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <!-- post-widget-item end -->
@endforeach														
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--tab  end-->
                                                        <!--tab -->
                                                        <div class="tab">
                                                            <div id="tab-resent" class="tab-content">
                                                                <div class="post-widget-container fl-wrap">
                                                                    @foreach ($widgetNews->take(3) as $item)
<!-- post-widget-item -->
                                                                    <div class="post-widget-item fl-wrap">
                                                                        <div class="post-widget-item-media">
                                                                            <a href="{{ route('news.show', $item->slug) }}"><img src="{{ asset($item->image) }}"  alt=""></a>
                                                                        </div>
                                                                        <div class="post-widget-item-content">
                                                                            <h4><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                                            <ul class="pwic_opt">
                                                                                <li><span><i class="far fa-clock"></i> {{ $item->display_date }}</span></li>
                                                                                <li><span><i class="fal fa-eye"></i> {{ $item->views_count }}</span></li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <!-- post-widget-item end -->
@endforeach													
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--tab end-->							
                                                    </div>
                                                    <!--tabs end-->  
                                                </div>
                                                <!-- content-tabs-wrap end -->
                                            </div>
                                        </div>
                                        <!-- box-widget  end -->					
                                    </div>
                                    <!-- sidebar  end -->
                                </div>
                            </div>
                            <div class="limit-box fl-wrap"></div>
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
