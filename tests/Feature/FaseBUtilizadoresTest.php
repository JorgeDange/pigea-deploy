<?php

namespace Tests\Feature;

use App\Livewire\Admin\UsersTable;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase B (prop.md) — gestão de utilizadores a sério:
 * editar dados, repor senha com troca obrigatória, desativar de verdade,
 * eliminar com confirmação por email, paginação à escolha, permissões
 * (institucional → admin; newsletter → admin e editor).
 */
class FaseBUtilizadoresTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    private function editor(): User
    {
        // SEM cache estático — cada teste recria a BD (migrate:fresh) e um
        // objeto em cache apontaria para uma linha que já não existe.
        $editor = User::factory()->create([
            'name' => 'Editor de Teste',
            'email' => 'editor.faseb@pigea.example.com',
        ]);
        $editor->assignRole('editor');

        return $editor;
    }

    /* ---------- B1: editar dados ---------- */

    public function test_admin_edita_dados_do_utilizador(): void
    {
        $alvo = $this->editor();

        $this->actingAs($this->admin())
            ->put(route('admin.utilizador.atualizar', $alvo), [
                'nome' => 'Editor Renomeado',
                'email' => 'editor.renomeado@pigea.example.com',
                'perfil' => 'editor',
                'telefone' => '+244 999 000 111',
                'provincia' => 'Luanda',
            ])
            ->assertRedirect(route('admin.utilizador.editar', $alvo));

        $alvo->refresh();
        $this->assertSame('Editor Renomeado', $alvo->name);
        $this->assertSame('editor.renomeado@pigea.example.com', $alvo->email);
        $this->assertSame('+244 999 000 111', $alvo->phone);
        $this->assertSame('Luanda', $alvo->province);
    }

    public function test_admin_nao_pode_mudar_o_proprio_perfil(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.utilizador.atualizar', $admin), [
                'nome' => $admin->name,
                'email' => $admin->email,
                'perfil' => 'editor',
                'telefone' => '',
                'provincia' => '',
            ])
            ->assertSessionHasErrors('perfil');

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    /* ---------- B2: repor senha com troca obrigatória ---------- */

    public function test_repor_senha_marca_troca_obrigatoria_e_mata_sessoes(): void
    {
        $alvo = $this->editor();

        DB::table('sessions')->insert([
            'id' => 'sessao-de-teste-editor',
            'user_id' => $alvo->id,
            'ip_address' => '10.9.9.9',
            'user_agent' => 'phpunit',
            'payload' => 'x',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.utilizador.senha', $alvo), [
                'password' => 'TempSenha123',
                'password_confirmation' => 'TempSenha123',
            ])
            ->assertRedirect(route('admin.utilizador.editar', $alvo));

        $alvo->refresh();
        $this->assertTrue($alvo->must_change_password);
        $this->assertTrue(Hash::check('TempSenha123', $alvo->password));
        $this->assertDatabaseMissing('sessions', ['id' => 'sessao-de-teste-editor']);
    }

    public function test_trocar_no_perfil_limpa_a_flag_de_troca_obrigatoria(): void
    {
        $editor = $this->editor();
        $editor->forceFill(['must_change_password' => true])->save();

        // Painel trancado: tudo redireciona para o perfil.
        $this->actingAs($editor)->get(route('admin.noticias'))
            ->assertRedirect(route('admin.perfil'));

        $this->actingAs($editor)->get(route('admin.perfil'))->assertOk();

        // Troca a senha no próprio perfil — a flag tem de cair.
        $this->actingAs($editor)->post(route('admin.perfil.senha'), [
            'current_password' => 'password',
            'password' => 'SenhaDoUtilizador1',
            'password_confirmation' => 'SenhaDoUtilizador1',
        ])->assertRedirect(route('admin.perfil'));

        $this->assertFalse($editor->fresh()->must_change_password);

        // Painel livre outra vez.
        $this->actingAs($editor)->get(route('admin.noticias'))->assertOk();
    }

    /* ---------- B3: desativar com efeito real ---------- */

    public function test_desativado_e_deslogado_de_imediato(): void
    {
        $editor = $this->editor();

        DB::table('sessions')->insert([
            'id' => 'sessao-editor-desativado',
            'user_id' => $editor->id,
            'ip_address' => '10.9.9.9',
            'user_agent' => 'phpunit',
            'payload' => 'x',
            'last_activity' => now()->timestamp,
        ]);

        Livewire::actingAs($this->admin())
            ->test(UsersTable::class)
            ->call('alternar', $editor->id);

        $this->assertSame('blocked', $editor->fresh()->status);
        $this->assertDatabaseMissing('sessions', ['id' => 'sessao-editor-desativado']);

        // O middleware desloga mesmo com sessão válida (instância FRESCA — a
        // em memória ainda diz 'active').
        $this->actingAs(User::findOrFail($editor->id))->get(route('admin.noticias'))
            ->assertRedirect(); // para o login, com mensagem 'estado'

        $this->assertGuest();
    }

    public function test_reativar_devolve_acesso(): void
    {
        $editor = $this->editor();
        $editor->forceFill(['status' => 'blocked'])->save();

        Livewire::actingAs($this->admin())
            ->test(UsersTable::class)
            ->call('alternar', $editor->id);

        $this->assertSame('active', $editor->fresh()->status);
    }

    /* ---------- B4: eliminar com confirmação ---------- */

    public function test_eliminar_exige_email_exato(): void
    {
        $alvo = $this->editor();

        Livewire::actingAs($this->admin())
            ->test(UsersTable::class)
            ->call('pedirEliminar', $alvo->id)
            ->set('confirmarEmail', 'nao-e-este@email.com')
            ->call('eliminar');

        $this->assertDatabaseHas('users', ['id' => $alvo->id]);

        Livewire::actingAs($this->admin())
            ->test(UsersTable::class)
            ->call('pedirEliminar', $alvo->id)
            ->set('confirmarEmail', $alvo->email)
            ->call('eliminar');

        $this->assertDatabaseMissing('users', ['id' => $alvo->id]);
    }

    public function test_eliminar_com_artigos_associados_e_bloqueado(): void
    {
        $alvo = $this->editor();
        News::query()->firstOrFail()->forceFill(['author_id' => $alvo->id])->save();

        Livewire::actingAs($this->admin())
            ->test(UsersTable::class)
            ->call('pedirEliminar', $alvo->id)
            ->set('confirmarEmail', $alvo->email)
            ->call('eliminar')
            ->assertSet('erro', fn (string $erro) => str_contains($erro, 'artigos'));

        $this->assertDatabaseHas('users', ['id' => $alvo->id]);
    }

    /* ---------- B5: permissões do cliente ---------- */

    public function test_editor_nao_ve_institucional_mas_ve_newsletter(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->get(route('admin.equipa'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.parceiros'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.newsletter'))->assertOk();
    }

    public function test_admin_ve_institucional_e_newsletter(): void
    {
        $this->actingAs($this->admin())->get(route('admin.equipa'))->assertOk();
        $this->actingAs($this->admin())->get(route('admin.parceiros'))->assertOk();
        $this->actingAs($this->admin())->get(route('admin.newsletter'))->assertOk();
    }

    public function test_editor_nao_gira_utilizadores_nem_configuracoes(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->get(route('admin.utilizadores'))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.utilizador.editar', $this->admin()))->assertForbidden();
        $this->actingAs($editor)->get(route('admin.configuracoes'))->assertForbidden();
    }
}
