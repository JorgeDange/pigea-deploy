@extends('layouts.app')

@section('title', 'PIGEA — Plataforma de Impulsionamento das Geociências em Angola')
@section('description', 'PIGEA reúne notícias, projetos mineiros, empregos, formação e investigação das geociências em Angola.')

@section('content')

                    <!-- hero-slider-wrap     -->
                    <div class="hero-slider-wrap fl-wrap">
                        <!-- hero-slider-container     -->
                        <div class="hero-slider-container multi-slider fl-wrap full-height">
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <!-- swiper-slide -->
                                    <div class="swiper-slide">
                                        <div class="bg-wrap">
                                            <div class="bg" data-bg="{{ asset('images/all/20.jpg') }}" data-swiper-parallax="40%"></div>
                                            <div class="overlay"></div>
                                        </div>
                                        <div class="hero-item fl-wrap">
                                            <div class="container">
                                                <a class="post-category-marker" href="{{ route('about') }}">Sobre</a>
                                                <div class="clearfix"></div>
                                                <h2><a href="{{ route('about') }}">Geociências por um Angola mais sustentável</a></h2>
                                                <h4>Notícias, projetos e oportunidades das geociências num só lugar.</h4>
                                                <div class="clearfix"></div>
                                                <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset('images/avatar/4.jpg') }}" alt="">  <span>Paulo Fernando</span></a></div>
                                                <span class="post-date"><i class="far fa-clock"></i>  18 mai 2022</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- swiper-slide  end   -->
                                    @foreach ($listNews->take(1) as $item)
<!-- swiper-slide -->
                                    <div class="swiper-slide">
                                        <div class="bg-wrap">
                                            <div class="bg" data-bg="{{ asset($item->image) }}" data-swiper-parallax="40%"></div>
                                            <div class="overlay"></div>
                                        </div>
                                        <div class="hero-item fl-wrap">
                                            <div class="container">
                                                <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                <div class="clearfix"></div>
                                                <h2><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h2>
                                                <h4>Plataforma geológica digital, disponível para consulta pública.</h4>
                                                <div class="clearfix"></div>
                                                <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                                <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- swiper-slide  end   -->
