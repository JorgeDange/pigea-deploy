<?php

namespace Tests\Feature;

use App\Livewire\Frontend\DirectoryListing;
use App\Models\Company;
use App\Models\DirectoryDocument;
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

    public function test_aba_invalida_e_ignorada(): void
    {
        $teste = Livewire::test(DirectoryListing::class)->call('mostrar', 'hackeado');

        $this->assertSame('empresas', $teste->instance()->aba);
    }
}
