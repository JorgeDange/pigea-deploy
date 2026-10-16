<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Definições de `/admin/configuracoes` — par `key`/`value`.
 *
 * O protótipo (`admin/configuracoes.html`) tem 5 `<form class="dashboard-form">
 * ` sem action: nada era guardado. Aqui cada campo desses 5 cartões vira um
 * registo (`portal`, `descricao`, `logo`, `favicon`, `email`, `telefone`,
 * `endereco`, `facebook`, `instagram`, `linkedin`, `youtube`, `meta_title`,
 * `meta_description`, `template`, `texto_email`).
 *
 * Sem nada guardado, a página mostra exatamente os `placeholder` do protótipo
 * (os inputs vazios do HTML original). O cartão "Idiomas" não tem `<form>` no
 * protótipo, por isso continua visual.
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
        return static::query()->pluck('value', 'key')->all();
    }

    /**
     * Valor de um campo, com recurso a um de omissão (o placeholder do
     * protótipo serve de texto de exemplo quando a secção não foi configurada).
     */
    public static function obter(string $chave, ?string $padrao = null): ?string
    {
        return static::query()->where('key', $chave)->value('value') ?? $padrao;
    }

    /**
     * Guarda um conjunto de definições (um cartão do formulário).
     *
     * @param  array<string, string|null>  $definicoes
     */
    public static function guardar(array $definicoes): void
    {
        foreach ($definicoes as $chave => $valor) {
            static::query()->updateOrCreate(['key' => $chave], ['value' => $valor]);
        }
    }
}
