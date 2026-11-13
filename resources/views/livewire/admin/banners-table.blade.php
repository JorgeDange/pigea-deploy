<div class="card-body">
  <div class="dashboard-filters">
    <select name="posicao" wire:model.live="posicao" aria-label="Filtrar por posição">
      <option value="">Posição: todas</option>
      <option value="topo">Topo</option>
      <option value="sidebar">Sidebar</option>
      <option value="lateral">Lateral (vertical)</option>
      <option value="rodape">Rodapé</option>
    </select>
    <input type="text" placeholder="Procurar banner…" class="filter-search" wire:model.live.debounce.500ms="busca">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th scope="col">Título</th><th scope="col">Imagem</th><th scope="col">Link de Destino</th><th scope="col">Posição</th><th scope="col">Período</th><th scope="col">Estado</th><th scope="col">Ações</th></tr></thead>
      <tbody>
        @forelse ($banners as $banner)
        <tr wire:key="banner-{{ $banner->id }}">
          <td>{{ $banner->titulo }}</td>
          <td>
            @if ($banner->imagem_url)
            <img src="{{ str_starts_with($banner->imagem_url, 'http') ? $banner->imagem_url : asset($banner->imagem_url) }}" alt="{{ $banner->titulo }}" style="max-width: 110px; max-height: 44px; object-fit: cover; border-radius: 3px;">
            @else —
            @endif
          </td>
          <td>@if ($banner->link_destino)<a href="{{ $banner->link_destino }}" target="_blank" rel="noopener">Abrir link</a>@else—@endif</td>
          {{-- posições com acentos e nomes claros para não-técnicos --}}
          <td>{{ ['topo' => 'Topo', 'sidebar' => 'Sidebar', 'lateral' => 'Lateral (vertical)', 'rodape' => 'Rodapé'][$banner->posicao] ?? ucfirst($banner->posicao) }}</td>
          <td>{{ ($banner->data_inicio?->format('d/m/Y') ?? '—').' → '.($banner->data_fim?->format('d/m/Y') ?? '—') }}</td>
          <td><span class="status-badge {{ $banner->ativo ? 'active' : 'neutral' }}">{{ $banner->ativo ? 'Ativo' : 'Inativo' }}</span></td>
          <td>
            <div class="row-actions">
              <button class="icon-btn {{ $banner->ativo ? 'danger' : '' }}" title="{{ $banner->ativo ? 'Desativar' : 'Ativar' }}" wire:click="alternarAtivo({{ $banner->id }})"><i class="fa {{ $banner->ativo ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
              <button class="icon-btn" title="Editar" wire:click="editar({{ $banner->id }})"><i class="fa fa-pen"></i></button>
              <button class="icon-btn danger js-confirm-livewire" title="Eliminar" data-action="eliminar" data-args='[{{ $banner->id }}]' data-title="Eliminar este registo?" data-message="Eliminar «{{ $banner->titulo }}»? Esta ação não pode ser desfeita." data-confirm-text="Eliminar"><i class="fa fa-trash"></i></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7">Nenhum banner encontrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('livewire.admin.pagination', ['registos' => $banners])
</div>
