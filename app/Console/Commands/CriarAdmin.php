<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

/**
 * Cria o primeiro utilizador com role `admin`.
 *
 * Existe porque o painel admin exige `role:admin` para entrar — sem um admin
 * já criado não há forma de chegar ao modal "Adicionar utilizador" do próprio
 * painel. `DatabaseSeeder` cria só o utilizador de demonstração
 * (`test@example.com`), que **não** se quer em produção.
 *
 * O RoleSeeder tem de correr primeiro: cria os papéis e permissões.
 *
 * A password não é argumento de linha de comandos (apareceria no `ps` e no
 * histórico do shell) — é pedida em modo oculto.
 *
 * @see gmag/DEPLOY.md (guia de produção)
 */
class CriarAdmin extends Command
{
    /** A saída do comando é em português, como o resto da aplicação. */
    protected $signature = 'pigea:criar-admin
        {email : Email do administrador a criar}
        {--nome= : Nome a mostrar (pedido interactivamente se omitido)}';

    protected $description = 'Cria um utilizador com role admin para abrir o painel de gestão';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        // Mesma regra do registo público — e é aqui que se valida, não depois.
        $erros = Validator::make(
            ['email' => $email],
            ['email' => ['required', 'email', 'max:255', 'unique:users,email']]
        )->errors();

        if ($erros->isNotEmpty()) {
            $this->error('Email inválido ou já registado: '.$erros->first());

            return self::FAILURE;
        }

        // Os papéis são criados pelo RoleSeeder — sem eles `assignRole` rebenta
        // com RoleDoesNotExist a meio da criação.
        try {
            Role::findByName('admin', 'web');
        } catch (RoleDoesNotExist) {
            $this->error('Não existe o papel "admin". Corra primeiro:');

            $this->line('    php artisan db:seed --class=RoleSeeder --force');

            return self::FAILURE;
        }

        $nome = $this->option('nome') ?: $this->ask('Nome a mostrar');

        if (! $nome) {
            $this->error('É preciso um nome.');

            return self::FAILURE;
        }

        // A password é pedida duas vezes e comparada à mão. O `secret()` do
        // Laravel não tem modo "confirmar" (o 2.º argumento é só "oculto"), e
        // a regra `confirmed` exigiria um `password_confirmation` que este
        // contexto não tem.
        //
        // A confirmação importa: sem ela, um typo dava uma conta com uma
        // password que o administrador não sabe — e não há login ainda para a
        // corrigir pelo painel.
        $password = $this->secret('Password (mínimo 8 caracteres)');
        $repitida = $this->secret('Repita a password');

        if ($password !== $repitida) {
            $this->error('As passwords não coincidem. Nada foi criado.');

            return self::FAILURE;
        }

        $errosPassword = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', 'min:8']]
        )->errors();

        if ($errosPassword->isNotEmpty()) {
            $this->error('Password inválida: '.$errosPassword->first());

            return self::FAILURE;
        }

        $utilizador = User::create([
            'name' => $nome,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        // `status` cai no default da migração (`active`); escreve-se à mesma
        // para o comando não depender desse default.
        $utilizador->forceFill(['status' => 'active'])->save();
        $utilizador->assignRole('admin');

        $this->newLine();
        $this->info("Administrador criado: {$utilizador->email} ({$nome})");

        $this->line('Painel em: '.config('app.admin_path'));

        return self::SUCCESS;
    }
}
