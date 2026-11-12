<?php

namespace App\Livewire\Admin;

use App\Models\Video;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela de vídeos do painel — pesquisa + eliminar (confirmar via modal).
 * Espelha o padrão das outras tabelas Livewire simples do admin.
 */
class VideosTable extends Component
{
    use WithPagination;

    public string $busca = '';

    public function updatedBusca(): void
    {
        $this->resetPage();
    }

    public function eliminar(int $id): void
    {
        Gate::authorize('video.manage');

        Video::findOrFail($id)->delete();
        $this->dispatch('toast', mensagem: 'Vídeo removido.', tipo: 'success');
    }

    public function render()
    {
        $termo = trim($this->busca);

        $videos = Video::query()
            ->when($termo !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('titulo', 'like', '%' . $termo . '%')
                ->orWhere('url', 'like', '%' . $termo . '%')))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(8);

        return view('livewire.admin.videos-table', [
            'videos' => $videos,
        ]);
    }
}
