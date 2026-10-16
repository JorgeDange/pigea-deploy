<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aliases de autorização (fase.md §3 — EnsureUserIs*).
        // Uso: Route::middleware(['auth', 'role:admin'])->group(...)
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // Proxy de confiança (nginx/Apache a terminar TLS à frente da app).
        //
        // O `TrustProxies` já vem na pilha global por omissão e lê
        // `config('trustedproxy.proxies')` **no momento do pedido** (não no
        // bootstrap, onde a config ainda não existe) — por isso o valor vive em
        // `config/trustedproxy.php` e não aqui. Sem ele, atrás de um proxy,
        // `$request->secure()` devolve false e o HSTS do SecurityHeaders nunca
        // é emitido. Ver gmag/DEPLOY.md §5.
        //
        // Cabeçalhos de segurança em TODAS as respostas — não só nas rotas `web`.
        //
        // No grupo `web` não chega: (a) o mapa de prioridades do Laravel move o
        // `auth` para **antes** dele, por isso o redirect 302 pós-`auth` saía sem
        // headers (a excepção é apanhada no `carry()` do `auth` e a resposta
        // regressa para fora, sem passar por ele); (b) um 404 nunca chega a
        // entrar no grupo `web` (`findRoute` lança `NotFoundHttpException`
        // dentro do destino do pipeline global). Apensado ao fim da pilha global
        // envolve o router e cobre 200, 302, 404 e 500. Depois de `TrustProxies`
        // para `$request->secure()` ver o valor real por trás de um proxy (HSTS).
        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
