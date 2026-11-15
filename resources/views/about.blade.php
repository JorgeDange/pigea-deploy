@extends('layouts.app')

@section('title', 'Sobre o PIGEA | PIGEA')
@section('description', 'Missão, visão e valores da Plataforma de Impulsionamento das Geociências em Angola.')

@section('content')

                    <!--section   -->
                    <div class="breadcrumbs-header fl-wrap">
                        <div class="container">
                            <div class="breadcrumbs-header_url">
                                <a href="{{ route('home') }}">Início</a><span>Sobre o PIGEA</span>
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
                    <!-- section end  -->
                    <!--section   -->
                    <section>
                        @php
                            $sobreMissao = \App\Models\Setting::obter('sobre_missao', 'Reunir, validar e disponibilizar informação qualificada sobre as geociências em Angola — mineração, água, ambiente e investigação — ao serviço de profissionais, empresas, instituições e comunidades.');
                            $sobreVisao = \App\Models\Setting::obter('sobre_visao', 'Ser a plataforma de referência das geociências em Angola.');
                            $sobreValores = \App\Models\Setting::obter('sobre_valores', 'Rigor científico, transparência, inclusão e sustentabilidade na gestão dos recursos naturais.');
                            $sobreVideo = \App\Models\Setting::obter('video_institucional', 'https://vimeo.com/225223404');
                            $sobreOverlay = \App\Models\Setting::obter('sobre_overlay', 'Missão · Visão · Valores — informação fiável, acessível e atualizada.');
                        @endphp
                        <div class="container">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="section-title sect_dec">
                                        <h2>Sobre o PIGEA</h2>
                                        <h4>Quem Somos</h4>
                                    </div>
                                    <div class="about-wrap">
                                        <div class="about-pilares">
                                            <div class="about-pilar is-missao">
                                                <h5 class="about-pilar-titulo"><i class="fas fa-bullseye" aria-hidden="true"></i> Missão</h5>
                                                <p>{{ $sobreMissao }}</p>
                                            </div>
                                            <div class="about-pilar is-visao">
                                                <h5 class="about-pilar-titulo"><i class="fas fa-eye" aria-hidden="true"></i> Visão</h5>
                                                <p>{{ $sobreVisao }}</p>
                                            </div>
                                            <div class="about-pilar is-valores">
                                                <h5 class="about-pilar-titulo"><i class="fas fa-gem" aria-hidden="true"></i> Valores</h5>
                                                <p>{{ $sobreValores }}</p>
                                            </div>
                                        </div>
                                        @if ($sobreVideo)
                                        <a href="{{ $sobreVideo }}" class="btn float-btn color-btn image-popup"> <span>Vídeo Institucional</span> <i class="fas fa-caret-right"></i></a>
                                        @endif
                                        <nav class="about-links" aria-label="Explorar Sobre Nós">
                                            <span class="about-links-rotulo">Explorar</span>
                                            <a class="about-link-item" href="{{ route('equipa') }}"><i class="fas fa-users" aria-hidden="true"></i> Equipa PIGEA</a>
                                            <a class="about-link-item" href="{{ route('parceiros') }}"><i class="fas fa-handshake" aria-hidden="true"></i> Nossos Parceiros</a>
                                            <a class="about-link-item" href="{{ route('contacts') }}"><i class="fas fa-envelope" aria-hidden="true"></i> Contactos</a>
                                        </nav>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-6">
                                    <div class="about-img fl-wrap">
                                        <img src="{{ asset('images/all/6.jpg') }}" class="respimg" alt="Sobre a PIGEA" width="2752" height="1536" loading="lazy">
                                        <div class="about-img-hotifer color-bg">
                                            <p>{{ $sobreOverlay }}</p>
                                            <h4><a href="{{ route('equipa') }}">Conheça a Equipa PIGEA</a></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sec-dec"></div>
                    </section>
                    <!--about end   -->
                </div>
                
@endsection