@endforeach								
                                    <!-- swiper-slide -->
                                    <div class="swiper-slide">
                                        <div class="bg-wrap">
                                            <div class="bg" data-bg="{{ asset('images/all/56.jpg') }}" data-swiper-parallax="40%"></div>
                                            <div class="overlay"></div>
                                        </div>
                                        <div class="hero-item fl-wrap">
                                            <div class="container">
                                                <a class="post-category-marker" href="{{ route('news.index') }}">Mineração</a>
                                                <div class="clearfix"></div>
                                                <h2><a href="{{ route('directory.index') }}">Concurso público para exploração de cobre no Cuando Cubango</a></h2>
                                                <h4>Chuvas fortes ameaçam as comunidades ribeirinhas do Cuando Cubango.</h4>
                                                <div class="clearfix"></div>
                                                <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset('images/avatar/2.jpg') }}" alt="">  <span>Carlos Neto</span></a></div>
                                                <span class="post-date"><i class="far fa-clock"></i> 22 março 2022</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- swiper-slide  end   -->								
                                    <!-- swiper-slide -->
                                    <div class="swiper-slide">
                                        <div class="bg-wrap">
                                            <div class="bg" data-bg="{{ asset('images/all/57.jpg') }}" data-swiper-parallax="40%"></div>
                                            <div class="overlay"></div>
                                        </div>
                                        <div class="hero-item fl-wrap">
                                            <div class="container">
                                                <a class="post-category-marker" href="{{ route('events.index') }}">Eventos</a>
                                                <div class="clearfix"></div>
                                                <h2><a href="{{ route('events.index') }}">Feira Internacional de Mineração de Angola</a></h2>
                                                <h4>Três dias de conferências, exposições e negócios em Luanda.</h4>
                                                <div class="clearfix"></div>
                                                <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset('images/avatar/1.jpg') }}" alt="">  <span>Ana Manuel</span></a></div>
                                                <span class="post-date"><i class="far fa-clock"></i> 15 março 2022</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- swiper-slide  end   -->								
                                </div>
                            </div>
                            <div class="fs-slider_btn color-bg fs-slider-button-prev"><i class="fas fa-caret-left"></i></div>
                            <div class="fs-slider_btn color-bg fs-slider-button-next"><i class="fas fa-caret-right"></i></div>
                        </div>
                        <!-- hero-slider-container  end   -->
                        <!-- hero-slider_controls-wrap   -->
                        <div class="hero-slider_controls-wrap">
                            <div class="container">
                                <div class="hero-slider_controls-list multi-slider_control">
                                    <div class="swiper-container">
                                        <div class="swiper-wrapper">
                                            <!-- swiper-slide  -->
                                            <div class="swiper-slide">
                                                <div class="hsc-list_item fl-wrap">
                                                    <div class="hsc-list_item-media">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/20.jpg') }}"></div>
                                                        </div>
                                                    </div>
                                                    <div class="hsc-list_item-content fl-wrap">
                                                        <h4>Geociências por um Angola mais sustentável</h4>
                                                        <span class="post-date"><i class="far fa-clock"></i> 18 maio 2022</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- swiper-slide end   -->
                                            <!-- swiper-slide  -->
                                            <div class="swiper-slide">
                                                <div class="hsc-list_item fl-wrap">
                                                    <div class="hsc-list_item-media">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/55.jpg') }}"></div>
                                                        </div>
                                                    </div>
                                                    <div class="hsc-list_item-content fl-wrap">
                                                        <h4>Novo mapa geológico digital de Angola</h4>
                                                        <span class="post-date"><i class="far fa-clock"></i>   05 abril 2022</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- swiper-slide end   -->	
                                            <!-- swiper-slide  -->
                                            <div class="swiper-slide">
                                                <div class="hsc-list_item fl-wrap">
                                                    <div class="hsc-list_item-media">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/56.jpg') }}"></div>
                                                        </div>
                                                    </div>
                                                    <div class="hsc-list_item-content fl-wrap">
                                                        <h4>Concurso público para exploração de cobre no Cuando Cubango</h4>
                                                        <span class="post-date"><i class="far fa-clock"></i> 15 março 2022</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- swiper-slide end   -->									
                                            <!-- swiper-slide  -->
                                            <div class="swiper-slide">
                                                <div class="hsc-list_item fl-wrap">
                                                    <div class="hsc-list_item-media">
                                                        <div class="bg-wrap">
                                                            <div class="bg" data-bg="{{ asset('images/all/57.jpg') }}"></div>
                                                        </div>
                                                    </div>
                                                    <div class="hsc-list_item-content fl-wrap">
                                                        <h4>Feira Internacional de Mineração de Angola</h4>
                                                        <span class="post-date"><i class="far fa-clock"></i>    15 março 2022</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- swiper-slide end   -->								
                                        </div>
                                    </div>
                                </div>
                                <div class="multi-pag"></div>
                            </div>
                        </div>
                        <!-- hero-slider_controls-wrap end  -->
                        <div class="slider-progress-bar act-slider">
                            <span>
                                <svg class="circ" width="30" height="30">
                                    <circle class="circ2" cx="15" cy="15" r="13" stroke="rgba(255,255,255,0.4)" stroke-width="1" fill="none" />
                                    <circle class="circ1" cx="15" cy="15" r="13" stroke="#e93314" stroke-width="2" fill="none" />
                                </svg>
                            </span>
                        </div>
                    </div>
                    <!-- hero-slider-wrap end -->
