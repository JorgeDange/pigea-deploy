<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fase 14 — autorização (fase.md §5 Fase 13): convidado é encaminhado para o
 * login pelo middleware `auth`; papel errado leva 403 do `EnsureUserHasRole`
 * (nunca redirect — isso mascararia erros de autorização).
 */
class RoleAccessTest extends TestCase
{
    /**
     * Os valores são NOMES de rota, não caminhos: os data providers são
     * `static` e correm antes de a aplicação estar pronta — `route()` não
     * existe aí. Os métodos de teste resolvem com `route($nome, [], false)`,
     * o que faz os testes seguirem sempre o prefixo configurável do painel
     * admin (ADMIN_PATH) em vez de escrever "/admin" à mão.
     */
    public static function rotasProtegidas(): array
    {
        return [
            'admin' => ['admin.index'],
            'painel do utilizador' => ['user.dashboard'],
            'painel da empresa' => ['company.dashboard'],
        ];
    }

    #[DataProvider('rotasProtegidas')]
    public function test_convidado_e_encaminhado_para_o_login(string $rota): void
    {
        $this->get(route($rota, [], false))->assertRedirect('/login');
    }

    public static function acessosIndevidos(): array
    {
        return [
            'utilizador comum no painel admin' => ['utilizador@pigea.ao', 'admin.index'],
            'utilizador comum no painel da empresa' => ['utilizador@pigea.ao', 'company.dashboard'],
            'empresa no painel do utilizador' => ['empresa@pigea.ao', 'user.dashboard'],
            'empresa no painel admin' => ['empresa@pigea.ao', 'admin.index'],
            'admin no painel do utilizador' => ['test@example.com', 'user.dashboard'],
            'admin no painel da empresa' => ['test@example.com', 'company.dashboard'],
        ];
    }

    #[DataProvider('acessosIndevidos')]
    public function test_papel_sem_acesso_recebe_403(string $email, string $rota): void
    {
        $utilizador = User::where('email', $email)->firstOrFail();

        $this->actingAs($utilizador)->get(route($rota, [], false))->assertForbidden();
    }

    public function test_papel_com_acesso_entra_no_seu_painel(): void
    {
        $this->actingAs(User::where('email', 'utilizador@pigea.ao')->firstOrFail())
            ->get('/painel')
            ->assertOk();

        $this->actingAs(User::where('email', 'empresa@pigea.ao')->firstOrFail())
            ->get('/painel-empresa')
            ->assertOk();

        $this->actingAs(User::where('email', 'test@example.com')->firstOrFail())
            ->get(route('admin.index', [], false))
            ->assertOk();
    }

    /**
     * Regressão da rota configurável do painel admin (ADMIN_PATH).
     *
     * Duas propriedades: a rota nomeada — é por aí que as vistas geram os
     * links, nenhuma escreve "/admin" à mão — vive no caminho do .env; e o
     * caminho antigo deixa de servir o painel.
     *
     * O 404 só se aplica quando ADMIN_PATH está customizado: com o default
     * "admin" o caminho antigo seria o próprio painel.
     */
    public function test_painel_admin_vive_no_caminho_configurado_e_nao_em_admin(): void
    {
        $caminho = config('app.admin_path');

        $this->assertStringEndsWith('/'.$caminho, route('admin.index', [], false));

        if ($caminho !== 'admin') {
            // Sem redirect: um redirect denunciaria o novo caminho.
            $this->get('/admin')->assertNotFound();
        }
    }
}
