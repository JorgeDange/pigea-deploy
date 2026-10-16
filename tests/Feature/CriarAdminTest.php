<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * `php artisan pigea:criar-admin` — o único caminho para o primeiro admin.
 *
 * O painel admin exige `role:admin` para entrar, e o modal "Adicionar
 * utilizador" vive *dentro* desse painel — sem este comando não há como abrir
 * o portal a ninguém em produção (o DatabaseSeeder cria só o utilizador de
 * demonstração, que não se quer lá).
 */
class CriarAdminTest extends TestCase
{
    public function test_cria_um_admin_com_o_papel(): void
    {
        $this->artisan('pigea:criar-admin', ['email' => 'chefe@pigea.ao'])
            ->expectsQuestion('Nome a mostrar', 'Direcção PIGEA')
            ->expectsQuestion('Password (mínimo 8 caracteres)', 'passe-segura-123')
            ->expectsQuestion('Repita a password', 'passe-segura-123')
            ->assertExitCode(0);

        $novo = User::where('email', 'chefe@pigea.ao')->firstOrFail();

        $this->assertTrue($novo->hasRole('admin'));
        $this->assertTrue(Hash::check('passe-segura-123', $novo->password));
        // Tem de nascer já utilizável — senão o login falhava na primeira tentativa.
        $this->assertSame('active', $novo->status);
    }

    public function test_aceita_o_nome_pelo_option(): void
    {
        // Com --nome não há pergunta do nome, só as da password.
        $this->artisan('pigea:criar-admin', ['email' => 'outro@pigea.ao', '--nome' => 'Segundo Admin'])
            ->expectsQuestion('Password (mínimo 8 caracteres)', 'passe-segura-123')
            ->expectsQuestion('Repita a password', 'passe-segura-123')
            ->assertExitCode(0);

        $this->assertSame(
            'Segundo Admin',
            User::where('email', 'outro@pigea.ao')->firstOrFail()->name
        );
    }

    public function test_passwords_que_nao_coincidem_nao_criam_a_conta(): void
    {
        // Um typo na primeira gravação daria uma conta inacessível.
        $this->artisan('pigea:criar-admin', ['email' => 'typo@pigea.ao', '--nome' => 'Com Typo'])
            ->expectsQuestion('Password (mínimo 8 caracteres)', 'passe-segura-123')
            ->expectsQuestion('Repita a password', 'passe-segura-456')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'typo@pigea.ao']);
    }

    public function test_recusa_email_ja_registado(): void
    {
        // Senão daria para recriar/replicar a mesma conta e pôr em causa a
        // unicidade do email.
        $this->artisan('pigea:criar-admin', ['email' => 'test@example.com'])
            ->assertExitCode(1);

        $this->assertSame(1, User::where('email', 'test@example.com')->count());
    }

    public function test_recusa_email_malformado(): void
    {
        $this->artisan('pigea:criar-admin', ['email' => 'nao-e-email'])
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'nao-e-email']);
    }

    public function test_avisa_quando_o_papel_admin_nao_existe(): void
    {
        // Sem o RoleSeeder, `assignRole` rebentaria a meio. O comando tem de
        // avisar ANTES de criar o utilizador — senão ficava uma conta órfã sem
        // papel, invisível no painel.
        Role::where('name', 'admin')->delete();

        $this->artisan('pigea:criar-admin', ['email' => 'sem-papel@pigea.ao'])
            ->expectsOutputToContain('RoleSeeder')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'sem-papel@pigea.ao']);
    }

    public function test_password_curta_nao_cria_a_conta(): void
    {
        // A validação tem de correr ANTES do `User::create` — senão criava-se a
        // conta e rebentava depois, deixando um registo a meio.
        $this->artisan('pigea:criar-admin', ['email' => 'curta@pigea.ao'])
            ->expectsQuestion('Nome a mostrar', 'Password Curta')
            ->expectsQuestion('Password (mínimo 8 caracteres)', 'curta')
            ->expectsQuestion('Repita a password', 'curta')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'curta@pigea.ao']);
    }
}