<!-- section   -->
                    <section>
                        <div class="container">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="main-container fl-wrap fix-container-init">
                                        <div class="section-title">
                                            <h2>Últimas Notícias</h2>
                                            <h4>Fique a par das últimas informações</h4>
                                            <div class="ajax-nav">
                                                <ul>
                                                    <li><a href="ajax/noticias-mineracao.html" class="current_page">Mineração</a></li>
                                                    <li><a href="ajax/noticias-agua.html">Água</a></li>
                                                    <li><a href="ajax/noticias-ambiente.html">Ambiente</a></li>
                                                    <li><a href="ajax/noticias-investigacao.html">Investigação</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="ajax-wrapper fl-wrap">
                                            <div class="ajax-loader"><div class="al-box"><img src="{{ asset('images/logo.png') }}" alt="PIGEA" class="al-logo"><span class="al-bar"></span></div></div>
                                            <div id="ajax-content" class="fl-wrap">
                                            </div>
                                        </div>
                                        <div class="content-banner-wrap cbw_mar">
                                            <img src="{{ asset('images/all/banner.jpg') }}" class="respimg" alt="">
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="section-title sect_dec">
                                            <h2>Destaques</h2>
                                            <h4>Fique a par das últimas informações</h4>
                                        </div>
                                        <div class="grid-post-wrap">
                                            <div class="more-post-wrap  fl-wrap">
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
                                            <!--grid-post-item-->
                                            <div class="single-grid-slider-wrap fl-wrap">
                                                <div class="single-grid-slider">
                                                    <div class="swiper-container">
                                                        <div class="swiper-wrapper">
                                                            @foreach ($listNews->take(3) as $item)
<!-- swiper-slide-->  
                                                            <div class="swiper-slide">
                                                                <div class="grid-post-item  bold_gpi  fl-wrap">
                                                                    <div class="grid-post-media gpm_sing">
                                                                        <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                                        <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                                                        <div class="grid-post-media_title">
                                                                            <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                                            <h4><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                                            <span class="video-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                                                            <ul class="post-opt">
                                                                                <li><i class="far fa-comments-alt"></i>  {{ $item->comments_count }} </li>
                                                                                <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- swiper-slide end-->
@endforeach										
                                                        </div>
                                                        <div class="sgs-pagination sgs_ver "></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--grid-post-item end-->								
                                            <div class="more-post-wrap  fl-wrap">
                                                <div class="list-post-wrap list-post-wrap_column fl-wrap">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            @foreach ($listNews->skip(2)->take(1) as $item)
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
                                                            @foreach ($listNews->skip(3)->take(1) as $item)
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
                                                        <div class="col-md-6">
                                                            @foreach ($listNews->skip(4)->take(1) as $item)
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
                                                        <div class="col-md-6">
                                                            @foreach ($listNews->skip(5)->take(1) as $item)
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
                                        <a href="{{ route('news.index') }}" class="dark-btn fl-wrap"> Ver todas as notícias </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <!-- sidebar   -->
                                    <div class="sidebar-content fl-wrap fix-bar">
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
                                                                    @foreach ($widgetNews->take(4) as $item)
