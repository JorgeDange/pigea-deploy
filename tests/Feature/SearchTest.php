<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Fase 14 — pesquisa funcional (fase.md §2.2): Scout `database` + MySQL
 * Full-Text (`MATCH … AGAINST` em `title`/`excerpt` + `LIKE`), tal como em
 * produção — os testes correm no MariaDB `pigea_test`, não em SQLite.
 */
class SearchTest extends TestCase
{
    public function test_pesquisa_com_termo_devolve_resultados_reais(): void
    {
        $resposta = $this->get('/pesquisa?se=geologia');

        $resposta->assertOk();
        $this->assertGreaterThan(0, $resposta->viewData('total'));
    }

    public function test_pesquisa_sem_correspondencia_devolve_zero(): void
    {
        $resposta = $this->get('/pesquisa?se=xyzabc');

        $resposta->assertOk();
        $this->assertSame(0, $resposta->viewData('total'));
    }

    public function test_pesquisa_sem_termo_devolve_a_pagina_vazia(): void
    {
        $resposta = $this->get('/pesquisa');

        $resposta->assertOk();
        $this->assertSame(0, $resposta->viewData('total'));
    }
}
