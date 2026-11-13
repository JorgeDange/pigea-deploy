<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FASE E4 — página «Acompanhamento» (admin only, leitura).
 *
 * Regras: acessível a admin e editor; anónimo é redirecionado; a página
 * mostra os blocos-chave (Redes, Largo, Províncias, Destaques, Vistas).
 */
class AcompanhamentoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_admin_ve_a_pagina_de_acompanhamento(): void
    {
        $resposta = $this->actingAs($this->admin())->get(route('admin.acompanhamento'));

        $resposta->assertOk();
        $resposta->assertSee('Acompanhamento');
        $resposta->assertSee('Redes sociais');
        $resposta->assertSee('Largo');
        $resposta->assertSee('Províncias');
        $resposta->assertSee('Destaque editorial');
        $resposta->assertSee('Vistas');
    }

    public function test_anonimo_e_redirecionado_para_login(): void
    {
        $this->get(route('admin.acompanhamento'))->assertRedirect();
    }

    public function test_pagina_nao_quebra_sem_redes_configuradas(): void
    {
        // Mesmo com a BD por omissão (sem links de redes), a página carrega
        // e mostra o estado «Por configurar».
        $resposta = $this->actingAs($this->admin())->get(route('admin.acompanhamento'));

        $resposta->assertOk();
        $resposta->assertSee('Redes sociais');
    }
}