<!-- post-widget-item -->
                                                                    <div class="post-widget-item fl-wrap">
                                                                        <div class="post-widget-item-media">
                                                                            <a href="{{ route('news.show', $item->slug) }}"><img src="{{ asset($item->image) }}"  alt=""></a>
                                                                        </div>
                                                                        <div class="post-widget-item-content">
                                                                            <h4><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                                            <ul class="pwic_opt">
                                                                                <li><span><i class="far fa-clock"></i> {{ $item->display_date }}</span></li>
                                                                                <li><span><i class="far fa-comments-alt"></i> {{ $item->comments_count }}</span></li>
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
                                                                                <li><span><i class="far fa-comments-alt"></i> {{ $item->comments_count }}</span></li>
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
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                    <div class="widget-title">PIGEA em números</div>
                    <div class="box-widget-content">
        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #EFEFEF;">
          <span style="font-family:Poppins,sans-serif;font-size:28px;font-weight:600;color:#C8102E;line-height:1;">312</span>
          <span style="font-family:Poppins,sans-serif;font-size:13px;color:#1A1A1A;text-align:right;line-height:1.4;">Projetos mineiros mapeados</span>
        </div>
        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #EFEFEF;">
          <span style="font-family:Poppins,sans-serif;font-size:28px;font-weight:600;color:#C8102E;line-height:1;">1.480</span>
          <span style="font-family:Poppins,sans-serif;font-size:13px;color:#1A1A1A;text-align:right;line-height:1.4;">Empresas no diretório</span>
        </div>
        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid #EFEFEF;">
          <span style="font-family:Poppins,sans-serif;font-size:28px;font-weight:600;color:#C8102E;line-height:1;">96</span>
          <span style="font-family:Poppins,sans-serif;font-size:13px;color:#1A1A1A;text-align:right;line-height:1.4;">Vagas de emprego ativas</span>
        </div>
        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:12px 0;">
          <span style="font-family:Poppins,sans-serif;font-size:28px;font-weight:600;color:#C8102E;line-height:1;">54</span>
          <span style="font-family:Poppins,sans-serif;font-size:13px;color:#1A1A1A;text-align:right;line-height:1.4;">Estudos de investigação</span>
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
                                            <div class="box-widget-content">
                                                <div class="single-grid-slider slider_widget">
                                                    <div class="slider_widget_title">Escolha da Equipa</div>
                                                    <div class="swiper-container">
                                                        <div class="swiper-wrapper">
                                                            @foreach ($listNews->take(3) as $item)
<!-- swiper-slide-->  
                                                            <div class="swiper-slide">
                                                                <div class="{{ $loop->index === 0 ? 'grid-post-item     fl-wrap' : 'grid-post-item  bold_gpi  fl-wrap' }}">
                                                                    <div class="grid-post-media gpm_sing">
                                                                        <div class="bg-wrap">
                                                                            <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                                            <div class="overlay"></div>
                                                                        </div>
                                                                        <div class="grid-post-media_title">
                                                                            <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                                            <h4><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                                            <span class="video-date"><i class="far fa-clock"></i>{{ $item->display_date }}</span>
                                                                            <ul class="post-opt">
                                                                                <li><i class="far fa-comments-alt"></i> {{ $item->comments_count }} </li>
                                                                                <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- swiper-slide end-->
