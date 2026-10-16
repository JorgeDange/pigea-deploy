<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles e permissões do PIGEA (fase.md §5 Fase 13 / plan.md §6.2).
 *
 * Privilégio mínimo: as permissões ficam explícitas por role em vez de
 * depender só do nome da role, para que a autorização possa ser granular
 * quando os CRUDs existirem.
 */
class RoleSeeder extends Seeder
{
    /**
     * Permissões por role. `*` é reservado ao admin.
     *
     * @var array<string, list<string>>
     */
    protected array $permissions = [
        // `*` mantém o admin compatível com qualquer permissão futura; as 4
        // concretas dão cobertura às rotas admin sem permissão correspondente
        // (empresas, vagas, utilizadores e configurações) — Fase 13.
        'admin' => ['*', 'company.moderate', 'job.moderate', 'user.manage', 'settings.manage'],
        'editor' => [
            'news.manage', 'project.manage', 'event.manage',
            'legislation.manage', 'research.manage', 'interview.manage',
        ],
        'company' => [
            'company.profile', 'job.manage', 'opportunity.manage',
            'application.receive',
        ],
        'user' => [
            'job.apply', 'job.save', 'profile.manage', 'notification.manage',
        ],
    ];

    public function run(): void
    {
        // Limpa o cache de permissões — obrigatório após alterar roles manualmente.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $roleName => $permissionNames) {
            $role = Role::findOrCreate($roleName, 'web');

            foreach ($permissionNames as $permissionName) {
                if ($permissionName === '*') {
                    continue;
                }

                $permission = Permission::findOrCreate($permissionName, 'web');
                $role->givePermissionTo($permission);
            }
        }

        // O admin passa a herdar explicitamente todas as permissões concretas,
        // para que `can('news.manage')` funcione sem wildcard em gates.
        $admin = Role::findByName('admin', 'web');
        $admin->syncPermissions(Permission::all());

        // Utilizador de demonstração (criado pelo DatabaseSeeder antes deste
        // seeder) passa a poder entrar nos painéis protegidos.
        $demo = User::where('email', 'test@example.com')->first();

        if ($demo !== null) {
            $demo->assignRole('admin');
        }
    }
}
