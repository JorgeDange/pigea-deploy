<?php

namespace Tests\Feature;

use App\Livewire\Admin\CompaniesTable;
use App\Livewire\Admin\ProfessionalsTable;
use App\Models\Banner;
use App\Models\Company;
use App\Models\Professional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * FASE 7 — aprovação/rejeição no painel (restrutura.md §5.3-5.4) e
 * páginas do admin (empresas/profissionais/documentos/banners).
 *
 * Contrato de permissões (RoleSeeder): moderação do diretório
 * (`company.moderate`) é EXCLUSIVA do admin; o editor só geriu
 * documentos e banners.
 */
class ModeracaoDiretorioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Moderação de Empresas (CompaniesTable)
    // ------------------------------------------------------------------

    public function test_aprovar_empresa_fica_visivel_no_publico(): void
    {
        $empresa = Company::create(['nome_empresa' => 'TEST Aprovar', 'nif' => 'MOD111', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('aprovar', $empresa->id);

        $this->assertSame('aprovado', $empresa->fresh()->status);
        $this->assertTrue(Company::aprovado()->get()->contains($empresa));
    }

    public function test_rejeitar_empresa_nao_aparece_no_publico(): void
    {
        $empresa = Company::create(['nome_empresa' => 'TEST Rejeitar', 'nif' => 'MOD222', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('rejeitar', $empresa->id);

        $this->assertSame('rejeitado', $empresa->fresh()->status);
        $this->assertFalse(Company::aprovado()->get()->contains($empresa));
    }

    public function test_restabelecer_volta_a_pendente(): void
    {
        $empresa = Company::create(['nome_empresa' => 'TEST Restabelecer', 'nif' => 'MOD333', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'rejeitado']);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('restabelecer', $empresa->id);

        $this->assertSame('pendente', $empresa->fresh()->status);
    }

    public function test_eliminar_empresa_remove_o_registo(): void
    {
        $empresa = Company::create(['nome_empresa' => 'TEST Eliminar', 'nif' => 'MOD444', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);

        Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->call('eliminar', $empresa->id);

        $this->assertDatabaseMissing('companies', ['id' => $empresa->id]);
    }

    public function test_busca_e_filtro_de_estado_na_tabela(): void
    {
        Company::create(['nome_empresa' => 'TEST Busca Alfa', 'nif' => 'MOD551', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);
        Company::create(['nome_empresa' => 'TEST Busca Beta', 'nif' => 'MOD552', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'aprovado']);

        $componente = Livewire::actingAs($this->admin())
            ->test(CompaniesTable::class)
            ->set('busca', 'Busca Alfa');

        $ids = $componente->viewData('empresas')->getCollection()->pluck('nome_empresa');
        $this->assertTrue($ids->contains('TEST Busca Alfa'));
        $this->assertFalse($ids->contains('TEST Busca Beta'));

        $componente->set('busca', '')->set('estado', 'aprovado');
        $ids = $componente->viewData('empresas')->getCollection()->pluck('nome_empresa');
        $this->assertFalse($ids->contains('TEST Busca Alfa'));
        $this->assertTrue($ids->contains('TEST Busca Beta'));
    }

    // ------------------------------------------------------------------
    // Moderação de Profissionais (ProfessionalsTable)
    // ------------------------------------------------------------------

    public function test_aprovar_profissional(): void
    {
        $profissional = Professional::create(['nome_completo' => 'TEST Mod Prof', 'formacao_academica' => 'x', 'contactos' => 'x', 'areas_atuacao' => ['Água'], 'status' => 'pendente']);

        Livewire::actingAs($this->admin())
            ->test(ProfessionalsTable::class)
            ->call('aprovar', $profissional->id);

        $this->assertSame('aprovado', $profissional->fresh()->status);
    }

    // ------------------------------------------------------------------
    // Permissões: moderação exclusiva do admin (RoleSeeder)
    // ------------------------------------------------------------------

    public function test_utilizador_sem_role_nao_acede_a_moderacao(): void
    {
        $semRole = User::factory()->create();

        $this->actingAs($semRole)->get(route('admin.empresas'))->assertForbidden();
        $this->actingAs($semRole)->get(route('admin.profissionais'))->assertForbidden();
    }

    /** FASE 7 — navegação do admin nas páginas do diretório/publicidade. */
    public function test_admin_ve_as_paginas_do_diretorio_e_publicidade(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.empresas'))->assertOk();
        $this->actingAs($this->admin())
            ->get(route('admin.profissionais'))->assertOk();
        $this->actingAs($this->admin())
            ->get(route('admin.documentos'))->assertOk();
        $this->actingAs($this->admin())
            ->get(route('admin.banners'))->assertOk();
        $this->actingAs($this->admin())
            ->get(route('admin.banners.novo'))->assertOk();
    }

    public function test_editor_gera_documentos_e_banners_mas_nao_moderacao(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        // Editor pode: documentos e banners (document.manage / banner.manage).
        $this->actingAs($editor)->get(route('admin.documentos'))->assertOk();
        $this->actingAs($editor)->get(route('admin.banners'))->assertOk();
        $this->actingAs($editor)->get(route('admin.banners.novo'))->assertOk();

        // Editor NÃO pode: moderação do diretório (company.moderate).
        $this->actingAs($editor)->get(route('admin.empresas'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.profissionais'))->assertForbidden();
    }

    // ------------------------------------------------------------------
    // CRUD de Banners (BannerFormController — upload OU link, §3.5)
    // ------------------------------------------------------------------

    public function test_criar_banner_com_link_externo(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.banners.novo.store'), [
                'titulo' => 'TEST Banner Link',
                'imagem_link' => 'https://cdn.example.ao/banner.jpg',
                'link_destino' => 'https://anunciante.example.ao',
                'posicao' => 'topo',
                'data_inicio' => now()->toDateString(),
                'data_fim' => now()->addMonth()->toDateString(),
                'ativo' => '1',
            ])
            ->assertRedirect(route('admin.banners'));

        $banner = Banner::where('titulo', 'TEST Banner Link')->firstOrFail();
        $this->assertSame('https://cdn.example.ao/banner.jpg', $banner->imagem_url);
        $this->assertSame('topo', $banner->posicao);
        $this->assertTrue($banner->ativo);
    }

    public function test_criar_banner_com_posicao_invalida_da_erro(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.banners.novo'))
            ->post(route('admin.banners.novo.store'), [
                'titulo' => 'TEST Posição Ruim',
                'imagem_link' => 'https://cdn.example.ao/x.jpg',
                'posicao' => 'home_topo', // posição antiga do pub.md — não existe
            ])
            ->assertSessionHasErrors('posicao');

        $this->assertDatabaseMissing('banners', ['titulo' => 'TEST Posição Ruim']);
    }

    public function test_criar_banner_sem_imagem_da_erro(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.banners.novo'))
            ->post(route('admin.banners.novo.store'), [
                'titulo' => 'TEST Sem Imagem',
                'posicao' => 'topo',
            ])
            ->assertSessionHasErrors('imagem_link'); // upload OU link obrigatórios na criação

        $this->assertDatabaseMissing('banners', ['titulo' => 'TEST Sem Imagem']);
    }
}
