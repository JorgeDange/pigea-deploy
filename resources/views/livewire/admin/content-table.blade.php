<div class="card-body">
  <div class="dashboard-filters">
    @foreach ($conf['filtros'] as $filtro)
      @if (($filtro['campo'] ?? '') === 'categoria')
      <select name="{{ $filtro['nome'] }}" wire:model.live="filtros.{{ $filtro['nome'] }}">
        <option value="">{{ $filtro['rotulo'] }}</option>
        @foreach ($categorias as $categoria)
        <option value="{{ $categoria->id }}">{{ $categoria->name }}</option>
        @endforeach
      </select>
      @elseif (!empty($filtro['campo']))
      <select name="{{ $filtro['nome'] }}" wire:model.live="filtros.{{ $filtro['nome'] }}">
        <option value="">{{ $filtro['rotulo'] }}</option>
        @foreach ($filtro['opcoes'] ?? [] as $opcao)
        <option value="{{ $opcao }}">{{ $opcao }}</option>
        @endforeach
      </select>
      @else
      <select name="{{ $filtro['nome'] }}">
        <option value="">{{ $filtro['rotulo'] }}</option>
        @foreach ($filtro['opcoes'] ?? [] as $opcao)
        <option>{{ $opcao }}</option>
        @endforeach
      </select>
      @endif
    @endforeach
    @if (!empty($conf['busca']))
    <input type="text" placeholder="{{ $conf['busca']['placeholder'] }}" class="filter-search" wire:model.live.debounce.500ms="busca">
    @endif
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr>@foreach ($conf['colunas'] as $coluna)<th>{{ $coluna['rotulo'] }}</th>@endforeach<th>Ações</th></tr></thead>
      <tbody>
        @forelse ($registos as $registo)
        <tr wire:key="registo-{{ $registo->id }}">
          @foreach ($conf['colunas'] as $coluna)
          <td>
            @switch($coluna['campo'])
              @case('title'){{ $registo->title }}@break
              @case('categoria'){{ $registo->category?->name ?? '—' }}@break
              @case('autor'){{ $registo->author?->name ?? '—' }}@break
              @case('data'){{ $registo->published_at?->format('d/m/Y') ?? '—' }}@break
              @case('ano'){{ $registo->published_at?->year ?? '—' }}@break
              @case('estado')<span class="status-badge {{ $conf['estado'][$registo->status][0] ?? 'pending' }}">{{ $conf['estado'][$registo->status][1] ?? $registo->status }}</span>@break
              @default
                @if (empty($coluna['campo']) || $registo->{$coluna['campo']} === null) —@else{{ $conf['mapas'][$coluna['campo']][$registo->{$coluna['campo']}] ?? $registo->{$coluna['campo']} }}@endif
            @endswitch
          </td>
          @endforeach
          <td>
            @if ($conf['acoes'] === 'aprovar' && in_array($registo->status, ['draft', 'pending'], true))
            <div class="row-actions"><button class="icon-btn " title="Aprovar" wire:click="aprovar({{ $registo->id }})"><i class="fa fa-check"></i></button><button class="icon-btn danger" title="Rejeitar" wire:click="rejeitar({{ $registo->id }})"><i class="fa fa-times"></i></button><button class="icon-btn " title="Ver" wire:click="ver({{ $registo->id }})"><i class="fa fa-eye"></i></button></div>
            @else
            <div class="row-actions"><button class="icon-btn " title="Editar" wire:click="editar({{ $registo->id }})"><i class="fa fa-pen"></i></button>@if (!empty($conf['ver']))<button class="icon-btn " title="Ver" wire:click="ver({{ $registo->id }})"><i class="fa fa-eye"></i></button>@else<button class="icon-btn " title="Ver"><i class="fa fa-eye"></i></button>@endif<button class="icon-btn danger" title="Eliminar" wire:click="eliminar({{ $registo->id }})" wire:confirm="Eliminar este registo? Esta ação não pode ser desfeita."><i class="fa fa-trash"></i></button></div>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="{{ count($conf['colunas']) + 1 }}">{{ $conf['vazio'] ?? 'Nenhum registo encontrado.' }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="dashboard-pagination">
    @for ($pagina = 1; $pagina <= $registos->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $registos->currentPage()])>{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($registos->currentPage() + 1, $registos->lastPage()) }})">»</a>
  </div>
</div>
