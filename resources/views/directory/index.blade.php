@extends('layouts.app')

@section('title', 'Diretório de Empresas & Oportunidades | PIGEA')
@section('description', 'Empresas do setor das geociências em Angola e oportunidades de negócio.')

@section('content')

                    <!--section   -->
                    <section class="hero-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/10.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="hero-section_title">
                                <h2>Empresas em Destaque</h2>
                            </div>
                            <div class="clearfix"></div>
                            <div class="breadcrumbs-list fl-wrap">
                                <a href="{{ route('home') }}">Início</a> <span>Diretório</span>
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
                                            <h2>Diretório de Empresas & Oportunidades</h2>
                                            <h4>Fique a par das últimas informações</h4>
                                            <div class="steader_opt steader_opt_abs">
                                                <select name="filter" id="list" data-placeholder="Categoria" class="style-select no-search-select">
                  <option>Todos os setores</option>
                  <option>Mineração</option>
                  <option>Água</option>
                  <option>Ambiente</option>
                  <option>Serviços</option>
                </select>
                                            </div>
                                        </div>
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
                                                    <a class="post-category-marker" href="#">{{ optional($item->category)->name }}</a>
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

@section('after_content')
<!-- section Oportunidades -->
<section>
 <div class="container">
  <div class="section-title sect_dec"><h2>Oportunidades de Negócio</h2><h4>Concursos, chamadas e parcerias abertas</h4></div>

  <div style="border:1px solid #E6E6E6;border-radius:6px;padding:22px 24px;margin-bottom:16px;background:#fff;">
   <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin:0 0 8px;color:#1A1A1A;"><a href="{{ route('directory.index') }}" style="color:inherit;">Concurso público: exploração de cobre no Cuando Cubango</a></h3>
    <span style="font-family:Poppins,sans-serif;font-size:13px;color:#C8102E;white-space:nowrap;">Encerramento: 30 de Novembro</span>
   </div>
   <p style="font-family:Poppins,sans-serif;font-size:14px;color:#555;margin:0;">Chamada aberta a empresas de mineração com experiência em exploração de cobre.</p>
  </div>
  <div style="border:1px solid #E6E6E6;border-radius:6px;padding:22px 24px;margin-bottom:16px;background:#fff;">
   <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin:0 0 8px;color:#1A1A1A;"><a href="{{ route('directory.index') }}" style="color:inherit;">Fornecimento de equipamento geofísico para levantamentos 2026</a></h3>
    <span style="font-family:Poppins,sans-serif;font-size:13px;color:#C8102E;white-space:nowrap;">Encerramento: 15 de Outubro</span>
   </div>
   <p style="font-family:Poppins,sans-serif;font-size:14px;color:#555;margin:0;">Aquisição de equipamento de prospeção e serviços associados de calibração.</p>
  </div>
  <div style="border:1px solid #E6E6E6;border-radius:6px;padding:22px 24px;margin-bottom:16px;background:#fff;">
   <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin:0 0 8px;color:#1A1A1A;"><a href="{{ route('directory.index') }}" style="color:inherit;">Parcerias de investigação em hidrogeologia — chamada aberta</a></h3>
    <span style="font-family:Poppins,sans-serif;font-size:13px;color:#C8102E;white-space:nowrap;">Encerramento: 20 de Dezembro</span>
   </div>
   <p style="font-family:Poppins,sans-serif;font-size:14px;color:#555;margin:0;">Instituições nacionais e internacionais podem submeter propostas de colaboração.</p>
  </div>
 </div>
</section>
<!-- section end -->
@endsection
