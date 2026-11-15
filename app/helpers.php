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

/**
 * urlAbsoluta($url):
 *  - URLs com esquema http:// ou https:// ficam como estao;
 *  - URLs // (protocol-relative) recebem https:;
 *  - dominio.com/caminho (sem esquema) recebe https://;
 *  - caminhos /internos viram URL absoluta a partir do APP_URL;
 *  - youtube/pigea (sem dominio) e sempre erros de conteudo do admin -
 *    devolvemos https:// + o valor para nao quebrarmos silenciosamente.
 *
 * NORMALIZA URLs DE REDES SOCIAIS (settings) tanto na gravacao como no render.
 */

if (! function_exists('urlAbsoluta')) {
    function urlAbsoluta(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (str_starts_with($url, '/')) {
            return url($url);
        }
        if (preg_match('#^[a-z0-9-]+(\.[a-z0-9-]+)+(/.*)?$#i', $url)) {
            return 'https://' . $url;
        }
        return 'https://' . $url;
    }
}

