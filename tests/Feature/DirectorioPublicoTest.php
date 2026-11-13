<?php

namespace Tests\Feature;

use App\Livewire\Frontend\DirectoryListing;
use App\Models\Company;
use App\Models\DirectoryDocument;
use App\Models\Professional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * FASE 7 — visibilidade pública do diretório (restrutura.md §6):
 * apenas aprovados, NIF NUNCA no HTML, busca por nome/área e abas.
 */
class DirectorioPublicoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagina_diretorio_renderiza(): void
    {
        $this->get('/directorio')->assertOk();
    }

    public function test_apenas_aprovados_e_o_nif_nunca_aparece(): void
    {
        $aprovada = Company::create([
            'nome_empresa' => 'TEST Visível Alpha',
            'nif' => 'VIS000111',
            'contactos' => '+244 900 111',
            'areas_atuacao' => ['Mineração'],
            'status' => 'aprovado',
        ]);
        Company::create(['nome_empresa' => 'TEST Oculta Pendente', 'nif' => 'VIS000222', 'contactos' => 'x', 'areas_atuacao' => ['Energia'], 'status' => 'pendente']);
        Company::create(['nome_empresa' => 'TEST Oculta Rejeitada', 'nif' => 'VIS000333', 'contactos' => 'x', 'areas_atuacao' => ['Energia'], 'status' => 'rejeitado']);

        $html = Livewire::test(DirectoryListing::class)->html();

        $this->assertStringContainsString('TEST Visível Alpha', $html);
        // NIF da aprovada: JAMAIS no público (§6) — só serve de identificação
        // no registo e moderação.
        $this->assertStringNotContainsString('VIS000111', $html, 'NIF exposto no público!');
        $this->assertStringNotContainsString('TEST Oculta Pendente', $html);
        $this->assertStringNotContainsString('TEST Oculta Rejeitada', $html);
    }

    public function test_busca_por_nome(): void
    {
        Company::create(['nome_empresa' => 'TEST Busca Serra', 'nif' => 'BUS111', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'aprovado']);
        Company::create(['nome_empresa' => 'TEST Busca Mar', 'nif' => 'BUS222', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'aprovado']);

        $empresas = Livewire::test(DirectoryListing::class)
            ->set('busca', 'Serra')
            ->viewData('empresas');

        $this->assertSame(1, $empresas->total());
        $this->assertSame('TEST Busca Serra', $empresas->getCollection()->first()->nome_empresa);
    }

    public function test_busca_por_area_de_atuacao(): void
    {
        Company::create(['nome_empresa' => 'TEST Área A', 'nif' => 'AR111', 'contactos' => 'x', 'areas_atuacao' => ['Petróleo e Gás'], 'status' => 'aprovado']);
        Company::create(['nome_empresa' => 'TEST Área B', 'nif' => 'AR222', 'contactos' => 'x', 'areas_atuacao' => ['Energia'], 'status' => 'aprovado']);

        $empresas = Livewire::test(DirectoryListing::class)
            ->set('busca', 'Petróleo e Gás')
            ->viewData('empresas');

        $this->assertTrue($empresas->getCollection()->contains(fn ($c) => $c->nome_empresa === 'TEST Área A'));
        $this->assertFalse($empresas->getCollection()->contains(fn ($c) => $c->nome_empresa === 'TEST Área B'));
    }

    public function test_aba_documentos_com_botao_download(): void
    {
        DirectoryDocument::create([
            'titulo' => 'TEST Guia de Prospeção',
            'descricao' => 'x',
            'areas_atuacao' => ['Mineração'],
            'link_download' => 'https://pigea.ao/docs/guia.pdf',
        ]);

        $teste = Livewire::test(DirectoryListing::class)->call('mostrar', 'documentos');

        $documentos = $teste->viewData('documentos');
        $this->assertTrue($documentos->getCollection()->contains(fn ($d) => $d->titulo === 'TEST Guia de Prospeção'));
        $this->assertStringContainsString('https://pigea.ao/docs/guia.pdf', $teste->html());
    }

    public function test_aba_profissionais_apenas_aprovados(): void
    {
        Professional::create([
            'nome_completo' => 'TEST Eng.ª Ana Exemplo',
            'formacao_academica' => 'Mestrado em Engenharia de Minas — ISPTEC',
            'contactos' => '+244 900 555',
            'areas_atuacao' => ['Mineração'],
            'status' => 'aprovado',
        ]);
        Professional::create([
            'nome_completo' => 'TEST Oculto Pendente',
            'formacao_academica' => 'x',
            'contactos' => 'x',
            'areas_atuacao' => ['Água'],
            'status' => 'pendente',
        ]);
        Professional::create([
            'nome_completo' => 'TEST Oculto Rejeitado',
            'formacao_academica' => 'x',
            'contactos' => 'x',
            'areas_atuacao' => ['Água'],
            'status' => 'rejeitado',
        ]);

        $teste = Livewire::test(DirectoryListing::class)->call('mostrar', 'profissionais');

        $nomes = $teste->viewData('profissionais')->getCollection()->pluck('nome_completo');

        // Seed de demonstração tem 2 aprovados ("Eng. Mário Cassoma", "Dr. Isabel Ferreira");
        // o teste adiciona o seu — todos os pendentes/rejeitados ficam de fora.
        $this->assertTrue($nomes->contains('TEST Eng.ª Ana Exemplo'));
        $this->assertFalse($nomes->contains('TEST Oculto Pendente'));
        $this->assertFalse($nomes->contains('TEST Oculto Rejeitado'));

        $html = $teste->html();
        $this->assertStringContainsString('TEST Eng.ª Ana Exemplo', $html);
        $this->assertStringContainsString('Profissional aprovado', $html);
        $this->assertStringNotContainsString('TEST Oculto Pendente', $html);
        $this->assertStringNotContainsString('TEST Oculto Rejeitado', $html);
    }

    public function test_aba_profissionais_busca_por_formacao_e_area(): void
    {
        Professional::create([
            'nome_completo' => 'TEST Busca Formação',
            'formacao_academica' => 'Mestrado em Engenharia de Minas',
            'contactos' => 'x',
            'areas_atuacao' => ['Mineração'],
            'status' => 'aprovado',
        ]);
        Professional::create([
            'nome_completo' => 'TEST Busca Outra',
            'formacao_academica' => 'Licenciatura em Geodesia',
            'contactos' => 'x',
            'areas_atuacao' => ['Ambiente'],
            'status' => 'aprovado',
        ]);

        $profissionais = Livewire::test(DirectoryListing::class)
            ->set('busca', 'Minas')
            ->viewData('profissionais');

        $this->assertTrue($profissionais->getCollection()->contains(fn ($p) => $p->nome_completo === 'TEST Busca Formação'));
        $this->assertFalse($profissionais->getCollection()->contains(fn ($p) => $p->nome_completo === 'TEST Busca Outra'));
    }

    public function test_aba_invalida_e_ignorada(): void
    {
        $teste = Livewire::test(DirectoryListing::class)->call('mostrar', 'hackeado');

        $this->assertSame('empresas', $teste->instance()->aba);
    }
}
