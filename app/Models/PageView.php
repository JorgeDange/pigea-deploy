<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Contador agregado de visitas por dia (ver migration `create_page_views_table`).
 */
class PageView extends Model
{
    protected $fillable = ['date', 'visits'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'visits' => 'integer',
        ];
    }

    /** Soma incremental do dia de hoje (cria a linha se nao existir). */
    public static function incrementarHoje(): void
    {
        $hoje = now()->toDateString();

        $linha = static::firstWhere('date', $hoje);

        if ($linha !== null) {
            $linha->increment('visits');

            return;
        }

        static::create(['date' => $hoje, 'visits' => 1]);
    }

    /** Chave de data 'Y-m-d' por numero de dias atras, mais antigo primeiro. */
    public static function serieUltimosDias(int $dias): array
    {
        $fim = now();

        $chaves = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $chaves[] = $fim->copy()->subDays($i)->toDateString();
        }

        $mapa = static::query()
            ->whereIn('date', $chaves)
            ->pluck('visits', 'date');

        return array_map(fn ($dia) => (int) ($mapa[$dia] ?? 0), $chaves);
    }
}
