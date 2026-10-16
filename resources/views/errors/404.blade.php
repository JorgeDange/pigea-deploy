@extends('layouts.app')

@section('title', 'Página não encontrada | PIGEA')
@section('description', 'A página que procura não foi encontrada.')

@section('content')

                    <!--section   -->
                    <section class="hero-section error-section">
                        <div class="bg-wrap hero-section_bg">
                            <div class="bg" data-bg="{{ asset('images/bg/6.jpg') }}"></div>
                        </div>
                        <div class="container">
                            <div class="error-wrap">
                                <h2>404</h2>
                                <p>Lamentamos, mas a página que procura não foi encontrada.</p>
                                <div class="clearfix"></div>
                                <form action="{{ route('search.index') }}" class="custom-form">
                                    <input name="se" id="se2" type="text" class="search" placeholder="Pesquisar notícias..." value="Pesquisar">
                                    <button class="search-submit color-bg" id="submit_btn2"><i class="fa fa-search"></i> </button>
                                </form>
                                <div class="clearfix"></div>
                                
                                <a href="{{ route('home') }}" class="btn color-btn flat-btn"> <span>Voltar à Página Inicial</span> <i class="fas fa-caret-right"></i></a>
                            </div>
                        </div>
                    </section>
                    <!-- section end  -->
                </div>
                
@endsection
