<div class="card-body">
  <div class="dashboard-filters">
    <input type="text" placeholder="Procurar vídeos…" class="filter-search" wire:model.live.debounce.500ms="busca" aria-label="Procurar por título ou link">
  </div>
  <div class="table-wrap">
    <table class="dashboard-table">
      <thead><tr><th scope="col">Título</th><th scope="col">Link</th><th scope="col">Publicado em</th><th scope="col">Estado</th><th scope="col">Ações</th></tr></thead>
      <tbody>
        @forelse ($videos as $video)
        <tr wire:key="video-{{ $video->id }}">
          <td>{{ $video->titulo }}</td>
          <td><a href="{{ $video->url }}" target="_blank" rel="noopener" style="max-width:220px;">{{ \Illuminate\Support\Str::limit($video->url, 44) }}</a></td>
          <td>{{ $video->published_at?->translatedFormat('d/m/Y H:i') ?? '—' }}</td>
          <td><span class="status-badge active">Publicado</span></td>
          <td>
            <div class="row-actions"><a class="icon-btn" href="{{ route('admin.videos.editar', $video->id) }}" title="Editar"><i class="fa fa-pen"></i></a>
            <button class="icon-btn danger js-confirm-livewire" title="Eliminar" data-action="eliminar" data-args='[{{ $video->id }}]' data-title="Eliminar este vídeo?" data-message="O vídeo será removido da base de dados."><i class="fa fa-trash"></i></button>
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="5">Nenhum vídeo publicado. Use «Novo vídeo» para publicar o primeiro.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('livewire.admin.pagination', ['registos' => $videos])
</div>