@endforeach										
                                                        </div>
                                                        <div class="sgs-pagination sgs_hor "></div>
                                                    </div>
                                                </div>
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
                    <!-- section   -->
                    <section class="dark-bg no-bottom-padding">
                        <div class="container">
                            <div class="main-video-wrap fl-wrap">
                                <div class="video-main-cont">
                                    <div class="video-section-title fl-wrap">
                                        <h2>Vídeo em Destaque</h2>
                                        <h4>Mantenha-se atualizado</h4>
                                        <a href="{{ route('news.index') }}">Ver tudo <i class="fas fa-caret-right"></i></a>
                                    </div>
                                    <a class="video-holder vh-main fl-wrap  image-popup"  href="#">
                                        <div class="bg"></div>
                                        <div class="overlay"></div>
                                        <div class="big_prom"> <i class="fas fa-play"></i></div>
                                    </a>
                                    <div class="video-holder-title fl-wrap">
                                        <div class="video-holder-title_item"><a href="#"></a></div>
                                        <span class="video-date"><i class="far fa-clock"></i> <strong></strong></span>
                                        <a class="post-category-marker" href="{{ route('news.index') }}"></a>
                                    </div>
                                    <div class="vh-preloader"><div class="vh-pl-box"><img src="{{ asset('images/logo.png') }}" alt="PIGEA" class="vh-pl-logo"><span class="vh-pl-bar"></span></div></div>
                                </div>
                                <!-- video-links-wrap   -->
                                <div class="video-links-wrap">
                                    <!-- video-item  -->
                                    <div class="video-item video-item_active fl-wrap" data-url="noticia-single.html" data-video-link="https://www.youtube.com/watch?v=VLoqKdRxPSc">
                                        <div class="video-item-img fl-wrap">
                                            <img src="{{ asset('images/all/33.jpg') }}" class="respimg" alt="">
                                            <div class="play-icon"><i class="fas fa-play"></i></div>
                                        </div>
                                        <div class="video-item-title">
                                            <h4>Luanda mantém-se o polo das geociências em Angola</h4>
                                            <span class="video-date"><i class="far fa-clock"></i> <strong>25may 2022</strong></span>
                                        </div>
                                        <a class="post-category-marker" href="{{ route('news.index') }}">Ambiente</a>
                                    </div>
                                    <!--video-item end   -->
                                    <!-- video-item  -->
                                    <div class="video-item fl-wrap" data-url="noticia-single.html" data-video-link="https://www.youtube.com/watch?v=K-6tPkm6cZA">
                                        <div class="video-item-img fl-wrap">
                                            <img src="{{ asset('images/all/43.jpg') }}" class="respimg" alt="">
                                            <div class="play-icon"><i class="fas fa-play"></i></div>
                                        </div>
                                        <div class="video-item-title">
                                            <h4>Sensor em destaque</h4>
                                            <span class="video-date"><i class="far fa-clock"></i> <strong>20april 2022</strong></span>
                                        </div>
                                        <a class="post-category-marker" href="{{ route('news.index') }}">Água</a>
                                    </div>
                                    <!--video-item end   -->									
                                    <!-- video-item  -->
                                    <div class="video-item fl-wrap" data-url="posts-ingle2.html" data-video-link="https://vimeo.com/390742893">
                                        <div class="video-item-img fl-wrap">
                                            <img src="{{ asset('images/all/3.jpg') }}" class="respimg" alt="">
                                            <div class="play-icon"><i class="fas fa-play"></i></div>
                                        </div>
                                        <div class="video-item-title">
                                            <h4>Mais empregos e crescimento económico no setor mineiro</h4>
                                            <span class="video-date"><i class="far fa-clock"></i><strong> 14december 2021</strong></span>
                                        </div>
                                        <a class="post-category-marker" href="{{ route('news.index') }}">Mineração</a>
                                    </div>
                                    <!--video-item end   -->	
                                    <!-- video-item  -->
                                    <div class="video-item fl-wrap" data-url="noticia-single.html" data-video-link="https://www.youtube.com/watch?v=vgVr_OpEVMQ">
                                        <div class="video-item-img fl-wrap">
                                            <img src="{{ asset('images/all/42.jpg') }}" class="respimg" alt="">
                                            <div class="play-icon"><i class="fas fa-play"></i></div>
                                        </div>
                                        <div class="video-item-title">
                                            <h4>A investigação científica sobe de nível</h4>
                                            <span class="video-date"><i class="far fa-clock"></i> <strong>05december 2021</strong></span>
                                        </div>
                                        <a class="post-category-marker" href="{{ route('news.index') }}">Ambiente</a>
                                    </div>
                                    <!--video-item end   -->											
                                </div>
                                <!-- video-links-wrap end   -->
                            </div>
                        </div>
                        <div class="video_carousel-wrap fl-wrap">
                            <div class="container">
                                <div class="video_carousel-container">
                                    <div class="video_carousel_title">
                                        <h4>Vídeos Populares</h4>
                                        <div class="vc-pagination pag-style"></div>
                                    </div>
                                    <!-- fw-carousel  -->
                                    <div class="video_carousel  lightgallery">
                                        <div class="swiper-container">
                                            <div class="swiper-wrapper">
                                                <!-- swiper-slide-->  
                                                <div class="swiper-slide">
                                                    @foreach ($listProjects->take(1) as $item)
<!-- video-item  -->
                                                    <div class="video-item fl-wrap">
                                                        <div class="video-item-img fl-wrap">
                                                            <img src="{{ asset($item->image) }}" class="respimg" alt="">
                                                            <a class="play-icon image-popup" href="https://www.youtube.com/watch?v=65YhmHsuD-M"><i class="fas fa-play"></i></a>
                                                        </div>
                                                        <div class="video-item-title">
                                                            <h4><a href="{{ route('projects.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                            <span class="video-date"><i class="far fa-clock"></i> 05december 2021</span>
                                                        </div>
                                                        <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                    </div>
                                                    <!--video-item end   -->
