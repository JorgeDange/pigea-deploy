<?php

namespace Tests\Unit;

use App\Models\Banner;
use App\Models\Company;
use App\Models\DirectoryDocument;
use App\Models\Professional;
use App\Support\AreasAtuacao;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE 7 — modelos novos da reestruturação (restrutura.md §3.5):
 * scopes de estado, casts JSON, SoftDeletes, whitelist de áreas e
 * regras de período/posição dos banners.
 *
 * Corre sobre a `pigea_test` (bootstrap) com transações — os registos
 * "TEST-…" vivem só dentro de cada teste.
 */
class ModelosRestruturaTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // AreasAtuacao — whitelist dos formulários públicos (§4)
    // ------------------------------------------------------------------

    public function test_areas_atuacao_filtrar_mantem_so_as_da_whitelist(): void
    {
        $this->assertSame(
            ['Mineração', 'Água'],
            AreasAtuacao::filtrar(['Mineração', 'Água', 'Hackeado'])
        );
    }

    public function test_areas_atuacao_filtrar_remove_duplicados(): void
    {
        $this->assertSame(['Energia'], AreasAtuacao::filtrar(['Energia', 'Energia']));
    }

    public function test_areas_atuacao_filtrar_com_nao_array_devolve_vazio(): void
    {
        $this->assertSame([], AreasAtuacao::filtrar('Mineração'));
        $this->assertSame([], AreasAtuacao::filtrar(null));
    }

    // ------------------------------------------------------------------
    // Company (§3.1) — scopes de estado + casts JSON
    // ------------------------------------------------------------------

    public function test_company_scopes_separam_aprovado_pendente_rejeitado(): void
    {
        $aprovada = Company::create(['nome_empresa' => 'TEST Aprovada', 'nif' => 'TEST111', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'aprovado']);
        $pendente = Company::create(['nome_empresa' => 'TEST Pendente', 'nif' => 'TEST222', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);
        $rejeitada = Company::create(['nome_empresa' => 'TEST Rejeitada', 'nif' => 'TEST333', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'rejeitado']);

        $this->assertTrue(Company::aprovado()->get()->contains($aprovada));
        $this->assertFalse(Company::aprovado()->get()->contains($pendente));
        $this->assertTrue(Company::pendente()->get()->contains($pendente));
        $this->assertTrue(Company::rejeitado()->get()->contains($rejeitada));
    }

    public function test_company_areas_atuacao_e_redes_sociais_sao_json(): void
    {
        $empresa = Company::create([
            'nome_empresa' => 'TEST JSON',
            'nif' => 'TEST444',
            'contactos' => 'x',
            'areas_atuacao' => ['Mineração', 'Energia'],
            'redes_sociais' => ['facebook' => 'https://facebook.com/teste'],
            'status' => 'aprovado',
        ])->fresh();

        $this->assertSame(['Mineração', 'Energia'], $empresa->areas_atuacao);
        $this->assertSame('https://facebook.com/teste', $empresa->redes_sociais['facebook']);
    }

    public function test_company_nif_e_unico(): void
    {
        Company::create(['nome_empresa' => 'TEST NIF1', 'nif' => 'TEST999', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);

        $erros = \Illuminate\Support\Facades\Validator::make(
            ['nif' => 'TEST999'],
            ['nif' => 'unique:companies,nif']
        );

        $this->assertTrue($erros->fails(), 'O NIF duplicado devia falhar o unique.');
        $this->assertStringContainsString('campo nif', $erros->errors()->first('nif'));
    }

    // ------------------------------------------------------------------
    // Professional (§3.2) — scopes
    // ------------------------------------------------------------------

    public function test_professional_scopes_separam_aprovado_pendente(): void
    {
        $aprovado = Professional::create(['nome_completo' => 'TEST Aprovado', 'formacao_academica' => 'x', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'aprovado']);
        $pendente = Professional::create(['nome_completo' => 'TEST Pendente', 'formacao_academica' => 'x', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);

        $this->assertTrue(Professional::aprovado()->get()->contains($aprovado));
        $this->assertFalse(Professional::aprovado()->get()->contains($pendente));
        $this->assertTrue(Professional::pendente()->get()->contains($pendente));
    }

    // ------------------------------------------------------------------
    // SoftDeletes (decisão: eliminar no admin é reversível)
    // ------------------------------------------------------------------

    public function test_directory_document_e_soft_delete(): void
    {
        $doc = DirectoryDocument::create([
            'titulo' => 'TEST Documento',
            'descricao' => 'x',
            'areas_atuacao' => ['Água'],
            'link_download' => 'https://example.ao/doc.pdf',
        ]);

        $doc->delete();

        $this->assertSoftDeleted('directory_documents', ['id' => $doc->id]);
    }

    public function test_banner_e_soft_delete(): void
    {
        $banner = Banner::create([
            'titulo' => 'TEST Banner',
            'imagem_url' => 'images/all/banner.jpg',
            'posicao' => 'topo',
            'ativo' => true,
        ]);

        $banner->delete();

        $this->assertSoftDeleted('banners', ['id' => $banner->id]);
    }

    // ------------------------------------------------------------------
    // Banner (§3.5) — período + posição (pub.md aproveitado, paradigma novo)
    // ------------------------------------------------------------------

    public function test_banner_ativos_somente_dentro_do_periodo(): void
    {
        $vigente = Banner::create(['titulo' => 'TEST vigente', 'imagem_url' => 'x', 'posicao' => 'topo', 'ativo' => true, 'data_inicio' => now()->subDay()->toDateString(), 'data_fim' => now()->addMonth()->toDateString()]);
        $sempre = Banner::create(['titulo' => 'TEST sempre', 'imagem_url' => 'x', 'posicao' => 'topo', 'ativo' => true, 'data_inicio' => null, 'data_fim' => null]);
        $expirado = Banner::create(['titulo' => 'TEST expirado', 'imagem_url' => 'x', 'posicao' => 'topo', 'ativo' => true, 'data_inicio' => now()->subMonths(2)->toDateString(), 'data_fim' => now()->subMonth()->toDateString()]);
        $futuro = Banner::create(['titulo' => 'TEST futuro', 'imagem_url' => 'x', 'posicao' => 'topo', 'ativo' => true, 'data_inicio' => now()->addMonth()->toDateString(), 'data_fim' => null]);
        $inativo = Banner::create(['titulo' => 'TEST inativo', 'imagem_url' => 'x', 'posicao' => 'topo', 'ativo' => false]);

        $titulos = Banner::ativos()->pluck('titulo');

        $this->assertTrue($titulos->contains($vigente->titulo));
        $this->assertTrue($titulos->contains($sempre->titulo));
        $this->assertFalse($titulos->contains($expirado->titulo));
        $this->assertFalse($titulos->contains($futuro->titulo));
        $this->assertFalse($titulos->contains($inativo->titulo));
    }

    public function test_banner_posicao_filtra_por_posicao(): void
    {
        $topo = Banner::create(['titulo' => 'TEST topo', 'imagem_url' => 'x', 'posicao' => 'topo', 'ativo' => true]);
        $sidebar = Banner::create(['titulo' => 'TEST sidebar', 'imagem_url' => 'x', 'posicao' => 'sidebar', 'ativo' => true]);

        $topoIds = Banner::ativos()->posicao('topo')->pluck('id');

        $this->assertTrue($topoIds->contains($topo->id));
        $this->assertFalse($topoIds->contains($sidebar->id));
    }
}
