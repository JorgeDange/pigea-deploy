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
                        <div class="container">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="section-title sect_dec">
                                        <h2>Sobre o PIGEA</h2>
                                        <h4>Quem Somos</h4>
                                    </div>
                                    <div class="about-wrap">
                                        <p><strong>Missão:</strong> reunir, validar e disponibilizar informação qualificada sobre as geociências em Angola — mineração, água, ambiente e investigação — ao serviço de profissionais, empresas, instituições e comunidades.</p>
                                        <p><strong>Visão e Valores:</strong> ser a plataforma de referência das geociências em Angola, assente em rigor científico, transparência, inclusão e sustentabilidade na gestão dos recursos naturais.</p>
                                        <a href="https://vimeo.com/225223404" class="btn float-btn color-btn image-popup"> <span>Vídeo Institucional</span> <i class="fas fa-caret-right"></i></a>
                                    </div>
                                </div>
                                <div class="col-md-1"></div>
                                <div class="col-md-6">
                                    <div class="about-img fl-wrap">
                                        <img src="{{ asset('images/all/6.jpg') }}" class="respimg" alt="">
                                        <div class="about-img-hotifer color-bg">
                                            <p>Missão · Visão · Valores — informação fiável, acessível e atualizada.</p>
                                            <h4>Equipa PIGEA</h4>
                                            <h5>Direção & Coordenação</h5>
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
