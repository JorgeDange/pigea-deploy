@extends('layouts.app')

@section('title', 'Parceiros | PIGEA')
@section('description', 'Conheça as empresas e os profissionais que fazem parte da rede de parceiros da Plataforma de Impulsionamento das Geociências em Angola.')

@section('content')

                    <!--section   -->
                    <div class="breadcrumbs-header fl-wrap">
                        <div class="container">
                            <div class="breadcrumbs-header_url">
                                <a href="{{ route('home') }}">Início</a><span>Parceiros</span>
                            </div>
                            <div class="scroll-down-wrap">
                                <div class="mousey">
                                    <div class="scroller"></div>
                                </div>
                                <span>A nossa rede de parcerias</span>
                            </div>
                        </div>
                        <div class="pwh_bg"></div>
                    </div>
                    <!-- section end  -->
                    <!--section   -->
                    <section>
                        <div class="container">
                            <div class="section-title sect_dec">
                                <h2>Parceiros</h2>
                                <h4>Quem constrói connosco o ecossistema das geociências em Angola</h4>
                            </div>

                            @if ($total === 0)
                                <p class="team-vazio">A lista de parceiros está a ser atualizada. Volte em breve.</p>
                            @else
                                <div class="team-section" id="parceiros-empresas">
                                    <h3 class="team-section-title"><span><i class="fa fa-building" aria-hidden="true"></i> Empresas</span></h3>
                                    @if ($empresas->isEmpty())
                                        <p class="team-vazio">Ainda não há empresas parceiras registadas.</p>
                                    @else
                                    <div class="partner-grid">
                                        @foreach ($empresas as $parceiro)
                                        <article class="partner-card">
                                            <div class="partner-card-media">
                                                <span class="partner-card-iniciais" aria-hidden="true">{{ \App\Support\Institucional::iniciais($parceiro->nome) }}</span>
                                                @if ($parceiro->imagem_url)
                                                    <img src="{{ $parceiro->imagem_url }}" alt="Logotipo de {{ $parceiro->nome }}" loading="lazy" onerror="this.remove()">
                                                @endif
                                            </div>
                                            <h4 class="partner-card-nome">{{ $parceiro->nome }}</h4>
                                            <span class="partner-card-badge partner-card-badge--empresa">Empresa</span>
                                            @if ($parceiro->detalhes)<p class="partner-card-detalhes">{{ $parceiro->detalhes }}</p>@endif
                                        </article>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>

                                <div class="team-section" id="parceiros-profissionais">
                                    <h3 class="team-section-title"><span><i class="fa fa-user-tie" aria-hidden="true"></i> Profissionais</span></h3>
                                    @if ($profissionais->isEmpty())
                                        <p class="team-vazio">Ainda não há profissionais parceiros registados.</p>
                                    @else
                                    <div class="partner-grid">
                                        @foreach ($profissionais as $parceiro)
                                        <article class="partner-card">
                                            <div class="partner-card-media">
                                                <span class="partner-card-iniciais" aria-hidden="true">{{ \App\Support\Institucional::iniciais($parceiro->nome) }}</span>
                                                @if ($parceiro->imagem_url)
                                                    <img src="{{ $parceiro->imagem_url }}" alt="Foto de {{ $parceiro->nome }}" loading="lazy" onerror="this.remove()">
                                                @endif
                                            </div>
                                            <h4 class="partner-card-nome">{{ $parceiro->nome }}</h4>
                                            <span class="partner-card-badge partner-card-badge--profissional">Profissional</span>
                                            @if ($parceiro->detalhes)<p class="partner-card-detalhes">{{ $parceiro->detalhes }}</p>@endif
                                        </article>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="sec-dec"></div>
                    </section>
                    <!-- partners end   -->
                </div>

@endsection
