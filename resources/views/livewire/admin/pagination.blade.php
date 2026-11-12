{{-- Paginação com janela deslizante (FASE C, melhoria.md §4.3).
     Mostra: 1 … atual±1 … última em vez de TODOS os botões.
     Props: $registos — LengthAwarePaginator do Livewire (WithPagination).
     Usado por todas as tabelas do painel (build-admin-css / melhoria.md). --}}
@php
    $atual = max(1, (int) $registos->currentPage());
    $ultima = max(1, (int) $registos->lastPage());

    // janela: primeira página, atual±1 e última — sem duplicados, ordenado
    $paginas = collect([1])
        ->merge(range(max(2, $atual - 1), min($ultima - 1, $atual + 1)))
        ->when($ultima > 1, fn ($c) => $c->push($ultima))
        ->unique()
        ->sort()
        ->values();
    $anterior = null;
@endphp
<nav class="dashboard-pagination" aria-label="Paginação">
    <a href="#" wire:click="gotoPage({{ max(1, $atual - 1) }})" @class(['disabled' => $atual <= 1]) aria-label="Página anterior" aria-disabled="{{ $atual <= 1 ? 'true' : 'false' }}">&laquo;</a>
    @foreach ($paginas as $pagina)
        @if ($anterior !== null && $pagina - $anterior > 1)
        <span class="page-ellipsis" aria-hidden="true">&hellip;</span>
        @endif
        <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $atual]) @if ($pagina === $atual) aria-current="page" @endif>{{ $pagina }}</a>
        @php $anterior = $pagina; @endphp
    @endforeach
    <a href="#" wire:click="gotoPage({{ min($ultima, $atual + 1) }})" @class(['disabled' => $atual >= $ultima]) aria-label="Página seguinte" aria-disabled="{{ $atual >= $ultima ? 'true' : 'false' }}">&raquo;</a>
</nav>
