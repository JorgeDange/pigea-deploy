<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contador de visitas do portal (cms_pro.md — cartão "Visitas no Site").
 *
 * Terminable middleware: a contagem acontece DEPOIS de o response ser enviado,
 * logo não pesa na latência percebida. Conta apenas GETs bem-sucedidos a
 * páginas públicas — nunca /admin, /livewire, /csp-report nem assets. Sem
 * dados pessoais (sem IP/cookie/UA); bots conhecidos ignorados.
 */
class CountPublicVisits
{
    /** Prefixos estaticos que nunca contam (assets, relatorios, excecoes). */
    private const IGNORAR_ESTATICOS = ['livewire', 'csp-report', 'storage', 'build', 'hot', 'favicon'];

    /** Padrões de user-agent considerados bots (ignorar contagem). */
    private const BOT = '#bot|spider|crawler|slurp|wget|curl|pingdom|uptime|facebookexternalhit#i';

    /** Prefixos a ignorar, incluindo o ADMIN_PATH configurado no .env. */
    private function ignorarPrefixos(): array
    {
        $admin = trim((string) config('app.admin_path', 'gestao-pigea'), '/');

        return array_merge(
            $admin !== '' ? [$admin] : [],
            ['admin'], // fallback legado, por seguranca
            self::IGNORAR_ESTATICOS
        );
    }

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->isMethod('GET')) {
            return;
        }

        if ((int) $response->getStatusCode() !== 200) {
            return;
        }

        $caminho = trim($request->path(), '/');

        foreach ($this->ignorarPrefixos() as $prefixo) {
            if ($caminho === $prefixo || str_starts_with($caminho, $prefixo.'/')) {
                return;
            }
        }

        $agente = (string) $request->userAgent();
        if ($agente !== '' && preg_match(self::BOT, $agente) === 1) {
            return;
        }

        try {
            PageView::incrementarHoje();
        } catch (\Throwable) {
            // O contador nunca pode partir o request público do visitante.
        }
    }
}
