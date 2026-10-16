<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Se alguma coisa voltar a migrar em meio de execução (o `RefreshDatabase`
     * desactiva o estado quando uma transação desaparece), reexecuta a seed —
     * os testes contam com os dados do `DatabaseSeeder` (roles, conteúdo,
     * utilizadores e dados de demonstração dos painéis).
     */
    protected function migrateDatabases()
    {
        $this->artisan('migrate:fresh', ['--seed' => true, '--force' => true]);
    }
}
