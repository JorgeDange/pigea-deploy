<div class="card-body">
  <div class="dashboard-filters">
    <select name="tipo" wire:model.live="tipo" aria-label="Filtrar por tipo">
      <option value="">Tipo: todos</option>
      <option value="artigo">Artigo</option>
      <option value="documento">Documento</option>
      <option value="lei">Lei</option>
    </select>
    <input type="text" placeholder="Procurar documento…" class="filter-search" wire:model.live.debounce.500ms="busca">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th scope="col">Título</th><th scope="col">Descrição</th><th scope="col">Tipo</th><th scope="col">Link de Download</th><th scope="col">Áreas</th><th scope="col">Ações</th></tr></thead>
      <tbody>
        @forelse ($documentos as $documento)
        <tr wire:key="documento-{{ $documento->id }}">
          <td>{{ $documento->titulo }}</td>
          <td>{{ Str::limit($documento->descricao, 60) }}</td>
          <td>{{ $documento->tipo_label }}</td>
          <td><a href="{{ $documento->link_download }}" target="_blank" rel="noopener" title="{{ $documento->link_download }}">Abrir link</a></td>
          <td>{{ collect($documento->areas_atuacao)->implode(', ') ?: '—' }}</td>
          <td>
            <div class="row-actions">
              <button class="icon-btn" title="Editar" wire:click="editar({{ $documento->id }})"><i class="fa fa-pen"></i></button>
              <button class="icon-btn danger js-confirm-livewire" title="Eliminar" data-action="eliminar" data-args='[{{ $documento->id }}]' data-title="Eliminar este registo?" data-message="Eliminar «{{ $documento->titulo }}»? Esta ação não pode ser desfeita." data-confirm-text="Eliminar"><i class="fa fa-trash"></i></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6">Nenhum documento encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <nav class="dashboard-pagination" aria-label="Paginação">
    @for ($pagina = 1; $pagina <= $documentos->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $documentos->currentPage()]) aria-current="page">{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($documentos->currentPage() + 1, $documentos->lastPage()) }})">»</a>
  </div>
</div>
