<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Job;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Gates de posse da Fase 13 (`fase.md` §5 — "can:/gates granulares").
 *
 * As permissões Spatie (ex.: `news.manage`) ficam nos middlewares `can:` das
 * rotas e nas ações dos Livewire; estes gates cobrem a **instância** — só quem
 * é dono do registo o pode eliminar/decidir, mesmo que tenha a role certa.
 *
 * Semântica igual à que existia inline nos controllers (`abort_unless` de
 * posse) — só fica centralizada, testável e reutilizável.
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Empresa elimina a própria vaga (`vacancies.author_id`).
        Gate::define('delete-vacancy', static fn (User $user, Job $job): bool => (int) $job->author_id === (int) $user->id);

        // Empresa elimina a própria oportunidade (`opportunities.user_id`).
        Gate::define('delete-opportunity', static fn (User $user, Opportunity $oportunidade): bool => (int) $oportunidade->user_id === (int) $user->id);

        // Empresa decide (aceitar/rejeitar) candidaturas às SUAS vagas.
        Gate::define('decide-application', static fn (User $user, Application $candidatura): bool => $candidatura->job !== null && (int) $candidatura->job->author_id === (int) $user->id);

        // Utilizador retira a própria candidatura (`applications.user_id`).
        Gate::define('withdraw-application', static fn (User $user, Application $candidatura): bool => (int) $candidatura->user_id === (int) $user->id);
    }
}
