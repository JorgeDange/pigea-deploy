<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela de moderação do Diretório de Empresas (restrutura.md §5.3).
 *
 * As empresas entram pelo formulário público (/registo-empresa) com status
 * `pendente`; daqui a equipa interna aprova/rejeita. As aprovadas ficam
 * visíveis na página pública (Fase 5) — as rejeitadas nunca aparecem.
 *
 * Substitui a antiga linha 'empresas' do ContentTable (modelo legado
 * Directory): a Company não tem title/slug/categoria/publicação, por isso
 * tem tabela própria em vez de forçar o componente genérico.
 */
class CompaniesTable extends Component
{
    use WithPagination;

    /** Filtro de estado ('' = todos). */
    public string $estado = '';

    /** Busca por nome da empresa. */
    public string $busca = '';

    public function updated(string $propriedade): void
    {
        if (in_array($propriedade, ['estado', 'busca'], true)) {
            $this->resetPage();
        }
    }

    /** Aprovar — fica visível no diretório público. */
    public function aprovar(int $id): void
    {
        $this->mudarEstado($id, 'aprovado');
    }

    /** Rejeitar — não aparece no público. */
    public function rejeitar(int $id): void
    {
        $this->mudarEstado($id, 'rejeitado');
    }

    /** Voltar a `pendente` (permite reanalisar um rejeitado por engano). */
    public function restabelecer(int $id): void
    {
        $this->mudarEstado($id, 'pendente');
    }

    /** Eliminar o registo (soft delete do modelo). */
    public function eliminar(int $id): void
    {
        Gate::authorize('company.moderate');

        $empresa = Company::find($id);

        $empresa?->delete();
    }

    protected function mudarEstado(int $id, string $estado): void
    {
        Gate::authorize('company.moderate');

        $empresa = Company::find($id);

        $empresa?->update(['status' => $estado]);
    }

    public function render()
    {
        Gate::authorize('company.moderate');

        $query = Company::query();

        if ($this->estado !== '') {
            $query->where('status', $this->estado);
        }

        if ($this->busca !== '') {
            $query->where('nome_empresa', 'like', '%'.$this->busca.'%');
        }

        return view('livewire.admin.companies-table', [
            'empresas' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(6),
        ]);
    }
}
