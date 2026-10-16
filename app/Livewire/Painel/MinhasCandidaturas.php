<?php

namespace App\Livewire\Painel;

use App\Livewire\Concerns\FiltraData;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela "As minhas candidaturas" (`painel-utilizador-candidaturas.html`).
 *
 * Filtros: estado (Em análise / Aceite / Rejeitada — estados reais de
 * `applications`) e data (mm/aaaa sobre `created_at`). Ações: Ver (abre a
 * vaga no site público) e Retirar (apaga a própria candidatura).
 */
class MinhasCandidaturas extends Component
{
    use FiltraData;
    use WithPagination;

    public string $estado = '';

    public string $data = '';

    public function updated(string $propriedade): void
    {
        if (in_array($propriedade, ['estado', 'data'], true)) {
            $this->resetPage();
        }
    }

    /** Ver: abre a vaga no site público (como no protótipo). */
    public function ver(int $id): void
    {
        $candidatura = auth()->user()->applications()->with('job')->find($id);

        if ($candidatura?->job !== null) {
            $this->redirect(route('jobs.show', $candidatura->job->slug));
        }
    }

    /** Retirar: o utilizador remove a própria candidatura. */
    public function retirar(int $id): void
    {
        $candidatura = auth()->user()->applications()->find($id);

        if ($candidatura !== null) {
            Gate::authorize('withdraw-application', $candidatura); // gate de posse (Fase 13)
            $candidatura->delete();
        }
    }

    public function render()
    {
        $query = auth()->user()->applications()->with(['job', 'job.author']);

        if ($this->estado !== '') {
            $query->where('status', $this->estado);
        }

        $this->filtrarData($query, $this->data);

        $candidaturas = $query
            ->orderByDesc('created_at')
            // Desempate como nas tabelas do admin (mesma data em série).
            ->orderByDesc('id')
            ->paginate(8);

        return view('livewire.painel.minhas-candidaturas', ['candidaturas' => $candidaturas]);
    }
}
