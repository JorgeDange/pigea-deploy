<div class="card-body">
  <div class="dashboard-filters">
    <select name="estado" wire:model.live="estado">
      <option value="">Estado: todos</option>
      <option value="Ativa">Ativa</option>
      <option value="Em análise">Em análise</option>
      <option value="Expirada">Expirada</option>
    </select>
    <input type="text" placeholder="Filtrar por data (mm/aaaa)" class="filter-search" wire:model.live.debounce.500ms="data">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th>Título</th><th>Data</th><th>Prazo</th><th>Candidaturas</th><th>Estado</th><th>Ações</th></tr></thead>
      <tbody>
        @forelse ($vagas as $vaga)
        <tr wire:key="vaga-{{ $vaga->id }}">
          <td>{{ $vaga->title }}</td>
          <td>{{ $vaga->published_at?->format('d/m/Y') ?? '—' }}</td>
          <td>{{ $vaga->deadline?->format('d/m/Y') ?? '—' }}</td>
          <td>{{ $vaga->applications_count }}</td>
          <td><span class="status-badge {{ $estados[$vaga->status][0] ?? 'pending' }}">{{ $estados[$vaga->status][1] ?? $vaga->status }}</span></td>
          <td><div class="row-actions"><button class="icon-btn " title="Editar"><i class="fa fa-pen"></i></button><button class="icon-btn " title="Ver candidaturas" wire:click="verCandidaturas({{ $vaga->id }})"><i class="fa fa-users"></i></button><button class="icon-btn danger" title="Eliminar" wire:click="eliminar({{ $vaga->id }})" wire:confirm="Eliminar este registo? Esta ação não pode ser desfeita."><i class="fa fa-trash"></i></button></div></td>
        </tr>
        @empty
        <tr><td colspan="6">Nenhuma vaga encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="dashboard-pagination">
    @for ($pagina = 1; $pagina <= $vagas->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $vagas->currentPage()])>{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($vagas->currentPage() + 1, $vagas->lastPage()) }})">»</a>
  </div>
</div>
