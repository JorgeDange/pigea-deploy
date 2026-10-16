<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Fase 14 — formulários de escrita do painel admin (Fase 8): criar notícia e
 * guardar configurações. Os POSTs validam, escrevem na BD e redirecionam —
 * tudo dentro da transação do teste (rollback automático).
 */
class AdminFormsTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_criar_noticia_valida_guarda_na_bd(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.noticia-nova.store', [], false), [
                'titulo' => 'Notícia de Teste PHPUnit',
                'estado' => 'publicar',
                'excerto' => 'Resumo de teste.',
                'conteudo' => 'Corpo da notícia de teste.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia de Teste PHPUnit',
            'status' => 'published',
            'author_id' => $this->admin()->id,
        ]);
    }

    public function test_criar_noticia_sem_titulo_devolve_erros(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.noticia-nova.store', [], false), ['titulo' => ''])
            ->assertSessionHasErrors('titulo');

        $this->assertDatabaseMissing('news', ['title' => '']);
    }

    public function test_configuracoes_geral_guarda_e_mostra_o_valor(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.configuracoes.geral', [], false), ['portal' => 'PIGEA Teste PHPUnit'])
            ->assertRedirect(route('admin.configuracoes', [], false));

        $this->get(route('admin.configuracoes', [], false))
            ->assertOk()
            ->assertSee('PIGEA Teste PHPUnit');
    }

    public function test_configuracoes_geral_sem_nome_do_portal_devolve_erro(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.configuracoes.geral', [], false), ['portal' => ''])
            ->assertSessionHasErrors('portal');
    }

    /*
    |----------------------------------------------------------------------
    | Modal "Adicionar utilizador"
    |----------------------------------------------------------------------
    |
    | No protótipo o `<form>` não tinha `action` nem botão de submissão — era
    | só visual. Aqui tem de criar mesmo a conta, com papel e password.
    |
    */

    public function test_adicionar_utilizador_cria_a_conta_com_o_papel(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.utilizador.novo', [], false), [
                'nome' => 'Editor Convidado',
                'email' => 'editor.convite@pigea.ao',
                'perfil' => 'editor',
                'password' => 'passe-segura-123',
                'password_confirmation' => 'passe-segura-123',
            ])
            ->assertRedirect(route('admin.utilizadores', [], false));

        $novo = User::where('email', 'editor.convite@pigea.ao')->firstOrFail();

        $this->assertTrue($novo->hasRole('editor'));
        $this->assertTrue(Hash::check('passe-segura-123', $novo->password));
        // `status` cai no default da migração — a conta nasce utilizável.
        $this->assertSame('active', $novo->status);
    }

    public function test_adicionar_utilizador_sem_password_devolve_erros(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.utilizador.novo', [], false), [
                'nome' => 'Sem Passe',
                'email' => 'sem.passe@pigea.ao',
                'perfil' => 'user',
            ])
            ->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'sem.passe@pigea.ao']);
    }

    public function test_adicionar_utilizador_com_email_repetido_devolve_erro(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.utilizador.novo', [], false), [
                'nome' => 'Repetido',
                'email' => 'test@example.com', // já existe (admin de testes)
                'perfil' => 'user',
                'password' => 'passe-segura-123',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_adicionar_utilizador_com_perfil_desconhecido_devolve_erro(): void
    {
        // Sem `Rule::in` o select dava para forjar a partir do cliente e criar
        // uma conta com papel arbitrário.
        $this->actingAs($this->admin())
            ->post(route('admin.utilizador.novo', [], false), [
                'nome' => 'Forjado',
                'email' => 'forjado@pigea.ao',
                'perfil' => 'superuser',
                'password' => 'passe-segura-123',
            ])
            ->assertSessionHasErrors('perfil');

        $this->assertDatabaseMissing('users', ['email' => 'forjado@pigea.ao']);
    }

    public function test_modal_adicionar_esta_ligado_ao_formulario(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.utilizadores', [], false))
            ->assertOk()
            // O form tem de apontar a uma rota real e levar o token. O `route()`
            // absoluto é o que a própria vista imprime.
            ->assertSee('action="'.route('admin.utilizador.novo').'"', false)
            ->assertSee('_token', false)
            // E o botão (que no protótipo ficava fora do form) tem de o submeter
            // pelo atributo HTML5 `form`.
            ->assertSee('form="modal-user-form"', false);
    }
}
