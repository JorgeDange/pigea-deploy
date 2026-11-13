<?php

namespace App\Livewire\Admin;

use App\Models\DirectoryDocument;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela de gestão dos Artigos/Documentos do Diretório (restrutura.md §5.5).
 *
 * Conteúdo carregado EXCLUSIVAMENTE pela equipa interna (não há submissão
 * pública nem status — tudo o que está aqui foi publicado pela equipa).
 * A criação/edição vive no DocumentFormController; daqui só se eliminar.
 */
class DocumentsTable extends Component
{
    use WithPagination;

    /** Filtro por tipo ('' = todos). */
    public string $tipo = '';

    /** Busca por título. */
    public string $busca = '';

    public function updated(string $propriedade): void
    {
        if (in_array($propriedade, ['tipo', 'busca'], true)) {
            $this->resetPage();
        }
    }

    /** Editar: abre o formulário do próprio tipo. */
    public function editar(int $id): void
    {
        Gate::authorize('document.manage');

        $this->redirect(route('admin.documentos.editar', ['id' => $id]));
    }

    /** Eliminar: soft delete (dá para reverter com restore()). */
    public function eliminar(int $id): void
    {
        Gate::authorize('document.manage');

        $documento = DirectoryDocument::find($id);

        $documento?->delete();
    }

    public function render()
    {
        Gate::authorize('document.manage');

        $query = DirectoryDocument::query();

        if ($this->tipo !== '') {
            $query->where('tipo', $this->tipo);
        }

        if ($this->busca !== '') {
            $query->where('titulo', 'like', '%'.$this->busca.'%');
        }

        return view('livewire.admin.documents-table', [
            'documentos' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(6),
        ]);
    }
}
