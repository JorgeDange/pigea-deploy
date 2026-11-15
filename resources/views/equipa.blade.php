@extends('layouts.app')

@section('title', 'Equipa PIGEA | PIGEA')
@section('description', 'Conheça a equipa da Plataforma de Impulsionamento das Geociências em Angola — direção, coordenação editorial, redação, tecnologia e parcerias.')

@section('content')

                    <!--section   -->
                    <div class="breadcrumbs-header fl-wrap">
                        <div class="container">
                            <div class="breadcrumbs-header_url">
                                <a href="{{ route('home') }}">Início</a><span>Equipa</span>
                            </div>
                            <div class="scroll-down-wrap">
                                <div class="mousey">
                                    <div class="scroller"></div>
                                </div>
                                <span>Os nossos recursos humanos</span>
                            </div>
                        </div>
                        <div class="pwh_bg"></div>
                    </div>
                    <!-- section end  -->
                    <!--section   -->
                    <section>
                        <div class="container">
                            <div class="section-title sect_dec">
                                <h2>Equipa PIGEA</h2>
                                <h4>As pessoas que fazem as geociências chegar a toda a Angola</h4>
                            </div>

                            @if ($total === 0)
                                <p class="team-vazio">A equipa está a ser atualizada. Volte em breve.</p>
                            @else
                                @foreach ($seccoes as $chave => $bloco)
                                <div class="team-section" id="equipa-{{ $chave }}">
                                    <h3 class="team-section-title"><span>{{ $bloco['rotulo'] }}</span></h3>
                                    <div class="team-grid">
                                        @foreach ($bloco['membros'] as $membro)
                                        <article class="team-card">
                                            <div class="team-card-media">
                                                <span class="team-card-iniciais" aria-hidden="true">{{ \App\Support\Institucional::iniciais($membro->nome) }}</span>
                                                @if ($membro->foto_url)
                                                    <img src="{{ $membro->foto_url }}" alt="Foto de {{ $membro->nome }}" width="176" height="176" loading="lazy" onerror="this.remove()">
                                                @endif
                                            </div>
                                            <h4 class="team-card-nome">{{ $membro->nome }}</h4>
                                            <span class="team-card-cargo">{{ \App\Support\Institucional::cargoVisivel($membro) }}</span>
                                            @if ($membro->facebook || $membro->instagram || $membro->linkedin)
                                            <div class="team-card-social">
                                                @if ($membro->facebook)<a href="{{ $membro->facebook }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook de {{ $membro->nome }}"><i class="fab fa-facebook-f"></i></a>@endif
                                                @if ($membro->instagram)<a href="{{ $membro->instagram }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram de {{ $membro->nome }}"><i class="fab fa-instagram"></i></a>@endif
                                                @if ($membro->linkedin)<a href="{{ $membro->linkedin }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn de {{ $membro->nome }}"><i class="fab fa-linkedin-in"></i></a>@endif
                                            </div>
                                            @endif
                                        </article>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="sec-dec"></div>
                    </section>
                    <!-- team end   -->
                </div>

@endsection
