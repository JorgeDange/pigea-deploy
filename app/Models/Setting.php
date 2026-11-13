<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Definições de `/admin/configuracoes` — par `key`/`value`.
 *
 * O protótipo (`admin/configuracoes.html`) tinha 5 `<form>` sem action: nada
 * era guardado. Aqui cada campo vira um registo (`portal`, `descricao`,
 * `logo`, `favicon`, `email`, `telefone`, `endereco`, `facebook`,
 * `instagram`, `linkedin`, `youtube`, `meta_title`, `meta_description`,
 * `template`, `texto_email`).
 *
 * `todas()` é cacheada 5 minutos (o site chama 2 componentes por página que
 * usam as mesmas definições); `guardar()` invalida a cache na hora.
 */
class Setting extends Model
{
    /**
     * Apenas estes atributos são de atribuição em massa: `key` identifica o
     * campo e `value` o conteúdo.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Todos os valores para a vista devolver o que já está guardado.
     *
     * @return array<string, string|null>
     */
    public static function todas(): array
    {
        return Cache::remember('settings.todas', now()->addMinutes(5), fn () =>
            static::query()->pluck('value', 'key')->all()
        );
    }

    /**
     * Valor de um campo, com recurso a um de omissão (o placeholder do
     * protótipo serve de texto de exemplo quando a secção não foi configurada).
     */
    public static function obter(string $chave, ?string $padrao = null): ?string
    {
        return static::todas()[$chave] ?? $padrao;
    }

    /** URL absoluto da imagem configurada (upload no storage OU link externo). */
    public static function imagem(?string $caminho, string $fallback): string
    {
        $caminho = trim((string) $caminho);

        if ($caminho === '') {
            return asset($fallback);
        }

        return str_starts_with($caminho, 'http') ? $caminho : asset('storage/' . $caminho);
    }

    /** Nome público do portal ("Nome do portal" nas configurações). */
    public static function portal(): string
    {
        return trim((string) (static::todas()['portal'] ?? '')) ?: 'PIGEA';
    }

    /** Meta description/og padrão — SEO configurado OU texto de sempre. */
    public static function descricaoPadrao(): string
    {
        $seo = trim((string) (static::todas()['meta_description'] ?? ''));

        return $seo !== ''
            ? $seo
            : 'PIGEA reúne notícias, empregos, formação e legislação do setor das geociências em Angola, com informação verificada e gratuita.';
    }

    /** Meta title/og padrão — SEO configurado OU nome do portal. */
    public static function tituloPadrao(): string
    {
        $seo = trim((string) (static::todas()['meta_title'] ?? ''));

        return $seo !== '' ? $seo : static::portal() . ' — Plataforma de Impulsionamento das Geociências em Angola';
    }

    /**
     * Guarda um conjunto de definições (um cartão do formulário) e
     * invalida a cache das definições.
     *
     * @param  array<string, string|null>  $definicoes
     */
    public static function guardar(array $definicoes): void
    {
        foreach ($definicoes as $chave => $valor) {
            static::query()->updateOrCreate(['key' => $chave], ['value' => $valor]);
        }

        Cache::forget('settings.todas');
    }
}
