<?php

namespace Tests\Feature;

use App\Livewire\Frontend\RegistoEmpresa;
use App\Livewire\Frontend\RegistoProfissional;
use App\Models\Company;
use App\Models\Professional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * FASE 7 — formulários públicos de registo (restrutura.md §4):
 * validação, gravação com status `pendente`, NIF unique e whitelist
 * de áreas (POST manipulável nunca grava texto arbitrário).
 */
class FormulariosPublicosTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginas_de_registo_renderizam(): void
    {
        $this->get('/registo-empresa')->assertOk();
        $this->get('/registo-profissional')->assertOk();
    }

    // ------------------------------------------------------------------
    // Registo de Empresa (§4.1)
    // ------------------------------------------------------------------

    public function test_submissao_valida_grava_empresa_pendente_e_mostra_mensagem(): void
    {
        $teste = Livewire::test(RegistoEmpresa::class)
            ->set('nome_empresa', 'TEST Empresa Alpha')
            ->set('nif', 'TEST000111')
            ->set('contactos', '+244 900 000 111')
            ->set('areas_atuacao', ['Mineração', 'Água'])
            ->set('facebook', 'https://facebook.com/alpha')
            ->call('submit')
            ->assertSet('estado', 'ok');

        $empresa = Company::where('nif', 'TEST000111')->firstOrFail();

        $this->assertSame('pendente', $empresa->status, 'Submissão pública deve nascer pendente (§4.1).');
        $this->assertSame(['Mineração', 'Água'], $empresa->areas_atuacao);
        $this->assertSame('https://facebook.com/alpha', $empresa->redes_sociais['facebook']);
        $this->assertStringContainsString(
            'Aguarde a aprovação da nossa equipa',
            $teste->html()
        );
    }

    public function test_nif_duplicado_e_rejeitado(): void
    {
        Company::create(['nome_empresa' => 'TEST Original', 'nif' => 'TEST000222', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);

        Livewire::test(RegistoEmpresa::class)
            ->set('nome_empresa', 'TEST Duplicada')
            ->set('nif', 'TEST000222')
            ->set('contactos', 'y')
            ->set('areas_atuacao', ['Água'])
            ->call('submit')
            ->assertSet('estado', 'erro')
            ->assertSet('erro', 'Já existe um registo com este NIF.');

        $this->assertSame(1, Company::where('nif', 'TEST000222')->count());
    }

    public function test_campos_obrigatorios_em_falta_mostram_erro(): void
    {
        Livewire::test(RegistoEmpresa::class)
            ->set('nome_empresa', '')
            ->set('areas_atuacao', [])
            ->call('submit')
            ->assertSet('estado', 'erro')
            ->assertSet('erro', 'Indique o nome da empresa.');

        $this->assertSame(0, Company::where('nome_empresa', '')->count());
    }

    public function test_areas_fora_da_whitelist_nao_sao_gravadas(): void
    {
        Livewire::test(RegistoEmpresa::class)
            ->set('nome_empresa', 'TEST Manipuladora')
            ->set('nif', 'TEST000333')
            ->set('contactos', 'z')
            ->set('areas_atuacao', ['Área Falsa do Hacker'])
            ->call('submit')
            ->assertSet('estado', 'erro')
            ->assertSet('erro', 'Selecione pelo menos uma área de atuação válida.');

        $this->assertNull(Company::where('nif', 'TEST000333')->first(), 'POST com áreas inválidas não pode gravar.');
    }

    public function test_redes_sociais_com_url_invalida_sao_rejeitadas(): void
    {
        Livewire::test(RegistoEmpresa::class)
            ->set('nome_empresa', 'TEST URL Ruim')
            ->set('nif', 'TEST000444')
            ->set('contactos', 'x')
            ->set('areas_atuacao', ['Água'])
            ->set('linkedin', 'nao-e-url')
            ->call('submit')
            ->assertSet('estado', 'erro')
            ->assertSet('erro', 'O link do LinkedIn não é um URL válido.');
    }

    // ------------------------------------------------------------------
    // Registo de Profissional (§4.2)
    // ------------------------------------------------------------------

    public function test_submissao_valida_grava_profissional_pendente(): void
    {
        Livewire::test(RegistoProfissional::class)
            ->set('nome_completo', 'TEST Profissional Beta')
            ->set('formacao_academica', 'Licenciatura em Geologia')
            ->set('contactos', 'beta@example.ao')
            ->set('areas_atuacao', ['Geociências'])
            ->call('submit')
            ->assertSet('estado', 'ok');

        $profissional = Professional::where('nome_completo', 'TEST Profissional Beta')->firstOrFail();

        $this->assertSame('pendente', $profissional->status);
        $this->assertSame(['Geociências'], $profissional->areas_atuacao);
    }

    public function test_profissional_sem_areas_mostra_erro(): void
    {
        Livewire::test(RegistoProfissional::class)
            ->set('nome_completo', 'TEST Sem Áreas')
            ->set('formacao_academica', 'x')
            ->set('contactos', 'x')
            ->set('areas_atuacao', [])
            ->call('submit')
            ->assertSet('estado', 'erro')
            ->assertSet('erro', 'Selecione pelo menos uma área de atuação.');
    }
}
