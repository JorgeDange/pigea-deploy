<div class="card-body">
  <div class="dashboard-filters">
    <select name="estado" wire:model.live="estado">
      <option value="">Estado: todas</option>
      <option value="em-analise">Em análise</option>
      <option value="aceite">Aceite</option>
      <option value="rejeitada">Rejeitada</option>
    </select>
    <input type="text" placeholder="Filtrar por data (mm/aaaa)" class="filter-search" wire:model.live.debounce.500ms="data">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th>Vaga</th><th>Empresa</th><th>Data</th><th>Estado</th><th>Ações</th></tr></thead>
      <tbody>
        @forelse ($candidaturas as $candidatura)
        <tr wire:key="candidatura-{{ $candidatura->id }}">
          <td>{{ $candidatura->job?->title ?? '—' }}</td>
          <td>{{ $candidatura->job?->author?->name ?? '—' }}</td>
          <td>{{ $candidatura->created_at->format('d/m/Y') }}</td>
          <td><span class="status-badge {{ \App\Models\Application::BADGES[$candidatura->status][0] ?? 'pending' }}">{{ \App\Models\Application::BADGES[$candidatura->status][1] ?? $candidatura->status }}</span></td>
          <td><div class="row-actions"><button class="icon-btn " title="Ver" wire:click="ver({{ $candidatura->id }})"><i class="fa fa-eye"></i></button><button class="icon-btn danger" title="Retirar" wire:click="retirar({{ $candidatura->id }})" wire:confirm="Retirar esta candidatura? Esta ação não pode ser desfeita."><i class="fa fa-times"></i></button></div></td>
        </tr>
        @empty
        <tr><td colspan="5">Nenhuma candidatura encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="dashboard-pagination">
    @for ($pagina = 1; $pagina <= $candidaturas->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $candidaturas->currentPage()])>{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($candidaturas->currentPage() + 1, $candidaturas->lastPage()) }})">»</a>
  </div>
</div>
