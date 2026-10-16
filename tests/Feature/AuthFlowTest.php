<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fase 14 — fluxos de autenticação: login por papel (RoleBasedHomeResponse),
 * mensagem de erro em português, logout, registo normal, registo de empresa
 * (bug 21) e o header auth-aware da home (decisão 24).
 */
class AuthFlowTest extends TestCase
{
    private function utilizador(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    /**
     * NOMES de rota (não caminhos): os data providers são `static` e correm
     * antes de a aplicação estar pronta — ver RoleAccessTest. O destino do
     * admin segue o prefixo configurável (ADMIN_PATH).
     */
    public static function logins(): array
    {
        return [
            'admin → painel admin' => ['test@example.com', 'admin.index'],
            'utilizador comum → /painel' => ['utilizador@pigea.ao', 'user.dashboard'],
            'empresa → /painel-empresa' => ['empresa@pigea.ao', 'company.dashboard'],
        ];
    }

    #[DataProvider('logins')]
    public function test_login_redirecciona_para_o_painel_do_papel(string $email, string $rota): void
    {
        $this->post('/login', ['email' => $email, 'password' => 'password'])
            ->assertRedirect(route($rota, [], false));

        $this->assertAuthenticated();
    }

    public function test_login_com_password_errada_mostra_mensagem_em_portugues(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => 'test@example.com', 'password' => 'errada'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->get('/login')->assertSee(
            'As credenciais indicadas não coincidem com as registadas no sistema.'
        );
    }

    public function test_logout_termina_a_sessao(): void
    {
        $this->actingAs($this->utilizador('utilizador@pigea.ao'))
            ->post('/logout')
            ->assertRedirect();

        $this->assertGuest();
        $this->get('/painel')->assertRedirect('/login');
    }

    public function test_registo_normal_entra_como_utilizador_no_painel(): void
    {
        $this->post('/register', [
            'name' => 'Ana Teste',
            'email' => 'ana.teste@pigea.ao',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/painel');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana.teste@pigea.ao']);
        $this->assertTrue($this->utilizador('ana.teste@pigea.ao')->hasRole('user'));
    }

    public function test_registo_com_email_duplicado_e_rejeitado(): void
    {
        $this->post('/register', [
            'name' => 'Repetido',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registo_de_empresa_entra_no_painel_da_empresa(): void
    {
        $this->post('/registo-empresa', [
            'empresa' => 'Minex Teste LDA',
            'nif' => '5417000000',
            'setor' => 'mineracao',
            'responsavel' => 'João Teste',
            'email' => 'empresa.teste@pigea.ao',
            'telefone' => '+244 920 000 000',
            'veracidade' => '1',
        ])->assertRedirect('/painel-empresa');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('company_profiles', ['email' => 'empresa.teste@pigea.ao']);
        $this->assertTrue($this->utilizador('empresa.teste@pigea.ao')->hasRole('company'));
    }

    public function test_registo_de_empresa_com_email_duplicado_e_rejeitado(): void
    {
        $this->post('/registo-empresa', [
            'empresa' => 'Repetida',
            'nif' => '5417000001',
            'setor' => 'mineracao',
            'responsavel' => 'Repetido',
            'email' => 'empresa@pigea.ao',
            'telefone' => '+244 920 000 001',
            'veracidade' => '1',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registo_de_empresa_sem_veracidade_e_rejeitado(): void
    {
        $this->post('/registo-empresa', [
            'empresa' => 'Sem Veracidade',
            'nif' => '5417000002',
            'setor' => 'mineracao',
            'responsavel' => 'Teste',
            'email' => 'sem.veracidade@pigea.ao',
            'telefone' => '+244 920 000 002',
        ])->assertSessionHasErrors('veracidade');

        $this->assertGuest();
    }

    public function test_header_guest_mostra_o_botao_entrar_do_prototipo(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<div class="srf_btn htact show-reg-form">', false)
            ->assertSee('Entrar');
    }

    /** NOMES de rota — o href esperado tem de bater certo com o header. */
    public static function papeis(): array
    {
        return [
            'utilizador comum → /painel' => ['utilizador@pigea.ao', 'user.dashboard'],
            'empresa → /painel-empresa' => ['empresa@pigea.ao', 'company.dashboard'],
            'admin → painel admin' => ['test@example.com', 'admin.index'],
        ];
    }

    #[DataProvider('papeis')]
    public function test_header_logado_aponta_para_o_painel_do_papel(string $email, string $rota): void
    {
        // O header escreve caminhos relativos ("/painel"), por isso o
        // `false` — o caminho do admin vem do ADMIN_PATH (.env).
        $href = route($rota, [], false);

        $this->actingAs($this->utilizador($email))
            ->get('/')
            ->assertOk()
            ->assertSee('<a href="'.$href.'" class="srf_btn htact">', false)
            ->assertSee('O meu painel')
            ->assertDontSee('<div class="srf_btn htact show-reg-form">', false);
    }
}
