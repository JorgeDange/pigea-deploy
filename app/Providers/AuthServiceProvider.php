<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Gates de instância (posse de registo).
 *
 * Na reestruturação (restrutura.md), os gates de posse do módulo de vagas/
 * candidaturas/painel-empresa foram removidos — empresas e profissionais não
 * são utilizadores do sistema; a gestão é exclusiva da equipa interna via
 * /admin. Novos gates de instância entram aqui quando a Fase 2 (Diretório)
 * os exigir.
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }
}
