@extends('layouts.dashboard')

@section('title', 'Acompanhamento')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Acompanhamento</li></ol>
@endsection

@section('content')

{{-- ==================== CABEÇALHO DE RESUMO ==================== --}}
<div class="acomp-summary {{ $alertas === 0 ? 'acomp-summary-ok' : 'acomp-summary-atencao' }}">
    <div class="acomp-summary-icone"><i class="fa {{ $alertas === 0 ? 'fa-check-circle' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i></div>
    <div class="acomp-summary-texto">
        <h2>{{ $alertas === 0 ? 'Tudo em ordem' : $alertas . ' ' . ($alertas === 1 ? 'ponto a rever' : 'pontos a rever') }}</h2>
        <p>{{ $alertas === 0
            ? 'As funcionalidades novas estão configuradas e a funcionar — redes, banners Largo, destaques, províncias e qualidade editorial.'
            : 'Há pontos que merecem atenção (ver os cartões marcados a vermelho/amarelo). Clica em «Corrigir…» em cada cartão.' }}</p>
    </div>
</div>

<div class="acomp-grid">

    {{-- ==================== REDES SOCIAIS (E3) ==================== --}}
    @php $redeOk = $redesConfiguradas === 5; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><i class="fa fa-share-alt" aria-hidden="true" style="color:#C8102E;"></i> Redes sociais
            <span class="acomp-badge {{ $redeOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $redesConfiguradas }}/5 configuradas</span></h3>
        </div>
        <div class="card-body">
            <ul class="acomp-lista">
                @foreach ($redesEstado as $chave => $rede)
                <li class="acomp-row">
                    <i class="{{ $rede['icone'] }}" aria-hidden="true" style="width:20px;color:#555;"></i>
                    <span class="acomp-row-nome">{{ $rede['nome'] }}</span>
                    @if ($rede['configurada'])
                        <span class="acomp-tag acomp-tag-ok">Configurada</span>
                    @else
                        <span class="acomp-tag acomp-tag-alerta">Por configurar</span>
                    @endif
                </li>
                @endforeach
            </ul>
            <p class="helper-text">As redes só aparecem no rodapé, no topo, em Contactos e no widget «Siga-nos» quando têm link ou nome público.</p>
            <div class="acomp-acoes"><a href="{{ route('admin.configuracoes') }}" class="btn-secondary btn-sm"><i class="fa fa-wrench"></i> Corrigir nas Configurações</a></div>
        </div>
    </div>

    {{-- ==================== BANNER «LARGO» (editoria_cms.md) ==================== --}}
    @php $largoOk = $largo['ativos'] > 0; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><i class="fa fa-bullhorn" aria-hidden="true" style="color:#C8102E;"></i> Banners «Largo»
            <span class="acomp-badge {{ $largoOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $largo['ativos'] }} ativo(s)</span></h3>
        </div>
        <div class="card-body">
            <p class="acomp-texto">Posição «Largo» = a faixa horizontal de largura total (substitui a imagem fixa do tema) nas secções de conteúdo da home, notícias, projetos, pesquisa, eventos, etc.</p>
            <ul class="acomp-kpis">
                <li><span class="acomp-kpi-num">{{ $largo['ativos'] }}</span><span class="acomp-kpi-label">ativos agora</span></li>
                <li><span class="acomp-kpi-num">{{ $largo['total'] }}</span><span class="acomp-kpi-label">criados (incl. inativos)</span></li>
            </ul>
            @unless ($largoOk)
                <p class="acomp-nota-alerta"><i class="fa fa-circle-exclamation" aria-hidden="true"></i> Sem banner «Largo» ativo, a faixa simplesmente não aparece — não há placeholder fixo.</p>
            @endunless
            <div class="acomp-acoes"><a href="{{ route('admin.banners') }}" class="btn-secondary btn-sm"><i class="fa fa-plus"></i> {{ $largoOk ? 'Gerir banners' : 'Criar o banner «Largo»' }}</a></div>
        </div>
    </div>

    {{-- ==================== PROVÍNCIAS (E1) ==================== --}}
    @php $provOk = $semProvincia === 0 && $totalPub > 0; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><i class="fa fa-map-marked-alt" aria-hidden="true" style="color:#C8102E;"></i> Províncias dos artigos
            <span class="acomp-badge {{ $provOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $comProvincia }}/{{ $totalPub }} com província</span></h3>
        </div>
        <div class="card-body">
            <p class="acomp-texto">Como estão os artigos publicados — e como é que «Outros» (texto livre, ex.: internacional) está a ser usada ({{ $livres }} como texto livre).</p>
            @if ($topProvincias->isNotEmpty())
            <ul class="acomp-lista acomp-lista-rank">
                @foreach ($topProvincias as $p)
                <li class="acomp-row">
                    <span class="acomp-pos">{{ $loop->iteration }}</span>
                    <span class="acomp-row-nome">{{ $p['rotulo'] }}</span>
                    <span class="acomp-conta-mini">{{ $p['n'] }} artigo(s)</span>
                </li>
                @endforeach
            </ul>
            @else
                <p class="helper-text">Ainda não há artigos com província preenchida.</p>
            @endif
            <p class="helper-text">Artigos sem província: <strong>{{ $semProvincia }}</strong> · como texto livre («Outros»): <strong>{{ $livres }}</strong></p>
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-pen"></i> Corrigir artigos</a></div>
        </div>
    </div>

    {{-- ==================== DESTAQUE EDITORIAL (editoria_cms.md) ==================== --}}
    @php $pinsOk = $pins > 0; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><i class="fa fa-star" aria-hidden="true" style="color:#C8102E;"></i> Destaque editorial
            <span class="acomp-badge {{ $pinsOk ? 'acomp-badge-ok' : 'acomp-badge-info' }}">{{ $pins }} {{ $pins === 1 ? 'artigo pinado' : 'artigos pinados' }}</span></h3>
        </div>
        <div class="card-body">
            <p class="acomp-texto">Notícias marcadas «★ Destacar na Página Inicial». Os 4 primeiros pinados entram no Hero; os seguintes entram na Zona «Destaques»; as mais recentes preenchem os lugares em falta.</p>
            <p class="helper-text">{{ $pins === 0
                ? 'Sem destaques marcados — o Hero e a Zona «Destaques» usam as mais recentes.'
                : ($pins >= 4 ? 'Hero completo com destaques editoriais — a Zona «Destaques» recebe os pins seguintes.' : 'Pins insuficientes para o Hero (4) — os lugares em falta preenchem-se com as mais recentes.') }}</p>
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-pen"></i> Gerir destaques (coluna ★)</a></div>
        </div>
    </div>

    {{-- ==================== QUALIDADE DOS ARTIGOS ==================== --}}
    @php $qualidadeOk = $semImagem === 0 && $semTags === 0; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><i class="fa fa-clipboard-check" aria-hidden="true" style="color:#C8102E;"></i> Qualidade dos artigos
            <span class="acomp-badge {{ $qualidadeOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $qualidadeOk ? 'em dia' : 'a rever' }}</span></h3>
        </div>
        <div class="card-body">
            <p class="acomp-texto">Sinais de alerta nos artigos publicados que prejudicam a leitura e a pesquisa:</p>
            <ul class="acomp-lista">
                <li class="acomp-row">
                    <i class="far fa-image" aria-hidden="true" style="width:20px;color:#555;"></i>
                    <span class="acomp-row-nome">Sem imagem de capa</span>
                    <span class="acomp-tag {{ $semImagem === 0 ? 'acomp-tag-ok' : 'acomp-tag-alerta' }}">{{ $semImagem }}</span>
                </li>
                <li class="acomp-row">
                    <i class="fas fa-tags" aria-hidden="true" style="width:20px;color:#555;"></i>
                    <span class="acomp-row-nome">Sem etiquetas (Tags)</span>
                    <span class="acomp-tag {{ $semTags === 0 ? 'acomp-tag-ok' : 'acomp-tag-alerta' }}">{{ $semTags }}</span>
                </li>
            </ul>
            <p class="helper-text">A «Imagem de capa» e «Etiquetas» usam-se nas listagens, no artigo e no widget «Tags Populares».</p>
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-pen"></i> Corrigir artigos</a></div>
        </div>
    </div>

    {{-- ==================== VISTAS & TOP 5 (E2) ==================== --}}
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><i class="far fa-eye" aria-hidden="true" style="color:#C8102E;"></i> Vistas reais &amp; Top 5
            <span class="acomp-badge acomp-badge-info">{{ number_format($totalVistas, 0, ',', '.') }} vistas</span></h3>
        </div>
        <div class="card-body">
            <p class="acomp-texto">O contador ignora bots/crawlers e conta cada sessão 1× por 5 minutos. Hoje no site: <strong>{{ $visitasHoje }}</strong> visita(s) registada(s).</p>
            @if ($maisLidas->every(fn ($n) => (int) $n->views_count === 0))
                <p class="chart-vazio">Ainda não há visitas registadas — a contagem honesta começa após a fase E2 (2026-11-13).</p>
            @else
            <ol class="top5-list" style="margin-top:6px">
                @foreach ($maisLidas as $i => $n)
                <li class="top5-item">
                    <span class="top5-pos">{{ $i + 1 }}</span>
                    <a class="top5-title" href="{{ route('news.show', $n->slug) }}" target="_blank" rel="noopener">{{ $n->title }}</a>
                    <span class="top5-views"><i class="far fa-eye" aria-hidden="true"></i> {{ number_format((int) $n->views_count, 0, ',', '.') }}</span>
                </li>
                @endforeach
            </ol>
            @endif
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-arrow-right"></i> Ver a tabela de notícias</a></div>
        </div>
    </div>

</div>

<p class="helper-text" style="margin-top:14px"><i class="fa fa-circle-info" aria-hidden="true"></i> Os contadores acima são reais e recalculados a cada abertura da página — sem cache. Cada cartão tem o botão de ida até ao sítio onde se corrige.</p>

@endsection
