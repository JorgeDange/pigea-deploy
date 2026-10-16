@extends('layouts.app')

@section('title', 'Agenda de Eventos | PIGEA')
@section('description', 'Feiras, conferências e seminários de geociências, mineração e ambiente em Angola.')

@section('content')

                    <!--section   -->
                    <section class="hero-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/6.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="hero-section_title">
                                <h2>PIGEA</h2>
                            </div>
                            <div class="clearfix"></div>
                            <div class="breadcrumbs-list fl-wrap">
                                <a href="{{ route('home') }}">Início</a> <span>Eventos</span>
                            </div>
                            <div class="scroll-down-wrap scw_transparent">
                                <div class="mousey">
                                    <div class="scroller"></div>
                                </div>
                                <span>Descubra mais</span>
                            </div>
                        </div>
                    </section>
                    <!-- section end  -->
                    <!--section   -->
                    <section>
                        <div class="container">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="main-container fl-wrap fix-container-init">
                                        <div class="section-title">
                                            <h3>Ordenar por data</h3>
                                            <div class="steader_opt steader_opt_abs">
                                                <select name="filter" id="list" data-placeholder="Categoria" class="style-select no-search-select">
                  <option>Todos os tipos</option>
                  <option>Conferência</option>
                  <option>Feira</option>
                  <option>Workshop</option>
                  <option>Webinar</option>
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
                    <!-- section  -->
                    <div class="gray-bg ad-wrap fl-wrap">
                        <div class="content-banner-wrap">
                            <img src="{{ asset('images/all/banner.jpg') }}" class="respimg" alt="">
                        </div>
                    </div>
                    <!-- section end -->
                </div>
                
@endsection
