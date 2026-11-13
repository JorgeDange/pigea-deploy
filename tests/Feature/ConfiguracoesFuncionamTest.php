<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Tests\TestCase;

/**
 * Round-trip das configurações do admin (problema: os formulários gravavam
 * mas o site ignorava tudo — só as redes sociais alimentavam o frontend).
 * Agora: Geral (nome do portal + descrição/SEO), Contactos e SEO alimentam
 * o site público (layout, rodapé, página Contactos, formulário de contacto).
 */
class ConfiguracoesFuncionamTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_contactos_alimentam_o_site(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.configuracoes.contactos'), [
                'endereco' => 'Rua Teste 123, Benguela',
                'email' => 'geral.teste@pigea.ao',
                'telefone' => '+244 999 888 777',
            ])
            ->assertRedirect(route('admin.configuracoes'));

        $this->assertDatabaseHas('settings', ['key' => 'email', 'value' => 'geral.teste@pigea.ao']);

        // rodapé (presente em todas as páginas públicas)
        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('geral.teste@pigea.ao', $html);
        $this->assertStringContainsString('Rua Teste 123, Benguela', $html);

        // página /contactos
        $html = $this->get(route('contacts'))->assertOk()->getContent();
        $this->assertStringContainsString('geral.teste@pigea.ao', $html);
        $this->assertStringContainsString('+244 999 888 777', $html);
        $this->assertStringContainsString('Rua Teste 123, Benguela', $html);

        // limpeza para os testes seguintes
        $this->actingAs($this->admin())
            ->post(route('admin.configuracoes.contactos'), [
                'endereco' => 'Luanda, Angola',
                'email' => 'geral@pigea.ao',
                'telefone' => '+244 000 000 000',
            ]);
    }

    public function test_seo_alimenta_os_defaults_do_layout(): void
    {
        // guarda SEO configurado → os helpers do layout passam a devolver o da BD
        Setting::guardar(['meta_title' => 'Portal Teste Geociências', 'meta_description' => 'Descrição de teste SEO.']);
        $this->assertSame('Portal Teste Geociências', Setting::tituloPadrao());
        $this->assertSame('Descrição de teste SEO.', Setting::descricaoPadrao());

        // limpa SEO → fallback de sempre
        Setting::guardar(['meta_title' => '', 'meta_description' => '']);
        $this->assertStringContainsString('PIGEA', Setting::tituloPadrao());
    }

    public function test_portal_alimenta_a_pagina_contactos(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.configuracoes.geral'), [
                'portal' => 'PIGEA Teste XYZ',
                'descricao' => 'Descrição de teste.',
            ])
            ->assertRedirect(route('admin.configuracoes'));

        $html = $this->get(route('contacts'))->assertOk()->getContent();
        $this->assertStringContainsString('Contactos | PIGEA Teste XYZ', $html);

        // limpeza para os testes seguintes
        $this->actingAs($this->admin())
            ->post(route('admin.configuracoes.geral'), [
                'portal' => 'PIGEA',
                'descricao' => '',
            ]);
    }
}
