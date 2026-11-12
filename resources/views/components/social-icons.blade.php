{{-- Ícones das redes sociais configuradas (`settings`) — usados no rodapé
     e na página de Contactos. Sem nenhuma rede configurada, mostra os 4
     nomes nativos como texto não clicável. --}}
@php
    use App\Models\Setting;

    $definicoes = Setting::todas();
    $redes = [
        'facebook' => ['icone' => 'fab fa-facebook-f', 'nome' => 'Facebook'],
        'instagram' => ['icone' => 'fab fa-instagram', 'nome' => 'Instagram'],
        'linkedin' => ['icone' => 'fab fa-linkedin-in', 'nome' => 'LinkedIn'],
        'youtube' => ['icone' => 'fab fa-youtube', 'nome' => 'YouTube'],
    ];
    $configuradas = array_intersect_key($redes, array_filter($redes, fn ($r, $chave) => filled($definicoes[$chave] ?? null), ARRAY_FILTER_USE_BOTH));
    $mostrar = $configuradas === [] ? $redes : $configuradas;
@endphp
@foreach ($mostrar as $chave => $rede)
    @php($url = $definicoes[$chave] ?? null)
    <li>
        @if (filled($url))
            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $rede['nome'] }}"><i class="{{ $rede['icone'] }}" aria-hidden="true"></i></a>
        @else
            <span aria-label="{{ $rede['nome'] }} — por configurar"><i class="{{ $rede['icone'] }}" aria-hidden="true"></i></span>
        @endif
    </li>
@endforeach
