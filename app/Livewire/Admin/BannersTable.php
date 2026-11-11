<?php

namespace App\Livewire\Admin;

use App\Models\Banner;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela de gestão da Publicidade — banners (restrutura.md §5.6).
 *
 * A criação/edição vive no BannerFormController; daqui só se eliminar e
 * alternar `ativo`. A exibição no público (Fase 6) usa o scope `ativos`
 * do modelo — um banner inativo deixa de aparecer sem ser apagado.
 */
class BannersTable extends Component
{
    use WithPagination;

    /** Filtro por posição ('' = todas). */
    public string $posicao = '';

    /** Busca por título. */
    public string $busca = '';

    public function updated(string $propriedade): void
    {
        if (in_array($propriedade, ['posicao', 'busca'], true)) {
            $this->resetPage();
        }
    }

    /** Ativar/desativar (sem apagar o registo). */
    public function alternarAtivo(int $id): void
    {
        Gate::authorize('banner.manage');

        $banner = Banner::find($id);

        if ($banner !== null) {
            $banner->update(['ativo' => ! $banner->ativo]);
        }
    }

    /** Editar: abre o formulário do próprio tipo. */
    public function editar(int $id): void
    {
        Gate::authorize('banner.manage');

        $this->redirect(route('admin.banners.editar', ['id' => $id]));
    }

    /** Eliminar: soft delete (dá para reverter com restore()). */
    public function eliminar(int $id): void
    {
        Gate::authorize('banner.manage');

        $banner = Banner::find($id);

        $banner?->delete();
    }

    public function render()
    {
        Gate::authorize('banner.manage');

        $query = Banner::query();

        if ($this->posicao !== '') {
            $query->where('posicao', $this->posicao);
        }

        if ($this->busca !== '') {
            $query->where('titulo', 'like', '%'.$this->busca.'%');
        }

        return view('livewire.admin.banners-table', [
            'banners' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(6),
        ]);
    }
}
