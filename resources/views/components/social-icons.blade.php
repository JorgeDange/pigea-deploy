{{-- Ícones das redes sociais do site (`settings`) — usados no rodapé, no
     topo do site, nos cartões e na página de Contactos.

     Apenas as 5 redes aprovadas pelo cliente (2026-11-13):
     Facebook, Instagram, LinkedIn, YouTube e X (Twitter).

     Uma rede só aparece quando configurada no admin (link OU nome público).
     Sem link não inventamos href — mostramos o ícone não clicável com o
     nome público na etiqueta. Sem NENHUMA rede configurada, não mostra
     nada (nada de ícones fantasma). --}}
@php
    use App\Models\Setting;

    $definicoes = Setting::todas();
    $redes = [
        'facebook' => ['icone' => 'fab fa-facebook-f', 'nome' => 'Facebook'],
        'instagram' => ['icone' => 'fab fa-instagram', 'nome' => 'Instagram'],
        'linkedin' => ['icone' => 'fab fa-linkedin-in', 'nome' => 'LinkedIn'],
        'youtube' => ['icone' => 'fab fa-youtube', 'nome' => 'YouTube'],
        'twitter' => ['icone' => 'fab fa-twitter', 'nome' => 'X (Twitter)'],
    ];
@endphp
@foreach ($redes as $chave => $rede)
    @php
        $url = trim((string) ($definicoes[$chave] ?? ''));
        $handle = trim((string) ($definicoes[$chave . '_nome'] ?? ''));
    @endphp
    @if ($url !== '' || $handle !== '')
        <li>
            @if ($url !== '')
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $rede['nome'] }}"><i class="{{ $rede['icone'] }}" aria-hidden="true"></i></a>
            @else
                <span role="img" aria-label="{{ $rede['nome'] }} — {{ $handle }}" title="{{ $handle }}"><i class="{{ $rede['icone'] }}" aria-hidden="true"></i></span>
            @endif
        </li>
    @endif
@endforeach
