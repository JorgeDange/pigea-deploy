<?php

namespace App\Support;

/**
 * Mapa canônico das secções hierárquicas da Equipa e dos tipos de Parceiros
 * (sobre-nós: /equipa e /parceiros).
 *
 * As únicas "opções fixas" no Cargo — quando `cargo_select = 'outros'` o
 * admin obriga a preencher `cargo_outro` (validação do POST). O rótulo a
 * mostrar no cartão é: rótulo da secção quando != outros, ou o valor de
 * `cargo_outro` quando é `outros`.
 */
final class Institucional
{
    /** SECÇÃO => [rótulo público]. Ordem = ordem na página. */
    public const SECCOES_EQUIPA = [
        'direcao' => 'Direção',
        'coordenacao-editorial' => 'Coordenação Editorial',
        'redacao' => 'Redação',
        'tecnologia-dados' => 'Tecnologia & Dados',
        'parcerias-comercial' => 'Parcerias & Comercial',
        'administracao' => 'Administração',
        'outros' => 'Outros',
    ];

    public const TIPOS_PARCEIRO = [
        'empresa' => 'Empresa',
        'profissional' => 'Profissional',
    ];

    public static function seccaoEquipa(?string $slug): string
    {
        return self::SECCOES_EQUIPA[$slug ?? ''] ?? 'Outros';
    }

    public static function cargoVisivel(object $membro): string
    {
        if ($membro->cargo_select === 'outros') {
            return trim((string) ($membro->cargo_outro ?? '')) ?: 'Equipa PIGEA';
        }

        return self::seccaoEquipa($membro->cargo_select);
    }

    public static function tipoParceiro(?string $tipo): string
    {
        return self::TIPOS_PARCEIRO[$tipo ?? ''] ?? 'Empresa';
    }

    /**
     * Iniciais (máx. 2) para o fallback quando há foto ausente:
     *  "Ana Maria Coutinho" → "AC".
     */
    public static function iniciais(string $nome): string
    {
        $partes = preg_split('/\s+/u', trim($nome)) ?: [];
        $partes = array_values(array_filter($partes, fn ($p) => $p !== ''));

        if ($partes === []) {
            return '?';
        }

        if (count($partes) === 1) {
            return mb_strtoupper(mb_substr($partes[0], 0, 1));
        }

        return mb_strtoupper(mb_substr($partes[0], 0, 1) . mb_substr($partes[count($partes) - 1], 0, 1));
    }
}
