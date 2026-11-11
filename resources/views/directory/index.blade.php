@extends('layouts.app')

@section('title', 'Directório — Empresas & Documentos | PIGEA')
@section('description', 'Directório PIGEA: empresas e profissionais do setor das geociências em Angola e artigos/documentos da equipa.')

@section('content')

                    <!--section   -->
                    <section class="hero-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/10.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="hero-section_title">
                                <h2>Directório PIGEA</h2>
                            </div>
                            <div class="clearfix"></div>
                            <div class="breadcrumbs-list fl-wrap">
                                <a href="{{ route('home') }}">Início</a> <span>Directório</span>
                            </div>
                        </div>
                    </section>
                    <!-- section end  -->
                    <!--section   -->
                    <section>
                        <div class="container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="pr-subtitle prs_big">Diretório</div>
                                    <div class="box-widget-content fl-wrap">
                                        <p style="padding: 16px 20px;">Empresas e profissionais do setor aprovados pela equipa PIGEA, e artigos/documentos publicados pela nossa equipa editorial.</p>
                                    </div>
                                    <div class="pr-subtitle prs_big">Pertence ao setor?</div>
                                    <div class="box-widget-content fl-wrap">
                                        <div class="banner-widget fl-wrap">
                                            <div class="banner-widget_content">
                                                <h5>Registe a sua empresa</h5>
                                                <p style="margin: 6px 0 12px;">Submeta o formulário — a equipa PIGEA analisa e aprova.</p>
                                                <a href="{{ route('registo-empresa') }}" class="btn float-btn color-bg small-btn">Registar Empresa</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-widget-content fl-wrap">
                                        <div class="banner-widget fl-wrap">
                                            <div class="banner-widget_content">
                                                <h5>Profissional independente</h5>
                                                <p style="margin: 6px 0 12px;">Entra no diretório de profissionais aprovados.</p>
                                                <a href="{{ route('registo-profissional') }}" class="btn float-btn color2-bg small-btn">Registar como Profissional</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="main-container fl-wrap fix-container-init">
                                        <div class="section-title">
                                            <h2>Empresas & Artigos/Documentos</h2>
                                            <h4>Apenas registos aprovados pela equipa PIGEA</h4>
                                        </div>
                                        <livewire:frontend.directory-listing />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- section end  -->

@endsection
