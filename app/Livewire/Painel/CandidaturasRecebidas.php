<?php

namespace App\Livewire\Painel;

use App\Livewire\Concerns\FiltraData;
use App\Models\Application;
use App\Models\Job;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabela "Candidaturas recebidas" (`painel-empresa-candidaturas.html`).
 *
 * Só candidaturas às vagas da empresa autenticada. Filtros: vaga (select com
 * as vagas da empresa) e estado (Em análise / Aceite / Rejeitada). Ações:
 * Ver CV (abre o link enviado, quando existe), Aceitar e Rejeitar (mudam o
 * estado da candidatura).
 */
class CandidaturasRecebidas extends Component
{
    use FiltraData;
    use WithPagination;

    public string $vaga = '';

    public string $estado = '';

    public function updated(string $propriedade): void
    {
        if (in_array($propriedade, ['vaga', 'estado'], true)) {
            $this->resetPage();
        }
    }

    /** Ver CV: abre o link do currículo enviado na candidatura. */
    public function verCv(int $id): void
    {
        $candidatura = $this->candidaturas()->find($id);

        if ($candidatura !== null && $candidatura->cv_url) {
            $this->redirect($candidatura->cv_url);
        }
    }

    public function aceitar(int $id): void
    {
        $candidatura = $this->candidaturas()->find($id);

        if ($candidatura !== null) {
            Gate::authorize('decide-application', $candidatura); // gate de posse (Fase 13)
            $candidatura->update(['status' => 'aceite']);
        }
    }

    public function rejeitar(int $id): void
    {
        $candidatura = $this->candidaturas()->find($id);

        if ($candidatura !== null) {
            Gate::authorize('decide-application', $candidatura); // gate de posse (Fase 13)
            $candidatura->update(['status' => 'rejeitada']);
        }
    }

    public function render()
    {
        $query = $this->candidaturas()->with(['user', 'job']);

        if ($this->vaga !== '') {
            $query->where('job_id', (int) $this->vaga);
        }

        if ($this->estado !== '') {
            $query->where('status', $this->estado);
        }

        $candidaturas = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(5);

        return view('livewire.painel.candidaturas-recebidas', [
            'candidaturas' => $candidaturas,
            'vagas' => Job::query()->where('author_id', auth()->id())->orderBy('title')->get(),
        ]);
    }

    private function candidaturas()
    {
        $vagasIds = Job::query()->where('author_id', auth()->id())->pluck('id');

        return Application::query()->whereIn('job_id', $vagasIds);
    }
}
