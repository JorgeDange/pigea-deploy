<div class="card-body">
  <div class="dashboard-filters">
    <input type="text" placeholder="Procurar notícias…" class="filter-search" wire:model.live.debounce.500ms="busca" aria-label="Procurar por título">

    <select wire:model.live="filtros.estado" aria-label="Filtrar por estado">
      <option value="">Estado: todos</option>
      <option value="published">Publicado</option>
      <option value="draft">Rascunho</option>
      <option value="em_revisao">Em revisão</option>
    </select>

    <select wire:model.live="filtros.categoria" aria-label="Filtrar por categoria">
      <option value="">Categoria: todas</option>
      @foreach ($categoriasTop as $grupo)
        @php $temFilhos = isset($categoriasFilhos[$grupo->id]) && $categoriasFilhos[$grupo->id]->count() > 0; @endphp
        @if ($temFilhos)
        <optgroup label="{{ $grupo->name }}">
          <option value="{{ $grupo->id }}">{{ $grupo->name }} (geral)</option>
          @foreach ($categoriasFilhos[$grupo->id] as $filho)
          <option value="{{ $filho->id }}">{{ $filho->name }}</option>
          @endforeach
        </optgroup>
        @else
        <option value="{{ $grupo->id }}">{{ $grupo->name }}</option>
        @endif
      @endforeach
    </select>

    <select wire:model.live="filtros.autor" aria-label="Filtrar por autor">
      <option value="">Autor: todos</option>
      @foreach ($autores as $autor)
      <option value="{{ $autor->id }}">{{ $autor->name }}</option>
      @endforeach
    </select>

    <label class="dashboard-checkbox">
      <input type="checkbox" wire:model.live="mostrarApagados">
      Só apagados (lixo)
    </label>
  </div>

  <div class="table-wrap">
    <table class="dashboard-table">
      <thead>
        <tr>
          <th scope="col"><button type="button" class="dashboard-table-sort" wire:click="ordenarPor('title')">Título <span aria-hidden="true">{{ $ordenar === 'title' ? ($direccao === 'asc' ? '↑' : '↓') : '' }}</span></button></th>
          <th scope="col">Categoria</th>
          <th scope="col">Autor</th>
          <th scope="col">Estado</th>
          <th scope="col"><button type="button" class="dashboard-table-sort" wire:click="ordenarPor('published_at')">Data <span aria-hidden="true">{{ $ordenar === 'published_at' ? ($direccao === 'asc' ? '↑' : '↓') : '' }}</span></button></th>
          <th scope="col"><button type="button" class="dashboard-table-sort" wire:click="ordenarPor('views_count')">Visitas <span aria-hidden="true">{{ $ordenar === 'views_count' ? ($direccao === 'asc' ? '↑' : '↓') : '' }}</span></button></th>
          <th scope="col">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($registos as $registo)
        <tr wire:key="registo-{{ $registo->id }}">
          <td><strong>{{ $registo->title }}</strong></td>
          <td>{{ $registo->category?->name ?? '—' }}</td>
          <td>{{ $registo->author?->name ?? '—' }}</td>
          <td>
            @php
              $badge = ['published' => ['active', 'Publicado'], 'draft' => ['neutral', 'Rascunho'], 'em_revisao' => ['pending', 'Em revisão']][$registo->status] ?? ['neutral', $registo->status];
            @endphp
            <span class="status-badge {{ $badge[0] }}">{{ $badge[1] }}</span>
          </td>
          <td>{{ $registo->published_at?->format('d/m/Y') ?? '—' }}</td>
          <td>{{ number_format((int) $registo->views_count, 0, ',', ' ') }}</td>
          <td>
            <div class="row-actions">
              @if ($mostrarApagados)
              <button type="button" class="icon-btn success" title="Restaurar" wire:click="restaurar({{ $registo->id }})"><i class="fa fa-undo" aria-hidden="true"></i></button>
              @else
              <button type="button" class="icon-btn" title="Editar" wire:click="editar({{ $registo->id }})"><i class="fa fa-pen" aria-hidden="true"></i></button>
              @if ($registo->status === 'published')
              <button type="button" class="icon-btn" title="Ver no site" wire:click="ver({{ $registo->id }})"><i class="fa fa-eye" aria-hidden="true"></i></button>
              @endif
              <button type="button" class="icon-btn" title="Duplicar (rascunho)" wire:click="duplicar({{ $registo->id }})"><i class="fa fa-copy" aria-hidden="true"></i></button>
              <div x-data="{ aberto: false }" style="display: contents">
                <button type="button" class="icon-btn danger" title="Apagar" @click="aberto = true"><i class="fa fa-trash" aria-hidden="true"></i></button>
                <div x-show="aberto" x-cloak class="dashboard-modal open" role="dialog" aria-modal="true" aria-label="Apagar notícia">
                  <div class="dashboard-modal-content">
                    <div class="dashboard-modal-header">
                      <h3>Apagar notícia</h3>
                      <button type="button" class="dashboard-modal-close" @click="aberto = false" aria-label="Fechar"><i class="fa fa-times" aria-hidden="true"></i></button>
                    </div>
                    <div class="dashboard-modal-body">
                      <p>Eliminar <strong>{{ $registo->title }}</strong>? O registo vai para o lixo e pode ser restaurado depois.</p>
                    </div>
                    <div class="dashboard-modal-footer">
                      <button type="button" class="btn-ghost" @click="aberto = false">Cancelar</button>
                      <button type="button" class="btn-danger" wire:click="eliminar({{ $registo->id }})" @click="aberto = false">Apagar</button>
                    </div>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <i class="fa fa-newspaper empty-state-icon" aria-hidden="true"></i>
              <p class="empty-state-title">Nada encontrado</p>
              <p class="empty-state-text">Nenhuma notícia corresponde à pesquisa ou aos filtros atuais. Experimenta limpar a busca ou remover os filtros.</p>
              @if ($busca !== '' || implode('', array_filter($filtros)) !== '')
              <button type="button" class="btn-secondary" wire:click="atualizarTabela">Limpar pesquisa e filtros</button>
              @endif
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <nav class="dashboard-pagination" aria-label="Paginação">
    @for ($pagina = 1; $pagina <= $registos->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $registos->currentPage()]) aria-current="page">{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($registos->currentPage() + 1, $registos->lastPage()) }})" aria-label="Página seguinte">»</a>
  </nav>
</div>
