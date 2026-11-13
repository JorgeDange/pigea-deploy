<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Fase 3 (cms_v.md) — sanitizador do corpo de artigo.
 *
 * Regra de ouro: sanitiza AO GUARDAR (nunca só na saída). O HTML já limpo é
 * persistido; o site público mantém `{!! $post->content !!}` sobre conteúdo
 * confiado.
 *
 * A lista branca vem de `config/article.php` (mesma fonte do TinyMCE).
 */
class ArticleSanitizer
{
    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $elements = implode(',', config('article.elements'));

        $config = HTMLPurifier_Config::createDefault();

        // Só os elementos do whitelist (whitelist por nome).
        $config->set('HTML.AllowedElements', $elements);

        // Atributos: apenas os por elemento (class filtrada pelo whitelist de classes
        // via HTML.AllowedClasses).
        $attributes = [];
        foreach (config('article.attributes') as $el => $attrs) {
            foreach ($attrs as $attr) {
                $attributes[] = $el . '.' . $attr;
            }
        }
        // A classe é universalmente permitida nos elementos que a suportam —
        // Attr.AllowedClasses restringe os VALORES (array associativo).
        $config->set('HTML.AllowedAttributes', array_merge($attributes, ['*.class']));
        $config->set('Attr.AllowedClasses', array_map(fn () => true, array_flip(config('article.classes_ok'))));

        // Esquemas de URI: http/https/mailto. URLs relativos (/storage/...) são
        // mantidos pelo próprio purificador (não exigem esquema).
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);

        // DTD XHTML 1.0 do purificador não conhece figure/figcaption — adiciona.
        // Caminho otimizado do HTMLPurifier: definição cacheada em disco
        // (storage/framework/cache/htmlpurifier — ignorado pelo git).
        $cachePath = storage_path('framework/cache/htmlpurifier');
        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0775, true);
        }
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('HTML.DefinitionID', 'pigea-article-def');
        $config->set('HTML.DefinitionRev', 3);

        // Caminho "optimized": se já houver definição cacheada, maybeGet devolve
        // null e os elementos já estão lá; caso contrário, customiza.
        $rawDefinition = $config->maybeGetRawDefinition('HTML');
        if ($rawDefinition !== null) {
            // O módulo Target (XHTML 1.1) conflita com o `a` do DTD — adicionamos
            // o atributo diretamente via módulo anónimo (merge) e o rel
            // "noopener noreferrer" é imposto no pós-processamento abaixo.
            $rawDefinition->addAttribute('a', 'target', 'Enum#_blank,_self,_parent,_top');
            $rawDefinition->addElement('figure', 'Block', 'Flow', 'Common');
            $rawDefinition->addElement('figcaption', 'Block', 'Flow', 'Common');
            $rawDefinition->addAttribute('img', 'loading', 'Enum#eager,lazy');
        }

        // Sem style / on*/script/iframe: não estão na whitelist ⇒ removidos.
        // rel="noopener noreferrer" em target=_blank é forçado via CSS? não —
        // forçado abaixo por pós-processamento.

        $purifier = new HTMLPurifier($config);
        $clean = $purifier->purify($html);

        // Garante rel="noopener noreferrer" em todos os target="_blank" remanescentes.
        $clean = preg_replace_callback(
            '~<a\b([^>]*)>~i',
            static function (array $m): string {
                $attrs = $m[1];
                if (stripos($attrs, 'target="_blank"') === false && stripos($attrs, "target='_blank'") === false) {
                    return $m[0];
                }
                if (stripos($attrs, 'rel=') !== false) {
                    // rel existente: normaliza para noopener noreferrer
                    return preg_replace('/rel\s*=\s*("|\')[^"\']*\1/i', 'rel="noopener noreferrer"', $m[0]) ?? $m[0];
                }
                return '<a' . rtrim($attrs) . ' rel="noopener noreferrer">';
            },
            $clean
        );

        return $clean;
    }
}
