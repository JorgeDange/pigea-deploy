<?php

/**
 * Helpers globais do PIGEA.
 *
 * `image_url($path, $fallback)`:
 *  - devolve URLs absolutas (http/https) como estão;
 *  - `asset()` caso contrário;
 *  - `asset($fallback)` quando $path é vazio e $fallback foi dado.
 *
 * Usado em todo o frontend para aceitar imagens de capa/imagem de conteúdo
 * provenientes de ficheiros locais OU de links externos (`image` do modelo,
 * `banner.imagem_url`, `banner_publicitario`).
 */

if (! function_exists('image_url')) {
    function image_url(?string $path, ?string $fallback = null): string
    {
        if ($path === null || trim($path) === '') {
            return $fallback !== null ? asset($fallback) : '';
        }

        $path = trim($path);

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : asset($path);
    }
}
