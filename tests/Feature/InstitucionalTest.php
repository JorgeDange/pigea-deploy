<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sobre Nós — módulo institucional (Equipa e Parceiros):
 * rotas públicas, secções hierárquicas, fallback de iniciais,
 * CRUD admin (com regra «Outros» + input de cargo), e menu.
 */
class InstitucionalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    // ------------------------------------------------------------------
    // Páginas públicas
    // ------------------------------------------------------------------

    public function test_paginas_equipa_e_parceiros_renderizam_vazias(): void
    {
        $this->get('/equipa')->assertOk()->assertSee('Equipa PIGEA');
        $this->get('/parceiros')->assertOk()->assertSee('Parceiros');
    }

    public function test_equipa_agrupa_por_seccao_e_ignora_inativos(): void
    {
        TeamMember::create(['nome' => 'TEST Diretor Geral', 'cargo_select' => 'direcao', 'ordem' => 1, 'ativo' => true]);
        TeamMember::create(['nome' => 'TEST Redatora Chefe', 'cargo_select' => 'redacao', 'ordem' => 1, 'ativo' => true]);
        TeamMember::create(['nome' => 'TEST Redator Invisivel', 'cargo_select' => 'redacao', 'ordem' => 2, 'ativo' => false]);

        $this->get('/equipa')
            ->assertOk()
            ->assertSee('Direção')
            ->assertSee('Redação')
            ->assertSee('TEST Diretor Geral')
            ->assertSee('TEST Redatora Chefe')
            ->assertDontSee('TEST Redator Invisivel');
    }

    public function test_equipa_seccao_vazia_nao_renderiza(): void
    {
        TeamMember::create(['nome' => 'TEST Tecnologico Meio', 'cargo_select' => 'tecnologia-dados', 'ordem' => 1, 'ativo' => true]);

        $html = $this->get('/equipa')->assertOk()->getContent();

        $this->assertStringContainsString('Tecnologia &amp; Dados', $html);
        $this->assertStringNotContainsString('id="equipa-direcao"', $html);
        $this->assertStringNotContainsString('id="equipa-redacao"', $html);
    }

    public function test_equipa_outros_mostra_o_cargo_personalizado(): void
    {
        TeamMember::create(['nome' => 'TEST Especialista SIG', 'cargo_select' => 'outros', 'cargo_outro' => 'Especialista em SIG', 'ordem' => 1, 'ativo' => true]);

        $this->get('/equipa')
            ->assertOk()
            ->assertSee('Especialista em SIG')
            ->assertDontSee('>Outros<');
    }

    public function test_equipa_foto_ausente_mostra_iniciais(): void
    {
        TeamMember::create(['nome' => 'TEST Maria João Silva', 'cargo_select' => 'redacao', 'ordem' => 1, 'ativo' => true]);

        $this->get('/equipa')
            ->assertOk()
            ->assertSee('TS');
    }

    public function test_parceiros_separa_empresas_de_profissionais(): void
    {
        Partner::create(['nome' => 'TEST Empresa Alfa', 'tipo' => 'empresa', 'detalhes' => 'Parceiro de prospeção.', 'ordem' => 1, 'ativo' => true]);
        Partner::create(['nome' => 'TEST Profissional Beta', 'tipo' => 'profissional', 'detalhes' => 'Consultoria geológica.', 'ordem' => 1, 'ativo' => true]);
        Partner::create(['nome' => 'TEST Parceiro Invisivel', 'tipo' => 'empresa', 'ordem' => 2, 'ativo' => false]);

        $this->get('/parceiros')
            ->assertOk()
            ->assertSee('Empresas')
            ->assertSee('Profissionais')
            ->assertSee('TEST Empresa Alfa')
            ->assertSee('TEST Profissional Beta')
            ->assertDontSee('TEST Parceiro Invisivel');
    }

    public function test_parceiros_sem_dados_mostra_vazio(): void
    {
        $this->get('/parceiros')->assertOk()->assertSee('A lista de parceiros está a ser atualizada.');
    }

    // ------------------------------------------------------------------
    // Menu (sobre-nós)
    // ------------------------------------------------------------------

    public function test_menu_sobre_aponta_para_as_novas_paginas(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('/equipa', $html);
        $this->assertStringContainsString('/parceiros', $html);
    }

    // ------------------------------------------------------------------
    // CRUD admin (equipa interna)
    // ------------------------------------------------------------------

    public function test_convidado_nao_acede_admin_equipa_nem_parceiros(): void
    {
        $this->get(route('admin.equipa'))->assertRedirect();
        $this->get(route('admin.parceiros'))->assertRedirect();
    }

    public function test_admin_cria_membro_com_outros_exige_cargo_personalizado(): void
    {
        $semCargo = $this->actingAs($this->admin())
            ->from(route('admin.equipa.novo'))
            ->post(route('admin.equipa.novo.store'), [
                'nome' => 'TEST Sem Cargo Outro',
                'cargo_select' => 'outros',
            ]);

        $semCargo->assertRedirect(route('admin.equipa.novo'))->assertSessionHasErrors(['cargo_outro']);
        $this->assertSame(0, TeamMember::where('nome', 'TEST Sem Cargo Outro')->count());

        $ok = $this->actingAs($this->admin())->post(route('admin.equipa.novo.store'), [
            'nome' => 'TEST Aprovado SIG',
            'cargo_select' => 'outros',
            'cargo_outro' => 'Especialista em SIG',
        ]);

        $ok->assertRedirect(route('admin.equipa'));
        $this->assertSame(1, TeamMember::where('nome', 'TEST Aprovado SIG')->count());
    }

    public function test_admin_cria_parceiro_empresa_e_edita(): void
    {
        $this->actingAs($this->admin())->post(route('admin.parceiros.novo.store'), [
            'nome' => 'TEST Parceiro Alvorada',
            'tipo' => 'empresa',
            'detalhes' => 'Consultoria em sondagens.',
            'ordem' => '3',
            'ativo' => '1',
        ])->assertRedirect(route('admin.parceiros'));

        $parceiro = Partner::where('nome', 'TEST Parceiro Alvorada')->firstOrFail();
        $this->assertSame('empresa', $parceiro->tipo);
        $this->assertSame('Consultoria em sondagens.', $parceiro->detalhes);

        $this->actingAs($this->admin())->put(route('admin.parceiros.atualizar', ['id' => $parceiro->id]), [
            'nome' => 'TEST Parceiro Alvorada Atualizado',
            'tipo' => 'empresa',
            'detalhes' => null,
            'ordem' => '1',
        ])->assertRedirect(route('admin.parceiros'));

        $this->assertSame('TEST Parceiro Alvorada Atualizado', $parceiro->fresh()->nome);
        $this->assertNull($parceiro->fresh()->detalhes);
    }

    public function test_admin_eliminar_membro_faz_soft_delete(): void
    {
        $membro = TeamMember::create(['nome' => 'TEST Para Remover', 'cargo_select' => 'redacao', 'ordem' => 1, 'ativo' => true]);

        $this->actingAs($this->admin())
            ->delete(route('admin.equipa.remover', ['id' => $membro->id]))
            ->assertRedirect(route('admin.equipa'));

        $this->assertSoftDeleted('team_members', ['id' => $membro->id]);
        $this->assertSame(0, TeamMember::where('id', $membro->id)->count(), 'Membro removido tem de desaparecer do escopo normal.');
    }

    public function test_formularios_validam_url_e_o_tipo_de_parceiro(): void
    {
        $this->actingAs($this->admin())->post(route('admin.equipa.novo.store'), [
            'nome' => 'TEST Foto Invalida',
            'cargo_select' => 'redacao',
            'foto_url' => 'nao-e-url',
        ])->assertSessionHasErrors(['foto_url']);

        $this->actingAs($this->admin())->post(route('admin.parceiros.novo.store'), [
            'nome' => 'TEST Tipo Invalido',
            'tipo' => 'nao-existe',
        ])->assertSessionHasErrors(['tipo']);
    }

    // ------------------------------------------------------------------
    // Configurações — cartão "Sobre Nós"
    // ------------------------------------------------------------------

    public function test_sobre_missao_visao_valores_e_editaveis_pelo_admin(): void
    {
        $this->actingAs($this->admin())->post(route('admin.configuracoes.sobre'), [
            'sobre_missao' => 'TEST MISSAO PERSONALIZADA',
            'sobre_visao' => 'TEST VISAO PERSONALIZADA',
            'sobre_valores' => 'TEST VALORES PERSONALIZADO',
            'video_institucional' => 'https://vimeo.com/999999999',
            'sobre_overlay' => 'TEST OVERLAY PERSONALIZADO',
        ])->assertRedirect(route('admin.configuracoes'));

        $this->get('/sobre')->assertOk()
            ->assertSee('TEST MISSAO PERSONALIZADA')
            ->assertSee('TEST VISAO PERSONALIZADA')
            ->assertSee('TEST VALORES PERSONALIZADO')
            ->assertSee('TEST OVERLAY PERSONALIZADO')
            ->assertSee('https://vimeo.com/999999999');
    }

    public function test_sobre_sem_config_mostra_o_texto_atual(): void
    {
        $this->get('/sobre')->assertOk()
            ->assertSee('Reunir, validar e disponibilizar')
            ->assertSee('plataforma de referência das geociências');
    }
}
