@extends('layouts.dashboard')

@section('title', 'Acompanhamento')

@section('sidebar')
<x-sidebar-admin />
@endsection

@section('breadcrumb')
<ol><li><a href="{{ route('admin.index') }}">Admin</a></li><li class="active" aria-current="page">Acompanhamento</li></ol>
@endsection

@section('content')

{{-- ================= DESTAQUE GERAL ================= --}}
<div class="acomp-hero {{ $alertas === 0 ? 'acomp-hero-ok' : 'acomp-hero-atencao' }}">
    <div class="acomp-hero-media" aria-hidden="true"><i class="fa {{ $alertas === 0 ? 'fa-check' : 'fa-exclamation' }}"></i></div>
    <div class="acomp-hero-body">
        <span class="acomp-kicker">PAINEL DE CONTROLO · ATUALIZADO AGORA</span>
        <h2>{{ $alertas === 0 ? 'Tudo em ordem' : $alertas . ' ' . ($alertas === 1 ? 'ponto a rever' : 'pontos a rever') }}</h2>
        <p>{{ $alertas === 0
            ? 'Redes configuradas e visíveis, destaques pinados, qualidade em dia — o site está pronto para o público.'
            : 'Há pontos que merecem ação. Toque em «Corrigir…» no cartão destacado a amarelo.' }}</p>
    </div>
    <dl class="acomp-hero-stats">
        <div><dt>Redes</dt><dd>{{ $redesConfiguradas }}<small>/5</small></dd></div>
        <div><dt>Pins</dt><dd>{{ $pins }}</dd></div>
        <div><dt>Vistas</dt><dd>{{ number_format($totalVistas, 0, ',', '.') }}</dd></div>
        <div><dt>Alertas</dt><dd class="{{ $alertas > 0 ? 'acomp-hero-alerta' : '' }}">{{ $alertas }}</dd></div>
    </dl>
</div>

