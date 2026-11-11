<div class="card-body">
  <div class="dashboard-filters">
    <select name="estado" wire:model.live="estado" aria-label="Filtrar por estado">
      <option value="">Estado: todos</option>
      <option value="pendente">Pendente</option>
      <option value="aprovado">Aprovada</option>
      <option value="rejeitado">Rejeitada</option>
    </select>
    <input type="text" placeholder="Procurar empresa…" class="filter-search" wire:model.live.debounce.500ms="busca">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th scope="col">Nome</th><th scope="col">NIF</th><th scope="col">Contactos</th><th scope="col">Áreas de Atuação</th><th scope="col">Estado</th><th scope="col">Ações</th></tr></thead>
      <tbody>
        @forelse ($empresas as $empresa)
        <tr wire:key="empresa-{{ $empresa->id }}">
          <td>{{ $empresa->nome_empresa }}</td>
          <td>{{ $empresa->nif }}</td>
          <td>{{ $empresa->contactos }}</td>
          <td>{{ collect($empresa->areas_atuacao)->implode(', ') ?: '—' }}</td>
          <td><span class="status-badge {{ ['pendente' => 'pending', 'aprovado' => 'active', 'rejeitado' => 'rejected'][$empresa->status] ?? 'pending' }}">{{ ['pendente' => 'Pendente', 'aprovado' => 'Aprovada', 'rejeitado' => 'Rejeitada'][$empresa->status] ?? $empresa->status }}</span></td>
          <td>
            <div class="row-actions">
              @if ($empresa->status !== 'aprovado')
              <button class="icon-btn success" title="Aprovar" wire:click="aprovar({{ $empresa->id }})"><i class="fa fa-check"></i></button>
              @endif
              @if ($empresa->status !== 'rejeitado')
              <button class="icon-btn danger" title="Rejeitar" wire:click="rejeitar({{ $empresa->id }})"><i class="fa fa-times"></i></button>
              @endif
              @if ($empresa->status !== 'pendente')
              <button class="icon-btn" title="Voltar a pendente" wire:click="restabelecer({{ $empresa->id }})"><i class="fa fa-undo"></i></button>
              @endif
              <button class="icon-btn danger" title="Eliminar" wire:click="eliminar({{ $empresa->id }})" wire:confirm="Eliminar o registo de «{{ $empresa->nome_empresa }}»? Esta ação não pode ser desfeita."><i class="fa fa-trash"></i></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6">Nenhuma empresa encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <nav class="dashboard-pagination" aria-label="Paginação">
    @for ($pagina = 1; $pagina <= $empresas->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $empresas->currentPage()]) aria-current="page">{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($empresas->currentPage() + 1, $empresas->lastPage()) }})">»</a>
  </div>
</div>
