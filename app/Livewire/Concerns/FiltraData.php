<?php

namespace App\Livewire\Concerns;

/**
 * Filtro de data no formato do placeholder do protótipo (`mm/aaaa`,
 * `dd/mm/aaaa` ou texto parcial) — mesmo comportamento usado pelas tabelas
 * do admin (`ContentTable`) e pelas do painel (candidaturas/vagas).
 */
trait FiltraData
{
    protected function filtrarData($query, string $filtro, string $coluna = 'created_at'): void
    {
        $filtro = trim($filtro);

        if ($filtro === '') {
            return;
        }

        if (preg_match('#^(\d{1,2})/(\d{4})$#', $filtro, $m)) {
            $query->whereRaw(
                "DATE_FORMAT({$coluna}, '%m/%Y') = ?",
                [str_pad($m[1], 2, '0', STR_PAD_LEFT).'/'.$m[2]]
            );

            return;
        }

        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $filtro, $m)) {
            $query->whereRaw(
                "DATE_FORMAT({$coluna}, '%d/%m/%Y') = ?",
                [str_pad($m[1], 2, '0', STR_PAD_LEFT).'/'.str_pad($m[2], 2, '0', STR_PAD_LEFT).'/'.$m[3]]
            );

            return;
        }

        $query->whereRaw("DATE_FORMAT({$coluna}, '%d/%m/%Y') LIKE ?", ['%'.$filtro.'%']);
    }
}
