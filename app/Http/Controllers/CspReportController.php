<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Endpoint de relatórios da CSP (fase.md §6 — decisão 4 do memoria.md).
 *
 * O navegador envia aqui as violações da política
 * `Content-Security-Policy-Report-Only` (headers em `SecurityHeaders`). Só
 * regista e responde 204 — enquanto a CSP não é obrigatória não há nada que
 * bloquear; o objetivo é recolher evidência para a tornar restritiva depois.
 *
 * Proteções: throttled (evita inundar o log) + CSRF desligado (o POST vem do
 * navegador, que não tem token desta origem).
 */
class CspReportController
{
    /** Corpo máximo guardado por relatório (bytes). */
    private const TAMANHO_MAXIMO = 4000;

    public function __invoke(Request $request): Response
    {
        $bruto = (string) $request->getContent();
        $dados = json_decode($bruto, true);

        Log::warning('CSP Report-Only', [
            'ip' => $request->ip(),
            'agente' => (string) $request->userAgent(),
            'relatorio' => is_array($dados) ? $dados : substr($bruto, 0, self::TAMANHO_MAXIMO),
        ]);

        return response()->noContent(204);
    }
}
