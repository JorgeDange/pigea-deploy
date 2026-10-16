<?php

namespace App\Livewire\Painel;

use App\Livewire\Concerns\FiltraData;
use App\Models\Job;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela "Vagas publicadas" (`painel-empresa-vagas.html`).
 *
 * Só as vagas da empresa autenticada (`vacancies.author_id`). Filtros: estado
 * (Ativa / Em análise / Expirada → `published` / `draft` / `rejected`) e data
 * (mm/aaaa sobre `published_at`). Ações: Editar (sem página de edição no
 * protótipo — inerte), Ver candidaturas e Eliminar (soft delete).
 */
class VagasEmpresa extends Component
{
    use FiltraData;
    use WithPagination;

    public string $estado = '';

    public string $data = '';

    /** Estados do protótipo → valores de `status`. */
    private const ESTADOS = [
        'Ativa' => 'published',
        'Em análise' => 'draft',
        'Expirada' => 'rejected',
    ];

    /** `status` → [classe do badge, rótulo] (textos do protótipo). */
    private const BADGES = [
        'published' => ['active', 'Ativa'],
        'draft' => ['pending', 'Em análise'],
        'rejected' => ['rejected', 'Expirada'],
    ];

    public function updated(string $propriedade): void
    {
        if (in_array($propriedade, ['estado', 'data'], true)) {
            $this->resetPage();
        }
    }

    /** Ver candidaturas: segue para a página de candidaturas recebidas. */
    public function verCandidaturas(int $id): void
    {
        $vaga = $this->vagas()->find($id);

        if ($vaga !== null) {
            $this->redirect(route('company.candidaturas'));
        }
    }

    /** Eliminar: soft delete (dá para reverter com restore()). */
    public function eliminar(int $id): void
    {
        $vaga = $this->vagas()->find($id);

        if ($vaga !== null) {
            Gate::authorize('delete-vacancy', $vaga); // gate de posse (Fase 13)
            $vaga->delete();
        }
    }

    public function render()
    {
        $query = $this->vagas()->withCount('applications');

        if ($this->estado !== '') {
            $query->where('status', self::ESTADOS[$this->estado] ?? $this->estado);
        }

        $this->filtrarData($query, $this->data, 'published_at');

        $vagas = $query
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(6);

        return view('livewire.painel.vagas-empresa', [
            'vagas' => $vagas,
            'estados' => self::BADGES,
        ]);
    }

    private function vagas()
    {
        return Job::query()->where('author_id', auth()->id());
    }
}
