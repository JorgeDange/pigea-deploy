<div class="card-body">
  <div class="dashboard-filters">
    <select name="perfil" wire:model.live="perfil">
      <option value="">Perfil: todos</option>
      @foreach ($opcoesPerfil as $opcao)
      <option>{{ $opcao }}</option>
      @endforeach
    </select>
    <select name="estado" wire:model.live="estado">
      <option value="">Estado: todos</option>
      @foreach ($opcoesEstado as $opcao)
      <option>{{ $opcao }}</option>
      @endforeach
    </select>
    <input type="text" placeholder="Procurar utilizador…" class="filter-search" wire:model.live.debounce.500ms="busca">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th>Nome</th><th>Email</th><th>Perfil</th><th>Estado</th><th>Último acesso</th><th>Ações</th></tr></thead>
      <tbody>
        @forelse ($utilizadores as $utilizador)
        <tr wire:key="utilizador-{{ $utilizador->id }}">
          <td><div class="table-user"><img src="{{ asset($utilizador->avatar ?: 'images/avatar/1.jpg') }}" alt=""><span>{{ $utilizador->name }}<small>{{ $utilizador->email }}</small></span></div></td>
          <td>{{ $utilizador->email }}</td>
          <td>{{ $rotulos[$utilizador->getRoleNames()->first()] ?? '—' }}</td>
          <td><span class="status-badge {{ $badges[$utilizador->status ?? 'pending'][0] ?? 'pending' }}">{{ $badges[$utilizador->status ?? 'pending'][1] ?? $utilizador->status }}</span></td>
          <td>{{ $utilizador->last_login_at?->format('d/m/Y · H:i') ?? '—' }}</td>
          <td><div class="row-actions"><button class="icon-btn " title="Editar"><i class="fa fa-pen"></i></button><button class="icon-btn " title="Bloquear" wire:click="bloquear({{ $utilizador->id }})"><i class="fa fa-ban"></i></button><button class="icon-btn danger" title="Eliminar" wire:click="eliminar({{ $utilizador->id }})" wire:confirm="Eliminar este utilizador? Esta ação não pode ser desfeita."><i class="fa fa-trash"></i></button></div></td>
        </tr>
        @empty
        <tr><td colspan="6">Nenhum utilizador encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="dashboard-pagination">
    @for ($pagina = 1; $pagina <= $utilizadores->lastPage(); $pagina++)
    <a href="#" wire:click="gotoPage({{ $pagina }})" @class(['active' => $pagina === $utilizadores->currentPage()])>{{ $pagina }}</a>
    @endfor
    <a href="#" wire:click="gotoPage({{ min($utilizadores->currentPage() + 1, $utilizadores->lastPage()) }})">»</a>
  </div>
</div>
