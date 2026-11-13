<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * FASE E2 (estendida) — única fonte de verdade do filtro anti-bot do portal.
 *
 * Dois usos:
 *  1) `contar()` — contadores por artigo (`news`, `projects`):
 *       bots nunca contam + mesma sessão não conta o mesmo artigo
 *       duas vezes em 5 minutos;
 *  2) `BOTS` (regex) — o contador global de visitas (`CountPublicVisits`),
 *       que mantém a sua semântica própria e só troca a lista de padrões.
 */
final class ContadorDeVisitas
{
    /** Padrões de user-agent considerados bots/crawlers. */
    public const BOTS = '/bot|crawl|spider|slurp|wget|curl|pingdom|uptime|facebookexternalhit|twitterbot|linkedinbot|whatsapp|telegram|discord|slackbot|applebot|bingpreview|pinterest|embedly|preview|monitor|validator/i';

    public static function ehBot(?string $agente): bool
    {
        $agente = trim((string) $agente);

        return $agente === '' || preg_match(self::BOTS, $agente) === 1;
    }

    /**
     * Regista uma leitura real: ignora bots e a mesma sessão dentro de 5 minutos.
     * $prefixo isola a chave de sessão por tipo (ex.: `noticia_42`, `projeto_42`).
     */
    public static function contar(Request $request, Model $modelo, string $prefixo): bool
    {
        if (self::ehBot($request->userAgent())) {
            return false;
        }

        $chave = $prefixo . '_' . $modelo->getKey();
        $ultima = session($chave);

        if ($ultima !== null && (now()->timestamp - (int) $ultima) < 300) {
            return false;
        }

        session([$chave => now()->timestamp]);

        $modelo->increment('views_count');

        return true;
    }
}
