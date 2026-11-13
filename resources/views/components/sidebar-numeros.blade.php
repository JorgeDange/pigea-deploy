{{-- Widget "PIGEA em números" — valores REAIS da base de dados (Fase 15).
     Substitui os números hardcoded que estavam no protótipo (312/1.480/96/54).

     Decisão documental: a vaga "Vagas de emprego ativas" NÃO tem tabela no
     sistema (o registo é projects/companies/research/news), por isso foi
     substituída por "Notícias publicadas" (o cliente pediu expressamente). --}}
@php
    use App\Models\Company;
    use App\Models\News;
    use App\Models\Project;
    use App\Models\Research;

    $numeros = [
        ['valor' => News::published()->count(), 'rotulo' => 'Notícias publicadas'],
        ['valor' => Project::published()->count(), 'rotulo' => 'Projetos mineiros mapeados'],
        ['valor' => Company::where('status', 'aprovado')->count(), 'rotulo' => 'Empresas no diretório'],
        ['valor' => Research::published()->count(), 'rotulo' => 'Estudos de investigação'],
    ];
@endphp
<div class="box-widget fl-wrap">
    <div class="widget-title">PIGEA em números</div>
    <div class="box-widget-content">
        @foreach ($numeros as $i => $item)
        <div style="display:flex;align-items:baseline;justify-content:space-between;gap:12px;padding:12px 0;{{ $i < count($numeros) - 1 ? 'border-bottom:1px solid #EFEFEF;' : '' }}">
            <span style="font-family:Poppins,sans-serif;font-size:28px;font-weight:600;color:#C8102E;line-height:1;">{{ number_format($item['valor'], 0, ',', '.') }}</span>
            <span style="font-family:Poppins,sans-serif;font-size:13px;color:#1A1A1A;text-align:right;line-height:1.4;">{{ $item['rotulo'] }}</span>
        </div>
        @endforeach
    </div>
</div>
<!-- box-widget  end -->