@endforeach
                                                </div>
                                                <!-- swiper-slide end-->  
                                                <!-- swiper-slide-->  
                                                <div class="swiper-slide">
                                                    @foreach ($listProjects->take(1) as $item)
<!-- video-item  -->
                                                    <div class="video-item fl-wrap">
                                                        <div class="video-item-img fl-wrap">
                                                            <img src="{{ asset($item->image) }}" class="respimg" alt="">
                                                            <a class="play-icon image-popup" href="https://vimeo.com/155428349"><i class="fas fa-play"></i></a>
                                                        </div>
                                                        <div class="video-item-title">
                                                            <h4><a href="{{ route('projects.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                            <span class="video-date"><i class="far fa-clock"></i> 05december 2021</span>
                                                        </div>
                                                        <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                    </div>
                                                    <!--video-item end   -->
@endforeach
                                                </div>
                                                <!-- swiper-slide end-->  									
                                                <!-- swiper-slide-->  
                                                <div class="swiper-slide">
                                                    @foreach ($listProjects->take(1) as $item)
<!-- video-item  -->
                                                    <div class="video-item fl-wrap">
                                                        <div class="video-item-img fl-wrap">
                                                            <img src="{{ asset($item->image) }}" class="respimg" alt="">
                                                            <a class="play-icon image-popup" href="https://vimeo.com/108359069"><i class="fas fa-play"></i></a>
                                                        </div>
                                                        <div class="video-item-title">
                                                            <h4><a href="{{ route('projects.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                            <span class="video-date"><i class="far fa-clock"></i> 05december 2021</span>
                                                        </div>
                                                        <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                    </div>
                                                    <!--video-item end   -->
@endforeach
                                                </div>
                                                <!-- swiper-slide end-->  									
                                                <!-- swiper-slide-->  
                                                <div class="swiper-slide">
                                                    @foreach ($listProjects->take(1) as $item)
<!-- video-item  -->
                                                    <div class="video-item fl-wrap">
                                                        <div class="video-item-img fl-wrap">
                                                            <img src="{{ asset($item->image) }}" class="respimg" alt="">
                                                            <a class="play-icon image-popup" href="https://www.youtube.com/watch?v=s-TySen2xLo"><i class="fas fa-play"></i></a>
                                                        </div>
                                                        <div class="video-item-title">
                                                            <h4><a href="{{ route('projects.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                            <span class="video-date"><i class="far fa-clock"></i> 05december 2021</span>
                                                        </div>
                                                        <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                    </div>
                                                    <!--video-item end   -->
@endforeach
                                                </div>
                                                <!-- swiper-slide end-->  									
                                            </div>
                                        </div>
                                    </div>
                                    <!-- fw-carousel end -->								
                                    <div class="cc-prev cc_btn"><i class="fas fa-caret-left"></i></div>
                                    <div class="cc-next cc_btn"><i class="fas fa-caret-right"></i></div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- section end -->
                    <!-- section  -->
                    <section>
                        <div class="container">
                            <div class="section-title sect_dec">
                                <h2>Melhores por Categoria</h2>
                                <h4>Projetos, empregos e eventos em destaque</h4>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="list-post-wrap list-post-wrap_column list-post-wrap_column_fw">
                                        @foreach ($listNews->skip(6)->take(1) as $item)
<!--list-post-->	
                                        <div class="list-post fl-wrap">
                                            <a class="post-category-marker" href="{{ route('projects.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
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
                                                <p>Conteúdo de demonstração do PIGEA sobre geociências em Angola: mineração, água, ambiente e investigação científica. Leia a notícia completa no PIGEA.  </p>
                                                <ul class="post-opt">
                                                    <li><i class="far fa-comments-alt"></i> {{ $item->comments_count }} </li>
                                                    <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                </ul>
                                                <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                            </div>
                                        </div>
                                        <!--list-post end-->
