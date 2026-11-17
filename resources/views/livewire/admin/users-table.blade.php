<div class="card-body">
  <div class="dashboard-filters">
    <select name="perfil" wire:model.live="perfil" aria-label="Filtrar por perfil">
      <option value="">Perfil: todos</option>
      @foreach ($opcoesPerfil as $opcao)
      <option>{{ $opcao }}</option>
      @endforeach
    </select>
    <select name="estado" wire:model.live="estado" aria-label="Filtrar por estado">
      <option value="">Estado: todos</option>
      @foreach ($opcoesEstado as $opcao)
      <option>{{ $opcao }}</option>
      @endforeach
    </select>
    <input type="text" placeholder="Procurar utilizador…" class="filter-search" wire:model.live.debounce.500ms="busca">
    <select name="porPagina" wire:model.live="porPagina" aria-label="Registos por página">
      @foreach ($opcoesPorPagina as $n)
      <option value="{{ $n }}">{{ $n }} por página</option>
      @endforeach
    </select>
  </div>

  @if ($erro)
    <p class="form-error" role="alert" style="margin: 8px 0 0;">{{ $erro }}</p>
  @endif
  @if ($ok)
    <p class="form-success" role="status" style="margin: 8px 0 0; color: #2E7D32; font-weight: 600;">{{ $ok }}</p>
  @endif

  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th scope="col">Nome</th><th scope="col">Email</th><th scope="col">Perfil</th><th scope="col">Estado</th><th scope="col">Criado</th><th scope="col">Último acesso</th><th scope="col">Ações</th></tr></thead>
      <tbody>
        @forelse ($utilizadores as $utilizador)
        <tr wire:key="utilizador-{{ $utilizador->id }}">
          <td><div class="table-user"><img src="{{ asset($utilizador->avatar ?: 'images/avatar/1.jpg') }}" alt=""><span>{{ $utilizador->name }}<small>{{ $utilizador->email }}</small></span></div></td>
          <td>{{ $utilizador->email }}</td>
          <td>{{ $rotulos[$utilizador->getRoleNames()->first()] ?? '—' }}</td>
          <td><span class="status-badge {{ $badges[$utilizador->status ?? 'pending'][0] ?? 'pending' }}">{{ $badges[$utilizador->status ?? 'pending'][1] ?? $utilizador->status }}</span></td>
          <td>{{ $utilizador->created_at?->format('d/m/Y') }}</td>
          <td>{{ $utilizador->last_login_at?->format('d/m/Y H:i') ?? '—' }}</td>
          <td>
            @if ($aEliminar === $utilizador->id)
              <div class="confirm-eliminar" style="display:flex; gap:6px; align-items:center; flex-wrap: wrap;">
                <input type="email" class="dashboard-form-input" style="min-width:160px; max-width:210px;" wire:model.live.debounce.300ms="confirmarEmail" placeholder="Escreva o email da conta" aria-label="Escreva o email para eliminar">
                <button type="button" class="icon-btn danger" wire:click="eliminar()" title="Confirmar eliminação" aria-label="Confirmar eliminação"><i class="fa fa-check" aria-hidden="true"></i></button>
                <button type="button" class="icon-btn" wire:click="cancelarEliminar()" title="Cancelar" aria-label="Cancelar"><i class="fa fa-times" aria-hidden="true"></i></button>
              </div>
            @else
              <div class="row-actions">
                <a class="icon-btn" href="{{ route('admin.utilizador.editar', $utilizador) }}" title="Gerir (dados e senha)" aria-label="Gerir utilizador"><i class="fa fa-edit" aria-hidden="true"></i></a>
                @if (($utilizador->status ?? 'active') === 'blocked')
                  <button class="icon-btn" title="Reativar" wire:click="alternar({{ $utilizador->id }})"><i class="fa fa-unlock" aria-hidden="true"></i></button>
                @else
                  <button class="icon-btn" title="Desativar" wire:click="alternar({{ $utilizador->id }})"><i class="fa fa-ban" aria-hidden="true"></i></button>
                @endif
                <button class="icon-btn danger js-confirm-livewire" title="Eliminar" data-action="pedirEliminar" data-args='[{{ $utilizador->id }}]' data-title="Eliminar utilizador?" data-message="Eliminar este utilizador? Esta ação não pode ser desfeita. Depois de confirmar, terá de escrever o email da conta para executar." data-confirm-text="Eliminar"><i class="fa fa-trash" aria-hidden="true"></i></button>
              </div>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="7">Nenhum utilizador encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('livewire.admin.pagination', ['registos' => $utilizadores])
</div>
