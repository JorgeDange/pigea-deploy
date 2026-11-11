<?php

namespace App\Support;

/**
 * Áreas de atuação do Diretório (restrutura.md §4 — checkboxes dos
 * formulários públicos, guardadas como JSON).
 *
 * Servem de whitelist na validação: o cliente só submete via checkboxes,
 * mas o valor chega ao servidor por POST — filtrar sempre contra esta
 * lista evita gravar texto arbitrário no JSON.
 */
final class AreasAtuacao
{
    public const LISTA = [
        'Mineração',
        'Petróleo e Gás',
        'Geociências',
        'Água',
        'Ambiente',
        'Energia',
    ];

    /**
     * Devolve apenas as áreas submetidas que constam da whitelist,
     * sem duplicados.
     *
     * @param  mixed  $submetidas  valor bruto do formulário (array esperado)
     * @return list<string>
     */
    public static function filtrar(mixed $submetidas): array
    {
        if (! is_array($submetidas)) {
            return [];
        }

        return array_values(array_unique(array_intersect(
            $submetidas,
            self::LISTA
        )));
    }
}