@endforeach				
                                    </div>
                                    <a href="{{ route('news.index') }}" class="dark-btn fl-wrap"> Ver todas as notícias </a>
                                </div>
                                <div class="col-md-7">
                                    <div class="picker-wrap-container fl-wrap">
                                        <div class="picker-wrap-controls">
                                            <ul class="fl-wrap">
                                                <li><span class="pwc_up"><i class="fas fa-caret-up"></i></span></li>
                                                <li><span class="pwc_pause"><i class="fas fa-pause"></i></span></li>
                                                <li><span class="pwc_down"><i class="fas fa-caret-down"></i></span></li>
                                            </ul>
                                        </div>
                                        <div class="picker-wrap fl-wrap">
                                            <div class="list-post-wrap  fl-wrap">
                                                @foreach ($listNews->skip(7)->take(1) as $item)
<!--list-post-->	
                                                <div class="list-post fl-wrap">
                                                    <div class="list-post-media">
                                                        <a href="{{ route('news.show', $item->slug) }}">
                                                            <div class="bg-wrap">
                                                                <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                            </div>
                                                        </a>
                                                        <span class="post-media_title">&copy; Foto: PIGEA</span>
                                                    </div>
                                                    <div class="list-post-content">
                                                        <a class="post-category-marker" href="{{ route('projects.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                        <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                                                        <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                                        <p>Conteúdo de demonstração do PIGEA sobre geociências em Angola.</p>
                                                        <ul class="post-opt">
                                                            <li><i class="far fa-comments-alt"></i> {{ $item->comments_count }} </li>
                                                            <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                        </ul>
                                                        <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                                    </div>
                                                </div>
                                                <!--list-post end-->
@endforeach
                                                @foreach ($listNews->skip(8)->take(1) as $item)
<!--list-post-->	
                                                <div class="list-post fl-wrap">
                                                    <div class="list-post-media">
                                                        <a href="{{ route('news.show', $item->slug) }}">
                                                            <div class="bg-wrap">
                                                                <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                            </div>
                                                        </a>
                                                        <span class="post-media_title">&copy; Foto: PIGEA</span>
                                                    </div>
                                                    <div class="list-post-content">
                                                        <a class="post-category-marker" href="{{ route('projects.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                        <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                                                        <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                                        <p>Conteúdo de demonstração do PIGEA sobre geociências em Angola.</p>
                                                        <ul class="post-opt">
                                                            <li><i class="far fa-comments-alt"></i>  {{ $item->comments_count }} </li>
                                                            <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                        </ul>
                                                        <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                                    </div>
                                                </div>
                                                <!--list-post end-->
@endforeach					
                                                @foreach ($listNews->skip(9)->take(1) as $item)
<!--list-post-->	
                                                <div class="list-post fl-wrap">
                                                    <div class="list-post-media">
                                                        <a href="{{ route('news.show', $item->slug) }}">
                                                            <div class="bg-wrap">
                                                                <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                            </div>
                                                        </a>
                                                        <span class="post-media_title">&copy; Foto: PIGEA</span>
                                                    </div>
                                                    <div class="list-post-content">
                                                        <a class="post-category-marker" href="{{ route('jobs.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                        <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                                                        <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                                        <p>Conteúdo de demonstração do PIGEA sobre geociências em Angola.</p>
                                                        <ul class="post-opt">
                                                            <li><i class="far fa-comments-alt"></i>  {{ $item->comments_count }} </li>
                                                            <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                        </ul>
                                                        <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                                    </div>
                                                </div>
                                                <!--list-post end-->
@endforeach					
                                                @foreach ($listNews->skip(10)->take(1) as $item)
