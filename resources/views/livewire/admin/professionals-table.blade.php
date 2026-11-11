<div class="card-body">
  <div class="dashboard-filters">
    <select name="estado" wire:model.live="estado" aria-label="Filtrar por estado">
      <option value="">Estado: todos</option>
      <option value="pendente">Pendente</option>
      <option value="aprovado">Aprovado</option>
      <option value="rejeitado">Rejeitado</option>
    </select>
    <input type="text" placeholder="Procurar profissional…" class="filter-search" wire:model.live.debounce.500ms="busca">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th scope="col">Nome</th><th scope="col">Formação Académica</th><th scope="col">Contactos</th><th scope="col">Áreas de Atuação</th><th scope="col">Estado</th><th scope="col">Ações</th></tr></thead>
      <tbody>
        @forelse ($profissionais as $profissional)
        <tr wire:key="profissional-{{ $profissional->id }}">
          <td>{{ $profissional->nome_completo }}</td>
          <td>{{ $profissional->formacao_academica }}</td>
          <td>{{ $profissional->contactos }}</td>
          <td>{{ collect($profissional->areas_atuacao)->implode(', ') ?: '—' }}</td>
          <td><span class="status-badge {{ ['pendente' => 'pending', 'aprovado' => 'active', 'rejeitado' => 'rejected'][$profissional->status] ?? 'pending' }}">{{ ['pendente' => 'Pendente', 'aprovado' => 'Aprovado', 'rejeitado' => 'Rejeitado'][$profissional->status] ?? $profissional->status }}</span></td>
          <td>
            <div class="row-actions">
              @if ($profissional->status !== 'aprovado')
              <button class="icon-btn success" title="Aprovar" wire:click="aprovar({{ $profissional->id }})"><i class="fa fa-check"></i></button>
              @endif
              @if ($profissional->status !== 'rejeitado')
              <button class="icon-btn danger" title="Rejeitar" wire:click="rejeitar({{ $profissional->id }})"><i class="fa fa-times"></i></button>
              @endif
              @if ($profissional->status !== 'pendente')
              <button class="icon-btn" title="Voltar a pendente" wire:click="restabelecer({{ $profissional->id }})"><i class="fa fa-undo"></i></button>
              @endif
              <button class="icon-btn danger" title="Eliminar" wire:click="eliminar({{ $profissional->id }})" wire:confirm="Eliminar o registo de «{{ $profissional->nome_completo }}»? Esta ação não pode ser desfeita."><i class="fa fa-trash"></i></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6">Nenhum profissional encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <nav class="dashboard-pagination" aria-label="Paginação">
    @for ($pagina = 1; $pagina <= $profissionais->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $profissionais->currentPage()]) aria-current="page">{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($profissionais->currentPage() + 1, $profissionais->lastPage()) }})">»</a>
  </div>
</div>
