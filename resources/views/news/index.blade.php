@extends('layouts.app')

@section('title', 'Notícias de Geociências | PIGEA')
@section('description', 'Últimas notícias sobre mineração, água, ambiente e investigação científica em Angola.')

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
                                    <livewire:frontend.news-filter />
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
                                        <!-- box-widget -->
                                        <div class="box-widget fl-wrap">
                                            <div class="box-widget-content">
                                                <div class="banner-widget fl-wrap">
                                                    <div class="bg-wrap bg-parallax-wrap-gradien">
                                                        <div class="bg  " data-bg="{{ asset('images/bg/7.jpg') }}"></div>
                                                    </div>
                                                    <div class="banner-widget_content">
                                                        <h5>Registe a sua empresa no diretório PIGEA e aceda a novas oportunidades de negócio.</h5>
                                                        <a href="{{ route('company.register') }}" class="btn float-btn color-bg small-btn">Registar Empresa</a>
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
