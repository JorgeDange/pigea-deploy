<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Troca da própria senha em /admin/perfil:
 * senha atual obrigatória, régua Password::default(), sessão atual
 * mantida, TODAS as outras sessões da conta terminadas.
 */
class PerfilAlterarSenhaTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_perfil_mostra_a_secao_de_senha(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.perfil'))
            ->assertOk()
            ->assertSee('A minha senha')
            ->assertSee('current_password')
            ->assertSee('Informação da conta');
    }

    public function test_trocar_senha_com_dados_validos(): void
    {
        $utilizador = $this->admin();

        // Sessão "noutro dispositivo" para o teste poder provar a limpeza.
        DB::table('sessions')->insert([
            'id' => 'sessao-de-outro-dispositivo',
            'user_id' => $utilizador->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => base64_encode('x'),
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($utilizador)
            ->post(route('admin.perfil.senha'), [
                'current_password' => 'password',
                'password' => 'NovaSenha123',
                'password_confirmation' => 'NovaSenha123',
            ])
            ->assertRedirect(route('admin.perfil'));

        $this->assertTrue(
            Hash::check('NovaSenha123', $utilizador->fresh()->password),
            'A senha deve ficar atualizada.'
        );

        // A sessão "outro dispositivo" foi terminada; não podemos assertar
        // sobre a atual (handler de sessão do phpunit é file/array).
        $this->assertDatabaseMissing('sessions', ['id' => 'sessao-de-outro-dispositivo']);
    }

    public function test_senha_atual_errada_mantem(): void
    {
        $utilizador = $this->admin();
        $hashAntes = $utilizador->password;

        $this->actingAs($utilizador)
            ->post(route('admin.perfil.senha'), [
                'current_password' => 'errada-errada',
                'password' => 'NovaSenha123',
                'password_confirmation' => 'NovaSenha123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame($hashAntes, $utilizador->fresh()->password);
    }

    public function test_confirmacao_divergente(): void
    {
        $utilizador = $this->admin();

        $this->actingAs($utilizador)
            ->post(route('admin.perfil.senha'), [
                'current_password' => 'password',
                'password' => 'NovaSenha123',
                'password_confirmation' => 'OutraSenha999',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_acesso_anonimo_redireciona(): void
    {
        $this->post(route('admin.perfil.senha'), [
            'current_password' => 'password',
            'password' => 'NovaSenha123',
            'password_confirmation' => 'NovaSenha123',
        ])->assertRedirect();
    }
}