<!--list-post-->	
                                                <div class="list-post fl-wrap">
                                                    <div class="list-post-media">
                                                        <a href="{{ route('news.show', $item->slug) }}">
                                                            <div class="bg-wrap">
                                                                <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                            </div>
                                                        </a>
                                                        <span class="post-media_title">&copy; Foto: PIGEA</span>
                                                    </div>
                                                    <div class="list-post-content">
                                                        <a class="post-category-marker" href="{{ route('jobs.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                        <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                                                        <span class="post-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                                        <p>Conteúdo de demonstração do PIGEA sobre geociências em Angola.</p>
                                                        <ul class="post-opt">
                                                            <li><i class="far fa-comments-alt"></i>  {{ $item->comments_count }}</li>
                                                            <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                        </ul>
                                                        <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                                    </div>
                                                </div>
                                                <!--list-post end-->
@endforeach
                                            </div>
                                        </div>
                                        <div class="controls-limit fl-wrap"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="limit-box"></div>
                    </section>
                    <!-- section end -->
                    <!-- section -->
                    <section class="no-padding">
                        <div class="fs-carousel-wrap">
                            <div class="fs-carousel-wrap_title">
                                <div class="fs-carousel-wrap_title-wrap fl-wrap">
                                    <h4>Notícias em Alta</h4>
                                    <h5>Não perca nada — as melhores escolhas para si.</h5>
                                </div>
                                <div class="abs_bg"></div>
                                <div class="gs-controls">
                                    <div class="gs_button gc-button-next"><i class="fas fa-caret-right"></i></div>
                                    <div class="gs_button gc-button-prev"><i class="fas fa-caret-left"></i></div>
                                </div>
                            </div>
                            <div class="fs-carousel fl-wrap">
                                <div class="swiper-container">
                                    <div class="swiper-wrapper">
                                        @foreach ($listNews->take(4) as $item)
<!-- swiper-slide-->  
                                        <div class="swiper-slide">
                                            <div class="grid-post-item  bold_gpi  fl-wrap">
                                                <div class="grid-post-media gpm_sing">
                                                    <div class="bg" data-bg="{{ asset($item->image) }}"></div>
                                                    <div class="author-link"><a href="{{ route('news.index') }}"><img src="{{ asset(optional($item->author)->avatar ?? 'images/avatar/1.jpg') }}" alt="">  <span>{{ optional($item->author)->name }}</span></a></div>
                                                    <div class="grid-post-media_title">
                                                        <a class="post-category-marker" href="{{ route('news.index') }}">{{ optional($item->category)->name ?? 'Geral' }}</a>
                                                        <h4><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h4>
                                                        <span class="video-date"><i class="far fa-clock"></i> {{ $item->display_date }}</span>
                                                        <ul class="post-opt">
                                                            <li><i class="far fa-comments-alt"></i>  {{ $item->comments_count }} </li>
                                                            <li><i class="fal fa-eye"></i>  {{ $item->views_count }} </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- swiper-slide end-->
@endforeach									
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- section end -->
                    <!-- section  -->
                    <div class="gray-bg ad-wrap fl-wrap">
                        
<section style="background:#1A1A1A;padding:58px 0;">
 <div class="container">
  <div style="text-align:center;max-width:100%;margin:0 auto;">
   <h2 style="font-family:'Playfair Display',serif;font-size:34px;color:#fff;margin:0 0 14px;">Registe a sua empresa no diretório PIGEA</h2>
   <p style="text-align:center;font-family:Poppins,sans-serif;font-size:15px;color:#CFCFCF;margin:0 0 26px;text-align:center;">Aceda a novas oportunidades de negócio, projetos e parceiros das geociências em Angola.</p>
   <a href="{{ route('company.register') }}" style="display:inline-block;background:#C8102E;color:#fff;padding:15px 36px;border-radius:4px;font-family:Poppins,sans-serif;font-weight:600;letter-spacing:.03em;">Registar Empresa</a>
  </div>
 </div>
</section>
<br>
<!-- section end -->
<div class="content-banner-wrap" style="margin:40px auto 0;">
                            <img src="{{ asset('images/all/banner.jpg') }}" class="respimg" alt="">
                        </div>
                    </div>
                    <!-- section end -->
                </div>
                
@endsection
