@extends('layouts.app')

@section('title', 'Contactos | PIGEA')
@section('description', 'Contacte o PIGEA: Luanda, Angola — geral@pigea.ao')

@section('content')

                    <!--section   -->
                    <section class="hero-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/8.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="hero-section_title">
                                <h2>Os Nossos Contactos</h2>
                            </div>
                            <div class="clearfix"></div>
                            <div class="breadcrumbs-list fl-wrap">
                                <a href="{{ route('home') }}">Início</a> <a href="#">PIGEA</a> <span>Contactos</span>
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
                                <div class="col-md-4">
                                    <div class="pr-subtitle prs_big">Details</div>
                                    <!--card-item -->
                                    <ul class="contacts-list fl-wrap">
                                        <li><span><i class="fal fa-map-marker"></i> Morada :</span> <a href="#">Luanda, Angola</a></li>
                                        <li><span><i class="fal fa-phone"></i> Telefone :</span> <a href="tel:+244000000000">+244 000 000 000</a></li>
                                        <li><span><i class="fal fa-envelope"></i> Email :</span> <a href="mailto:geral@pigea.ao">geral@pigea.ao</a></li>
                                    </ul>
                                    <!--card-item end -->
                                    <div class="contact-social fl-wrap">
                                        <span class="cs-title">Siga-nos: </span>
                                        <ul>
                                            <li><a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a></li>
                                            <li><a href="#" target="_blank"><i class="fab fa-twitter"></i></a></li>
                                            <li><a href="#" target="_blank"><i class="fab fa-instagram"></i></a></li>
                                            <li><a href="#" target="_blank"><i class="fab fa-vk"></i></a></li>
                                            <li><a href="#" target="_blank"><i class="fab fa-pinterest-p"></i></a></li>
                                        </ul>
                                    </div>
                                    <!-- box-widget -->
                                    <div class="box-widget-content fl-wrap">
                                        <div class="banner-widget fl-wrap">
                                            <div class="bg-wrap bg-parallax-wrap-gradien">
                                                <div class="bg  " data-bg="{{ asset('images/bg/6.jpg') }}"></div>
                                            </div>
                                            <div class="banner-widget_content">
                                                <h5>Registe a sua empresa no diretório PIGEA e aceda a novas oportunidades de negócio.</h5>
                                                <a href="{{ route('company.register') }}" class="btn float-btn color-bg small-btn">Registar Empresa</a>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- box-widget  end -->				
                                </div>
                                <div class="col-md-8">
                                    <div class="pad-con fl-wrap">
                                        <div class="pr-subtitle prs_big">Envie-nos uma mensagem</div>
                                        <p>Os conteúdos do PIGEA são elaborados a partir de fontes oficiais, instituições parceiras e profissionais do setor, e revistos pela equipa editorial antes da publicação.</p>
                                        <livewire:frontend.contact-form />
                                        <!-- contact form  end--> 					
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                
@endsection
