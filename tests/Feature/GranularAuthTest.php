<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fase 13 — autorização granular (`fase.md` §5: "can:/gates").
 *
 * Três camadas, da mais ampla para a mais específica:
 *  1. `role:` (EnsureUserHasRole) — abre o grupo de rotas (ver RoleAccessTest);
 *  2. `can:<permissão>` (Spatie) — autoriza a ação concreta dentro do grupo;
 *  3. gates de posse (`AuthServiceProvider`) — só o dono do registo o manipula.
 *
 * Revogar a permissão mantendo a role prova que o `can:` decide sozinho.
 */
class GranularAuthTest extends TestCase
{
    /**
     * NOMES de rota (não caminhos): os data providers são `static` e correm
     * antes de a aplicação estar pronta — ver RoleAccessTest.
     */
    public static function rotasComPermissao(): array
    {
        return [
            'empresa em company.vaga-nova' => ['company.vaga-nova', 'empresa@pigea.ao', 'job.manage'],
            'empresa em company.oportunidades' => ['company.oportunidades', 'empresa@pigea.ao', 'opportunity.manage'],
            'utilizador em user.notificacoes' => ['user.notificacoes', 'utilizador@pigea.ao', 'notification.manage'],
            'utilizador em user.perfil' => ['user.perfil', 'utilizador@pigea.ao', 'profile.manage'],
            'admin em admin.noticias' => ['admin.noticias', 'test@example.com', 'news.manage'],
            'admin em admin.utilizadores' => ['admin.utilizadores', 'test@example.com', 'user.manage'],
            'admin em admin.configuracoes' => ['admin.configuracoes', 'test@example.com', 'settings.manage'],
        ];
    }

    /**
     * Com a permissão → 200; revogada à role (mantendo a role) → 403.
     * A revogação corre numa transação do RefreshDatabase — faz rollback no fim.
     */
    #[DataProvider('rotasComPermissao')]
    public function test_rota_exige_a_permissao_especifica(string $rota, string $email, string $permissao): void
    {
        $utilizador = User::where('email', $email)->firstOrFail();

        $uri = route($rota, [], false);

        $this->actingAs($utilizador)->get($uri)->assertOk();

        $utilizador->roles->first()->revokePermissionTo($permissao);

        $this->actingAs($utilizador)->get($uri)->assertForbidden();
    }

    public function test_empresa_so_elimina_as_suas_vagas(): void
    {
        $empresa = User::where('email', 'empresa@pigea.ao')->firstOrFail();

        $minhaVaga = Job::query()->where('author_id', $empresa->id)->firstOrFail();
        $outraVaga = Job::query()->where('author_id', '!=', $empresa->id)->firstOrFail();

        // Vaga própria → elimina (soft delete) e volta com redirect.
        $this->actingAs($empresa)
            ->post("/painel-empresa/vagas/eliminar/{$minhaVaga->id}")
            ->assertRedirect();
        $this->assertSoftDeleted('vacancies', ['id' => $minhaVaga->id]);

        // Vaga de outrem → 403 do gate de posse e registo intacto.
        $this->actingAs($empresa)
            ->post("/painel-empresa/vagas/eliminar/{$outraVaga->id}")
            ->assertForbidden();
        $this->assertNotNull($outraVaga->fresh());
    }

    public function test_gate_de_posse_distingle_dono_de_outrem(): void
    {
        $empresa = User::where('email', 'empresa@pigea.ao')->firstOrFail();

        $minhaVaga = Job::query()->where('author_id', $empresa->id)->firstOrFail();
        $outraVaga = Job::query()->where('author_id', '!=', $empresa->id)->firstOrFail();

        $this->assertTrue($empresa->can('delete-vacancy', $minhaVaga));
        $this->assertFalse($empresa->can('delete-vacancy', $outraVaga));
    }

    public function test_permissao_admin_nao_existe_sem_o_seeder(): void
    {
        // Garante que as 4 permissões novas da Fase 13 chegaram à BD: o admin
        // tem de poder definir as configurações (se faltasse, o `can:` daria
        // 403 a quem tem a role — falha aqui em vez de dar 403 silencioso).
        $admin = User::where('email', 'test@example.com')->firstOrFail();

        foreach (['company.moderate', 'job.moderate', 'user.manage', 'settings.manage'] as $permissao) {
            $this->assertTrue($admin->can($permissao), "Falta a permissão admin `{$permissao}` (corre o RoleSeeder).");
        }
    }
}
