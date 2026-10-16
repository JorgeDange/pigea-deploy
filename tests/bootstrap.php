<?php

/*
 * Bootstrap dos testes (fase.md Fase 14 — "testar cada página com dados reais").
 *
 * Estratégia: garantir que a BD de teste `pigea_test` está migrada e semeada
 * ANTES de qualquer teste, sem arrancar a aplicação no processo do PHPUnit
 * (boot em bootstrap = "risky: removed error handlers" no PHPUnit 11):
 *
 *  - BD em falta / sem `migrations`  → `migrate:fresh --seed` (1.ª execução,
 *    ~4 min nesta máquina — só acontece uma vez);
 *  - migrações pendentes             → `migrate` (incremental, rápido);
 *  - `users` vazio                   → `db:seed`;
 *  - `permissions` < 18              → `db:seed --class=RoleSeeder` (Fase 13);
 *  - caso contrário                  → nada (estado já commitado).
 *
 * Com a seed já commitada, o `View::share` do `AppServiceProvider` nasce nos
 * boots de cada teste com dados reais e o `RefreshDatabase` usa só transações
 * (rollback ao fim de cada teste), portanto a seed sobrevive à execução toda.
 *
 * Segurança (fail-closed): recusa correr se `DB_DATABASE` não for explicitamente
 * `pigea_test` — nunca podemos aproximar-nos da base de desenvolvimento.
 */

require __DIR__.'/../vendor/autoload.php';

// ---------------------------------------------------------------------------
// 1. Guarda de ambiente
// ---------------------------------------------------------------------------

$dbDeclarado = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? '');

if ($dbDeclarado !== 'pigea_test') {
    throw new RuntimeException(
        "Testes recusados: DB_DATABASE tem de ser 'pigea_test' (actual: ".
        var_export($dbDeclarado, true).'). Verifica o <env> no phpunit.xml.'
    );
}

// ---------------------------------------------------------------------------
// 2. Credenciais (phpunit.xml/putenv vencem; fallback ao .env)
// ---------------------------------------------------------------------------

$env = [];
$caminhoEnv = __DIR__.'/../.env';

if (is_readable($caminhoEnv)) {
    foreach (file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linha) {
        $linha = trim($linha);

        if ($linha === '' || str_starts_with($linha, '#') || ! str_contains($linha, '=')) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $env[trim($chave)] = trim($valor, " \t\"'");
    }
}

$ler = static function (string $chave) use ($env) {
    $valor = getenv($chave);

    if ($valor !== false && $valor !== '') {
        return $valor;
    }

    return $env[$chave] ?? null;
};

$host = $ler('DB_HOST') ?? '127.0.0.1';
$porta = $ler('DB_PORT') ?? '3306';
$utilizador = $ler('DB_USERNAME') ?? '';
$password = $ler('DB_PASSWORD') ?? '';

// ---------------------------------------------------------------------------
// 3. Garante a BD de teste e diagnóstico de estado
// ---------------------------------------------------------------------------

$pdo = new PDO(
    "mysql:host={$host};port={$porta};charset=utf8mb4",
    $utilizador,
    $password,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
);

try {
    $pdo->exec('USE `pigea_test`');
} catch (PDOException $e) {
    throw new RuntimeException(
        "A BD de teste 'pigea_test' não existe ou não é acessível a '{$utilizador}'.\n".
        "Cria-a uma vez (ex.: mysql -u root):\n".
        '  CREATE DATABASE `pigea_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'.
        "\n  GRANT ALL PRIVILEGES ON `pigea_test`.* TO '{$utilizador}'@'%';\n".
        'Causa original: '.$e->getMessage(),
        0,
        $e
    );
}

// ---------------------------------------------------------------------------
// 4. Executa os comandos artisan necessários (em processo separado)
// ---------------------------------------------------------------------------

$php = escapeshellarg(PHP_BINARY);
$artisan = escapeshellarg(__DIR__.'/../artisan');

$executar = static function (string $argumentos) use ($php, $artisan): void {
    $comando = "{$php} {$artisan} {$argumentos} 2>&1";
    $saida = [];
    $exit = 0;
    exec($comando, $saida, $exit);

    if ($exit !== 0) {
        throw new RuntimeException(
            "artisan {$argumentos} falhou (exit {$exit}):\n".implode("\n", $saida)
        );
    }
};

// O processo filho tem de usar a BD de teste, independentemente do .env.
putenv('DB_DATABASE=pigea_test');
$_ENV['DB_DATABASE'] = 'pigea_test';
$_SERVER['DB_DATABASE'] = 'pigea_test';

$temTabelas = (bool) $pdo->query("SHOW TABLES LIKE 'migrations'")->fetch();

if (! $temTabelas) {
    // Primeira execução (ou BD apagada): esquema + dados de demonstração.
    $executar('migrate:fresh --seed --force');
} else {
    // Migrations pendentes (ficheiro novo desde a última execução)?
    $registradas = array_column(
        $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_ASSOC),
        'migration'
    );
    $emDisco = array_map(
        static fn (string $f): string => basename($f, '.php'),
        glob(__DIR__.'/../database/migrations/*.php') ?: []
    );

    if (array_diff($emDisco, $registradas) !== []) {
        $executar('migrate --force');
    }

    // Seed ausente (BD migrada à mão sem seed)?
    if ((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        $executar('db:seed --force');
    }

    // Permissões em falta (o RoleSeeder ganhou permissões — Fase 13)?
    // Idempotente: só cria o que falta e volta a sync todas ao admin.
    if ((int) $pdo->query('SELECT COUNT(*) FROM permissions')->fetchColumn() < 18) {
        $executar('db:seed --class=RoleSeeder --force');
    }
}

// ---------------------------------------------------------------------------
// 5. O RefreshDatabase de cada teste não repete a migração — só transações.
// ---------------------------------------------------------------------------

Illuminate\Foundation\Testing\RefreshDatabaseState::$migrated = true;
