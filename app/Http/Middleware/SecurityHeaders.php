<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeçalhos de segurança base.
 *
 * CSP (decisão 4 → feita): aplicada em modo `Content-Security-Policy-Report-Only`
 * com endpoint de relatórios (`POST /csp-report` → CspReportController). Nada é
 * bloqueado — o frontend reaproveita HTML/jQuery do protótipo com scripts inline
 * e iframes YouTube/Vimeo, por isso a política inclui `'unsafe-inline'`/
 * `'unsafe-eval'` em `script-src` e os domínios de vídeo em `frame-src`. Os
 * relatórios acumulados é que dizem quando dá para apertar (e quando passar a
 * obrigatória) — ver memoria.md (pendências de produção).
 *
 * Registado na **pilha global** (`bootstrap/app.php`), não no grupo `web`:
 * lá o mapa de prioridades colocava o `auth` antes dele (o 302 pós-login saía
 * sem headers) e os 404 nunca entram no grupo `web` — ver bug 22 do memoria.md.
 */
class SecurityHeaders
{
    /**
     * Política Report-Only. Regras mantidas por uma razão concreta:
     *  - scripts/styles inline do protótipo + Alpine/Livewire → 'unsafe-inline'
     *    ('unsafe-eval' também: o Alpine avalia expressões x-data via Function);
     *  - vídeos são iframes YouTube/Vimeo (os únicos recursos de rede);
     *  - imagens/fontes são locais (+ data: URI dos plugins).
     */
    private const CSP_REPORT_ONLY =
        "default-src 'self'; "
        ."base-uri 'self'; "
        ."object-src 'none'; "
        ."frame-ancestors 'self'; "
        ."form-action 'self'; "
        ."script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
        ."style-src 'self' 'unsafe-inline'; "
        ."font-src 'self' data:; "
        ."img-src 'self' data:; "
        ."media-src 'self'; "
        ."connect-src 'self'; "
        ."frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com; "
        .'report-uri /csp-report; report-to csp-endpoint';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Impede que o navegador interprete uploads trocados de tipo (ex.: .svg/.html) como execução.
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Mesma origem apenas — bloqueia clickjacking dos painéis.
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Sem acesso a APIs de dispositivo que a plataforma não precisa.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()'
        );

        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // HSTS só faz sentido em HTTPS — em http://127.0.0.1 ficaria gravado na BD do browser.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP Report-Only (não bloqueia) + endpoint do Reporting-Tools API;
        // `report-uri` cobre os browsers mais antigos, `report-to` os novos.
        $response->headers->set('Content-Security-Policy-Report-Only', self::CSP_REPORT_ONLY);
        $response->headers->set('Reporting-Endpoints', 'csp-endpoint="/csp-report"');

        return $response;
    }
}
