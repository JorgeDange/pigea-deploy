@extends('layouts.app')

@section('title', 'Registo de Empresa | PIGEA')
@section('description', 'Registe a sua empresa no Diretório PIGEA — submissão pública, aprovação pela equipa interna.')

@section('content')

                    <!--section   -->
                    <section class="hero-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/6.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="hero-section_title">
                                <h2>Registo de Empresa</h2>
                            </div>
                            <div class="clearfix"></div>
                            <div class="breadcrumbs-list fl-wrap">
                                <a href="{{ route('home') }}">Início</a> <a href="#">Diretório</a> <span>Registo de Empresa</span>
                            </div>
                        </div>
                    </section>
                    <!-- section end  -->
                    <!--section   -->
                    <section>
                        <div class="container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="pr-subtitle prs_big">Como funciona</div>
                                    <div class="box-widget-content fl-wrap">
                                        <ul class="contacts-list fl-wrap">
                                            <li><span><i class="fal fa-building"></i> 1.</span> Submeta o formulário ao lado — não precisa de criar conta.</li>
                                            <li><span><i class="fal fa-user-shield"></i> 2.</span> A equipa PIGEA analisa e aprova o registo.</li>
                                            <li><span><i class="fal fa-check-circle"></i> 3.</span> Aprovado, a empresa fica visível no Diretório.</li>
                                        </ul>
                                    </div>
                                    <div class="box-widget-content fl-wrap">
                                        <div class="banner-widget fl-wrap">
                                            <div class="banner-widget_content">
                                                <h5>É um profissional independente?</h5>
                                                <a href="{{ route('registo-profissional') }}" class="btn float-btn color2-bg small-btn">Registar como Profissional</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="pad-con fl-wrap">
                                        <div class="pr-subtitle prs_big">Dados da Empresa</div>
                                        <p>Os dados submetidos ficam pendentes até aprovação da equipa editorial. O NIF nunca é apresentado na página pública — serve apenas para identificação.</p>
                                        <livewire:frontend.registo-empresa />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- section end  -->

@endsection