<div class="acomp-grid">

    {{-- ============ REDES SOCIAIS (FASE E3) ============ --}}
    @php $redeOk = $redesConfiguradas === 5; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><span class="acomp-icone"><i class="fa fa-share-alt" aria-hidden="true"></i></span> Redes sociais
                <span class="acomp-kicker acomp-kicker-fase">FASE E3</span></h3>
            <span class="acomp-badge {{ $redeOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $redesConfiguradas }}/5</span>
        </div>
        <div class="card-body">
            <ul class="acomp-lista">
                @foreach ($redesEstado as $chave => $rede)
                <li class="acomp-rede">
                    <span class="acomp-dot {{ $rede['configurada'] ? 'acomp-dot-ok' : 'acomp-dot-falta' }}" aria-hidden="true"></span>
                    <i class="{{ $rede['icone'] }} acomp-rede-icone" aria-hidden="true"></i>
                    <span class="acomp-rede-nome">{{ $rede['nome'] }}
                        @if ($rede['configurada'])<small>{{ \Illuminate\Support\Str::limit($rede['handle'] !== '' ? $rede['handle'] : $rede['url'], 34) }}</small>@endif
                    </span>
                    @if ($rede['configurada'])
                        <span class="acomp-tag acomp-tag-ok">Ativa</span>
                    @else
                        <span class="acomp-tag acomp-tag-falta">Falta</span>
                    @endif
                </li>
                @endforeach
            </ul>
            <p class="helper-text">Só aparecem onde existem configuradas (rodapé, topo, Contactos, «Siga-nos»). Sem link nem nome → escondida.</p>
            <div class="acomp-acoes"><a href="{{ route('admin.configuracoes') }}" class="btn-secondary btn-sm"><i class="fa fa-wrench"></i> Corrigir nas Configurações</a></div>
        </div>
    </div>

    {{-- ============ BANNERS «LARGO» (FASE EDITORIAL) ============ --}}
    @php $largoOk = $largo['ativos'] > 0; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><span class="acomp-icone"><i class="fa fa-bullhorn" aria-hidden="true"></i></span> Banners «Largo»
                <span class="acomp-kicker acomp-kicker-fase">FASE EDITORIAL</span></h3>
            <span class="acomp-badge {{ $largoOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $largo['ativos'] }} ativo(s)</span>
        </div>
        <div class="card-body">
            <p class="acomp-texto">A faixa horizontal de largura total que substitui o banner estático do tema nas páginas de conteúdo. Sem banner «Largo» ativo → a faixa não aparece (não há espaço vazio).</p>
            <ul class="acomp-kpis">
                <li><span class="acomp-kpi-num">{{ $largo['ativos'] }}</span><span class="acomp-kpi-label">ativos</span></li>
                <li><span class="acomp-kpi-num">{{ $largo['total'] }}</span><span class="acomp-kpi-label">criados</span></li>
            </ul>
            <div class="acomp-acoes"><a href="{{ route('admin.banners') }}" class="btn-secondary btn-sm"><i class="fa fa-plus"></i> {{ $largoOk ? 'Gerir banners' : 'Criar banner «Largo»' }}</a></div>
        </div>
    </div>

    {{-- ============ PROVÍNCIAS (FASE E1) ============ --}}
    @php
        $provOk = $semProvincia === 0 && $totalPub > 0;
        $maxProvs = $topProvincias->isNotEmpty() ? max(1, $topProvincias->max('n')) : 1;
    @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><span class="acomp-icone"><i class="fa fa-map-marked-alt" aria-hidden="true"></i></span> Províncias
                <span class="acomp-kicker acomp-kicker-fase">FASE E1</span></h3>
            <span class="acomp-badge {{ $provOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $comProvincia }}/{{ $totalPub }}</span>
        </div>
        <div class="card-body">
            <p class="acomp-texto">Capa por província nos artigos publicados — e quantos usam «Outros» (texto livre): <strong>{{ $livres }}</strong>.</p>
            @if ($topProvincias->isNotEmpty())
            <ul class="acomp-rank">
                @foreach ($topProvincias as $p)
                <li class="acomp-rank-item">
                    <span class="acomp-rank-rotulo">{{ $p['rotulo'] }}</span>
                    <span class="acomp-rank-barra" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) round($p['n'] / $maxProvs * 100) }}"><span style="width: {{ (int) round($p['n'] / $maxProvs * 100) }}%"></span></span>
                    <span class="acomp-rank-num">{{ $p['n'] }}</span>
                </li>
                @endforeach
            </ul>
            @else
                <p class="helper-text">Ainda não há artigos com província preenchida.</p>
            @endif
            <p class="helper-text">Artigos sem província: <strong>{{ $semProvincia }}</strong> · texto livre: <strong>{{ $livres }}</strong></p>
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-pen"></i> Corrigir artigos</a></div>
        </div>
    </div>

    {{-- ============ DESTAQUE EDITORIAL (FASE EDITORIAL) ============ --}}
    @php $pinsOk = $pins > 0; @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><span class="acomp-icone"><i class="fa fa-star" aria-hidden="true"></i></span> Destaque editorial
                <span class="acomp-kicker acomp-kicker-fase">FASE EDITORIAL</span></h3>
            <span class="acomp-badge {{ $pinsOk ? 'acomp-badge-ok' : 'acomp-badge-info' }}">{{ $pins }} {{ $pins === 1 ? 'pinado' : 'pinados' }}</span>
        </div>
        <div class="card-body">
            <p class="acomp-texto">Artigos marcados «★ Destacar na Página Inicial». Os 4 primeiros alimentam o <strong>Hero</strong>; os seguintes entram na Zona <strong>«Destaques»</strong>. Lugares em falta são preenchidos com as mais recentes.</p>
            <p class="acomp-callout {{ $pins >= 4 ? 'acomp-callout-ok' : ($pins > 0 ? 'acomp-callout-mix' : 'acomp-callout-falta') }}">
                {{ $pins === 0
                    ? 'Sem pins — o Hero e os Destaques usam as mais recentes.'
                    : ($pins >= 4 ? 'Hero completo com destaques editoriais.' : ($pins . ' pin(s) — o Hero será completado pelas mais recentes.')) }}
            </p>
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-pen"></i> Gerir destaques (coluna ★)</a></div>
        </div>
    </div>

    {{-- ============ QUALIDADE DOS ARTIGOS ============ --}}
    @php
        $comImagem = max(0, $totalPub - $semImagem);
        $comTags = max(0, $totalPub - $semTags);
        $pctImagem = $totalPub > 0 ? (int) round($comImagem / $totalPub * 100) : 0;
        $pctTags = $totalPub > 0 ? (int) round($comTags / $totalPub * 100) : 0;
        $qualidadeOk = $semImagem === 0 && $semTags === 0;
    @endphp
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><span class="acomp-icone"><i class="fa fa-clipboard-check" aria-hidden="true"></i></span> Qualidade dos artigos
                <span class="acomp-kicker acomp-kicker-fase">QUALIDADE</span></h3>
            <span class="acomp-badge {{ $qualidadeOk ? 'acomp-badge-ok' : 'acomp-badge-atencao' }}">{{ $qualidadeOk ? 'Em dia' : 'A rever' }}</span>
        </div>
        <div class="card-body">
            <div class="acomp-barra">
                <div class="acomp-barra-head"><span><i class="far fa-image" aria-hidden="true"></i> Imagem de capa</span><strong>{{ $comImagem }}/{{ $totalPub }}</strong></div>
                <div class="acomp-barra-trilho" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pctImagem }}" aria-label="Artigos com imagem de capa"><span class="{{ $semImagem === 0 ? 'ok' : ($pctImagem >= 60 ? 'meio' : 'falta') }}" style="width: {{ $pctImagem }}%"></span></div>
            </div>
            <div class="acomp-barra">
                <div class="acomp-barra-head"><span><i class="fas fa-tags" aria-hidden="true"></i> Etiquetas (tags)</span><strong>{{ $comTags }}/{{ $totalPub }}</strong></div>
                <div class="acomp-barra-trilho" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pctTags }}" aria-label="Artigos com etiquetas"><span class="{{ $semTags === 0 ? 'ok' : ($pctTags >= 60 ? 'meio' : 'falta') }}" style="width: {{ $pctTags }}%"></span></div>
            </div>
            <p class="helper-text">Sem imagem → cartões vazios; sem tags → «Tags Populares» fraco na sidebar.</p>
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-pen"></i> Corrigir artigos</a></div>
        </div>
    </div>

    {{-- ============ VISTAS & TOP 5 (FASE E2) ============ --}}
    <div class="card acomp-card">
        <div class="card-head">
            <h3 class="card-title"><span class="acomp-icone"><i class="far fa-eye" aria-hidden="true"></i></span> Vistas reais &amp; Top 5
                <span class="acomp-kicker acomp-kicker-fase">FASE E2</span></h3>
            <span class="acomp-badge acomp-badge-info">{{ number_format($totalVistas, 0, ',', '.') }}</span>
        </div>
        <div class="card-body">
            <p class="acomp-texto">Anti-bot ativo.
                @if ($visitasHoje)
                    Hoje no site: <strong>{{ $visitasHoje }}</strong> visita(s).
                @endif
                A mesma sessão conta 1× por 5 minutos; bots não contam.</p>
            @if ($maisLidas->every(fn ($n) => (int) $n->views_count === 0))
                <p class="chart-vazio">Ainda sem leituras registadas — a contagem honesta começa após 2026-11-13.</p>
            @else
            <ol class="top5-list">
                @foreach ($maisLidas as $i => $n)
                <li class="top5-item">
                    <span class="top5-pos">{{ $i + 1 }}</span>
                    <a class="top5-title" href="{{ route('news.show', $n->slug) }}" target="_blank" rel="noopener">{{ $n->title }}</a>
                    <span class="top5-views"><i class="far fa-eye" aria-hidden="true"></i>{{ number_format((int) $n->views_count, 0, ',', '.') }}</span>
                </li>
                @endforeach
            </ol>
            @endif
            <div class="acomp-acoes"><a href="{{ route('admin.noticias') }}" class="btn-secondary btn-sm"><i class="fa fa-arrow-right"></i> Ver a tabela de notícias</a></div>
        </div>
    </div>

</div>

<p class="acomp-rodape"><i class="fa fa-circle-info" aria-hidden="true"></i> Contadores recalculados a cada abertura. Cada cartão liga ao sítio onde se corrige — não há acção destrutiva nesta página.</p>

@endsection
