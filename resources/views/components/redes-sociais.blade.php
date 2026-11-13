{{-- Widget "Siga-nos" — REDES SOCIAIS dinâmicas (tabela `settings`).
     Cada rede configurada em `admin/configuracoes` mostra:
       ícone + nome da rede (pequeno) + nome público da conta (grande)
     O «nome público» vem de `<rede>_nome` (ex.: "pigea.ao"); se vazio,
     usa-se o nome da rede.

     Apenas as 5 redes aprovadas pelo cliente (2026-11-13):
     Facebook, Instagram, LinkedIn, YouTube e X (Twitter).

     Uma rede só aparece com link OU nome público configurado. Sem link
     não inventamos href — mostramos o nome sem ligação. Sem NENHUMA rede
     configurada, não mostra nada (nada de redes fantasma).
--}}
@php
    use App\Models\Setting;

    $definicoes = Setting::todas();
    $redes = [
        'facebook' => ['icone' => 'fab fa-facebook-f', 'classe' => 'facebook-soc', 'nome' => 'Facebook'],
        'instagram' => ['icone' => 'fab fa-instagram', 'classe' => 'instagram-soc', 'nome' => 'Instagram'],
        'linkedin' => ['icone' => 'fab fa-linkedin-in', 'classe' => 'linkedin-soc', 'nome' => 'LinkedIn'],
        'youtube' => ['icone' => 'fab fa-youtube', 'classe' => 'youtube-soc', 'nome' => 'YouTube'],
        'twitter' => ['icone' => 'fab fa-twitter', 'classe' => 'twitter-soc', 'nome' => 'X (Twitter)'],
    ];
    $afixar = array_filter(array_keys($redes), function (string $chave) use ($definicoes): bool {
        return filled($definicoes[$chave] ?? null) || filled($definicoes[$chave . '_nome'] ?? null);
    });
@endphp
@if (count($afixar) > 0)
<div class="box-widget fl-wrap">
    <div class="widget-title">Siga-nos</div>
    <div class="box-widget-content">
        <div class="social-widget">
            @foreach ($afixar as $chave)
                @php
                    $rede = $redes[$chave];
                    $url = trim((string) ($definicoes[$chave] ?? ''));
                    $handle = trim((string) ($definicoes[$chave . '_nome'] ?? ''));
                @endphp
                @if ($url !== '')
                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="{{ $rede['classe'] }}">
                        <i class="{{ $rede['icone'] }}"></i>
                        <span class="soc-widget-title">{{ $rede['nome'] }}</span>
                        <span class="soc-widget_counter soc-widget-handle">{{ $handle !== '' ? $handle : $rede['nome'] }}</span>
                    </a>
                @else
                    {{-- Handle configurado sem link: mostra o nome, sem inventar href --}}
                    <span class="{{ $rede['classe'] }} soc-widget-estatico">
                        <i class="{{ $rede['icone'] }}"></i>
                        <span class="soc-widget-title">{{ $rede['nome'] }}</span>
                        <span class="soc-widget_counter soc-widget-handle">{{ $handle }}</span>
                    </span>
                @endif
            @endforeach
        </div>
    </div>
</div>
<!-- box-widget  end -->
@endif
