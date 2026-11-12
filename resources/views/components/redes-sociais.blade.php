{{-- Widget "Siga-nos" — REDES SOCIAIS dinâmicas (tabela `settings`).
     Cada rede configurada em `admin/configuracoes` mostra:
       ícone + nome da rede (pequeno) + nome público da conta (grande)
     O «nome público» vem de `<rede>_nome` (ex.: "pigea.ao"); se vazio,
     usa-se o nome da rede.

     Sem NENHUMA rede configurada, mostramos os nomes das 4 redes como
     texto não-clicável (não inventamos links; o cliente configura no admin).
--}}
@php
    use App\Models\Setting;

    $definicoes = Setting::todas();
    $redes = [
        'facebook' => ['icone' => 'fab fa-facebook-f', 'classe' => 'facebook-soc', 'nome' => 'Facebook'],
        'instagram' => ['icone' => 'fab fa-instagram', 'classe' => 'instagram-soc', 'nome' => 'Instagram'],
        'linkedin' => ['icone' => 'fab fa-linkedin-in', 'classe' => 'linkedin-soc', 'nome' => 'LinkedIn'],
        'youtube' => ['icone' => 'fab fa-youtube', 'classe' => 'youtube-soc', 'nome' => 'YouTube'],
    ];
    $configuradas = count(array_filter(array_keys($redes), fn ($chave) => filled($definicoes[$chave] ?? null) || filled($definicoes[$chave.'_nome'] ?? null)));
@endphp
<div class="box-widget fl-wrap">
    <div class="widget-title">Siga-nos</div>
    <div class="box-widget-content">
        <div class="social-widget">
            @foreach ($redes as $chave => $rede)
                @php
                    $url = $definicoes[$chave] ?? null;
                    $handle = $definicoes[$chave . '_nome'] ?? null;
                @endphp
                @if (filled($url) || filled($handle))
                    @if (filled($url))
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="{{ $rede['classe'] }}">
                        <i class="{{ $rede['icone'] }}"></i>
                        <span class="soc-widget-title">{{ $rede['nome'] }}</span>
                        <span class="soc-widget_counter soc-widget-handle">{{ filled($handle) ? $handle : $rede['nome'] }}</span>
                    </a>
                    @else
                    {{-- Handle configurado sem link: mostra o nome, sem inventar href --}}
                    <span class="{{ $rede['classe'] }} soc-widget-estatico">
                        <i class="{{ $rede['icone'] }}"></i>
                        <span class="soc-widget-title">{{ $rede['nome'] }}</span>
                        <span class="soc-widget_counter soc-widget-handle">{{ $handle }}</span>
                    </span>
                    @endif
                @endif
            @endforeach
            @if ($configuradas === 0)
                @foreach ($redes as $rede)
                <span class="{{ $rede['classe'] }} soc-widget-estatico">
                    <i class="{{ $rede['icone'] }}"></i>
                    <span class="soc-widget-title">{{ $rede['nome'] }}</span>
                    <span class="soc-widget_counter soc-widget-handle">{{ $rede['nome'] }}</span>
                </span>
                @endforeach
            @endif
        </div>
    </div>
</div>
<!-- box-widget  end -->
