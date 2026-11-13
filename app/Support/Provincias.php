<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Províncias de Angola — fonte única de verdade do site (18 oficiais + Nacional).
 *
 * Feedback do cliente (2026-11-13, FASE E1): o editor de notícias só tinha
 * Luanda, Benguela, Huíla, Namibe e Nacional; o formulário de projetos tinha
 * outras 6 (incluindo slugs legados «cuanza-sul»). Agora TUDO deriva de
 * Provincias::LISTA — as 18 províncias oficiais de Angola + «Nacional».
 *
 * «Outros» não é uma província: é a opção que revela um texto livre no
 * formulário; o texto digitado é guardado directamente em `province`
 * (decisão do cliente, doc/fases_e.md §E1.1 — sem coluna extra).
 *
 * Os slugs legados («cuanza-sul» → «kwanza-sul») são normalizados na escrita
 * e aceites nos rótulos — os projetos antigos não se perdem.
 */
class Provincias
{
    /** As 18 províncias oficiais de Angola + «Nacional» (slug => rótulo). */
    public const LISTA = [
        'bengo' => 'Bengo',
        'benguela' => 'Benguela',
        'bie' => 'Bié',
        'cabinda' => 'Cabinda',
        'cuando-cubango' => 'Cuando Cubango',
        'kwanza-norte' => 'Cuanza Norte',
        'kwanza-sul' => 'Cuanza Sul',
        'cunene' => 'Cunene',
        'huambo' => 'Huambo',
        'huila' => 'Huíla',
        'luanda' => 'Luanda',
        'lunda-norte' => 'Lunda Norte',
        'lunda-sul' => 'Lunda Sul',
        'malanje' => 'Malanje',
        'moxico' => 'Moxico',
        'namibe' => 'Namibe',
        'uige' => 'Uíge',
        'zaire' => 'Zaire',
        'nacional' => 'Nacional',
    ];

    /** Slugs legados (formulário de projetos antigo) → slug canónico. */
    private const LEGADO = [
        'cuanza-sul' => 'kwanza-sul',
        'quando-cubango' => 'cuando-cubango',
    ];

    /** Normaliza o valor para o slug canónico (texto livre passa intacto, truncado a 100). */
    public static function normalizar(?string $valor): ?string
    {
        $bruto = trim((string) $valor);

        if ($bruto === '') {
            return null;
        }

        $slug = self::LEGADO[mb_strtolower($bruto)] ?? $bruto;

        return mb_substr($slug, 0, 100);
    }

    /**
     * Resolve o valor final a gravar em `province`.
     *
     * - O formulário envia `provincia` (slug ou «outros») e, opcionalmente,
     *   `provincia_outro` (texto livre para «Outros»).
     * - «outros» → o texto livre passa a ser o valor gravado; vazio → null.
     * - Qualquer outro valor → texto livre normalizado (os formulários só
     *   permitem os slugs da lista, mas a validação ao nível do Request é
     *   `string|max:100`, por isso texto livre à porta é igualmente aceite —
     *   é o comportamento que o cliente pediu para notícias internacionais).
     */
    public static function resolve(?string $provincia, ?string $provinciaOutro = null): ?string
    {
        $bruto = trim((string) $provincia);

        if (mb_strtolower($bruto) === 'outros') {
            return self::normalizar($provinciaOutro);
        }

        return self::normalizar($bruto);
    }

    /** Rótulo para apresentação (gráfico do painel, listagens): slug conhecido → rótulo oficial; texto livre → o próprio texto; «outros» → «Outros». */
    public static function rotulo(?string $valor): string
    {
        $bruto = trim((string) $valor);

        if ($bruto === '') {
            return '';
        }

        $slug = self::LEGADO[mb_strtolower($bruto)] ?? $bruto;

        return self::LISTA[$slug] ?? Str::headline($bruto);
    }

    /** Regras de validação comuns aos formulários (provincia + provincia_outro). */
    public static function regras(bool $provinciaOutroObrigatoria = true): array
    {
        return [
            'provincia' => ['nullable', 'string', 'max:100'],
            'provincia_outro' => array_filter([
                'nullable',
                'string',
                'max:100',
                $provinciaOutroObrigatoria ? 'required_if:provincia,outros' : null,
            ]),
        ];
    }
}
