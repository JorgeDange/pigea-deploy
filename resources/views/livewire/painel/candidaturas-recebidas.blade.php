<div class="card-body">
  <div class="dashboard-filters">
    <select name="vaga" wire:model.live="vaga">
      <option value="">Vaga: todas</option>
      @foreach ($vagas as $opcao)
      <option value="{{ $opcao->id }}">{{ $opcao->title }}</option>
      @endforeach
    </select>
    <select name="estado" wire:model.live="estado">
      <option value="">Estado: todos</option>
      <option value="em-analise">Em análise</option>
      <option value="aceite">Aceite</option>
      <option value="rejeitada">Rejeitada</option>
    </select>
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th>Candidato</th><th>Vaga</th><th>Data</th><th>Estado</th><th>Ações</th></tr></thead>
      <tbody>
        @forelse ($candidaturas as $candidatura)
        <tr wire:key="recebida-{{ $candidatura->id }}">
          <td><div class="table-user"><img src="{{ asset('images/avatar/'.(($candidatura->id % 5) + 2).'.jpg') }}" alt=""><span>{{ $candidatura->name }}<small>{{ $candidatura->email }}</small></span></div></td>
          <td>{{ $candidatura->job?->title ?? '—' }}</td>
          <td>{{ $candidatura->created_at->format('d/m/Y') }}</td>
          <td><span class="status-badge {{ \App\Models\Application::BADGES[$candidatura->status][0] ?? 'pending' }}">{{ \App\Models\Application::BADGES[$candidatura->status][1] ?? $candidatura->status }}</span></td>
          <td><div class="row-actions"><button class="icon-btn " title="Ver CV" wire:click="verCv({{ $candidatura->id }})"><i class="fa fa-file-alt"></i></button><button class="icon-btn " title="Aceitar" wire:click="aceitar({{ $candidatura->id }})"><i class="fa fa-check"></i></button><button class="icon-btn danger" title="Rejeitar" wire:click="rejeitar({{ $candidatura->id }})"><i class="fa fa-times"></i></button></div></td>
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
