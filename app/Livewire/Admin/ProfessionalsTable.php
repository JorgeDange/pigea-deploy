<?php

namespace App\Livewire\Admin;

use App\Models\Professional;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela de moderação do Diretório de Profissionais (restrutura.md §5.4).
 *
 * Mesmo mecanismo do CompaniesTable: submissão pública → `pendente` →
 * aprovar/rejeitar daqui. Os aprovados entram no diretório público (Fase 5).
 */
class ProfessionalsTable extends Component
{
    use WithPagination;

    /** Filtro de estado ('' = todos). */
    public string $estado = '';

    /** Busca por nome do profissional. */
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

        $profissional = Professional::find($id);

        $profissional?->delete();
    }

    protected function mudarEstado(int $id, string $estado): void
    {
        Gate::authorize('company.moderate');

        $profissional = Professional::find($id);

        $profissional?->update(['status' => $estado]);
    }

    public function render()
    {
        Gate::authorize('company.moderate');

        $query = Professional::query();

        if ($this->estado !== '') {
            $query->where('status', $this->estado);
        }

        if ($this->busca !== '') {
            $query->where('nome_completo', 'like', '%'.$this->busca.'%');
        }

        return view('livewire.admin.professionals-table', [
            'profissionais' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(6),
        ]);
    }
}
