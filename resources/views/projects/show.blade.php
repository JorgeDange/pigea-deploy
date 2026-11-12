@extends('layouts.app')

@section('title', 'Projeto Mineiro | PIGEA')
@section('description', 'Ficha de projeto mineiro no PIGEA.')

@section('content')

                    <!--section   -->
                    <section class="hero-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/9.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="hero-section_title hs_single-post">
                                <a class="post-category-marker color-bg" href="{{ route('news.index') }}">{{ optional($post->category)->name ?? 'Geral' }}</a>
                                <span class="post-date"><i class="far fa-clock"></i> {{ $post->display_date }}</span>
                                <div class="clearfix"></div>
                                <h2>{{ $post->title }}</h2>
                                <h5>Informação actualizada e verificada pela equipa PIGEA.</h5>
                                <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($post->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($post->author)->name }}</span></a></div>
                                <ul class="post-opt">
                                    <li><i class="fal fa-eye"></i>  {{ $post->views_count }} </li>
                                </ul>
                            </div>
                            <div class="clearfix"></div>
                            <div class="scroll-down-wrap scw_transparent">
                                <div class="mousey">
                                    <div class="scroller"></div>
                                </div>
                                <span>Descubra mais</span>
                            </div>
                        </div>
                    </section>
                    <!-- section end  -->
                    <div class="breadcrumbs-section fl-wrap">
                        <div class="container">
                            <div class="breadcrumbs-header_url">
                                <a href="{{ route('home') }}">Início</a><span>Projetos</span>
                            </div>
                            <div class="share-holder hor-share">
                                <div class="share-title">Share:</div>
                                <div class="share-container  isShare"></div>
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
                                        <!-- single-post-media   -->
                                        <div class="single-post-media fl-wrap">
                                            <a class="video-holder fl-wrap  image-popup"  href="https://www.youtube.com/watch?v=K-6tPkm6cZA">
                                                <div class="bg" data-bg="{{ asset('images/all/20.jpg') }}"></div>
                                                <div class="overlay"></div>
                                                <div class="big_prom"> <i class="fas fa-play"></i></div>
                                            </a>
                                        </div>
                                        <!-- single-post-media end   -->
                                        <!-- single-post-content   -->
                                        <div class="single-post-content  fl-wrap">
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
    
    <img src="{{ image_url($prev->image, 'images/all/2.jpg') }}" class="respimg" alt="">
    
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
    
    <img src="{{ image_url($next->image, 'images/all/20.jpg') }}" class="respimg" alt="">
    
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
                                                                        <div class="bg" data-bg="{{ image_url($item->image) }}"></div>
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
                                                                        <div class="bg" data-bg="{{ image_url($item->image) }}"></div>
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
                                        <!-- Banner vertical publicitario (FASE 18) -->
                                        <x-banner-publicitario posicao="lateral" wrapper />
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
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="widget-title">Cetegories</div>
                                            <div class="box-widget-content">
                                                <div class="sb-categories_bg">
                                                    <!-- sb-categories_bg_item -->
                                                    <a href="{{ route('news.index') }}" class="sb-categories_bg_item">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/7.jpg') }}"></div>
                                                            <div class="overlay"></div>
                                                        </div>
                                                        <div class="spb-categories_title"><span>01</span>Mineração</div>
                                                        <div class="spb-categories_counter">66</div>
                                                    </a>
                                                    <!-- sb-categories_bg_item  end-->
                                                    <!-- sb-categories_bg_item -->
                                                    <a href="{{ route('news.index') }}" class="sb-categories_bg_item">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/2.jpg') }}"></div>
                                                            <div class="overlay"></div>
                                                        </div>
                                                        <div class="spb-categories_title"><span>02</span>Água</div>
                                                        <div class="spb-categories_counter">22</div>
                                                    </a>
                                                    <!-- sb-categories_bg_item  end-->											
                                                    <!-- sb-categories_bg_item -->
                                                    <a href="{{ route('news.index') }}" class="sb-categories_bg_item">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/3.jpg') }}"></div>
                                                            <div class="overlay"></div>
                                                        </div>
                                                        <div class="spb-categories_title"><span>03</span>Ambiente</div>
                                                        <div class="spb-categories_counter">54</div>
                                                    </a>
                                                    <!-- sb-categories_bg_item  end-->													
                                                    <!-- sb-categories_bg_item -->
                                                    <a href="{{ route('news.index') }}" class="sb-categories_bg_item">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/5.jpg') }}"></div>
                                                            <div class="overlay"></div>
                                                        </div>
                                                        <div class="spb-categories_title"><span>04</span>Investigação</div>
                                                        <div class="spb-categories_counter">15</div>
                                                    </a>
                                                    <!-- sb-categories_bg_item  end-->													
                                                    <!-- sb-categories_bg_item -->
                                                    <a href="{{ route('news.index') }}" class="sb-categories_bg_item">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/4.jpg') }}"></div>
                                                            <div class="overlay"></div>
                                                        </div>
                                                        <div class="spb-categories_title"><span>05</span>Internacional</div>
                                                        <div class="spb-categories_counter">29</div>
                                                    </a>
                                                    <!-- sb-categories_bg_item  end-->													
                                                </div>
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
@include('components.redes-sociais')
						
                                        <!-- box-widget -->
@include('components.destaque', ['contexto' => 'sidebar'])
	
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
                                                                            <a href="{{ route('news.show', $item->slug) }}"><img src="{{ image_url($item->image) }}"  alt=""></a>
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
                                                                            <a href="{{ route('news.show', $item->slug) }}"><img src="{{ image_url($item->image) }}"  alt=""></a>
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
