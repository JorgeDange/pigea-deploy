@extends('layouts.app')

@section('title', 'Entrevistas & Testemunhos | PIGEA')
@section('description', 'Entrevistas com profissionais, empresas e investigadores das geociências em Angola.')

@section('content')

                    <!--section   -->
                    <section class="hero-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/11.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="hero-section_title">
                                <h2>Página do Autor</h2>
                            </div>
                            <div class="clearfix"></div>
                            <div class="breadcrumbs-list fl-wrap">
                                <a href="{{ route('home') }}">Início</a> <a href="{{ route('interviews.index') }}">Entrevistas</a> <span>Perfil</span>
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
                                    <div class="left_fix-bar fl-wrap">
                                        <div class="profile-card-wrap fl-wrap">
                                            <div class="profile-card_media fl-wrap">
                                                <img src="{{ asset('images/avatar/2.jpg') }}" alt="">
                                                <div class="profile-card_media_content">
                                                    <h4>Helena José</h4>
                                                    <h5>Geóloga · Entrevistada</h5>
                                                </div>
                                                <div class="abs_bg"></div>
                                                <div class="profile-card-stats">
                                                    <ul>
                                                        <li><span>12</span>Entrevistas</li>
                                                        <li><span>4320</span> Visualizações</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="profile-card_content">
                                                <h4>Sobre a Entrevistada</h4>
                                                <p>«A geologia de Angola precisa de dados abertos e partilhados — é assim que se decide onde investir, onde proteger e onde crescer», explica a entrevistada.</p>
                                                <p>Entrevista realizada no âmbito do PIGEA.</p>
                                                <div class="pc_contacts">
                                                    <ul>
                                                        <li>
                                                            <span>Escrever:</span> <a href="#">geral@pigea.ao</a>
                                                        </li>
                                                        <li>
                                                            <span>Ligar:</span> <a href="#">+244 000 000 000</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="profile-card-footer fl-wrap">
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
                                </div>
                                <div class="col-md-8">
                                    <livewire:frontend.interview-filter />
                                </div>
                            </div>
                        </div>
                        <div class="limit-box fl-wrap"></div>
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
