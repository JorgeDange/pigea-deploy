<?php

/**
 * Fase 2/3 (cms_v.md) — lista branca ÚNICA do corpo de artigo.
 *
 * Usada por:
 *   - TinyMCE (init):  valid_elements / extended_valid_elements / valid_classes /
 *                      paste_* / style_formats (classes de "Estilos do PIGEA")
 *   - ArticleSanitizer (servidor): HTMLPurifier at save-time (Fase 3)
 *
 * Regra: se uma classe/elemento não estiver aqui, NÃO sobrevive a guardar —
 * no editor (removido pelo valid_elements) ou no servidor (removido pelo purificador).
 */

return [
    'elements' => [
        'p', 'h2', 'h3', 'strong', 'em', 'u', 's',
        'ul', 'ol', 'li', 'a', 'blockquote',
        'figure', 'figcaption', 'img',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'br', 'div', 'hr',
    ],

    /** Atributos por elemento (class só se a classe estiver em classe_ok). */
    'attributes' => [
        'a' => ['href', 'target', 'rel'],
        'img' => ['src', 'alt', 'width', 'height', 'loading'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ],

    /** Classes permitidas (Fase 1/2: drop-cap, pull-quote, narrow, two-cols, media-*). */
    'classes_ok' => ['drop-cap', 'pull-quote', 'narrow', 'two-cols', 'media-left', 'media-right'],

    /** Schemes permitidos em href/src (http/https/mailto em links). */
    'uri_allowed' => ['http', 'https', 'mailto'],
];
