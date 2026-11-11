<?php

namespace Tests\Feature;

use App\Services\ArticleSanitizer;
use Tests\TestCase;

/**
 * Fase 3 (cms_v.md) — o "gate" de sanitização do corpo do artigo.
 *
 * Cada destes casos tem de:
 *   - remover o perigoso;
 *   - manter o que o editor legitima (classes PIGEA, alt text).
 */
class ArticleSanitizerTest extends TestCase
{
    private function clean(string $html): string
    {
        return ArticleSanitizer::clean($html) ?? '';
    }

    public function test_remove_script(): void
    {
        $out = $this->clean('<p>Q</p><script>alert(1)</script>');

        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('alert', $out);
    }

    public function test_remove_atributos_on(): void
    {
        $out = $this->clean('<img src="/storage/a.webp" alt="ok" onerror="alert(1)"><p onclick="alert(1)">x</p>');

        $this->assertStringNotContainsString('onerror', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringContainsString('src="/storage/a.webp"', $out);
        $this->assertStringContainsString('alt="ok"', $out);
    }

    public function test_remove_style(): void
    {
        $out = $this->clean('<p style="color:red">x</p>');

        $this->assertStringNotContainsString('style=', $out);
        $this->assertStringContainsString('<p>x</p>', $out);
    }

    public function test_classe_desconhecida_removida_mas_classes_do_pigea_ficam(): void
    {
        $out = $this->clean('<p class="drop-cap outro z-ultimo">A</p><blockquote class="pull-quote estranha"><p>B</p></blockquote>');

        $this->assertStringContainsString('class="drop-cap"', $out);
        $this->assertStringContainsString('class="pull-quote"', $out);
        $this->assertStringNotContainsString('outro', $out);
        $this->assertStringNotContainsString('estranha', $out);
    }

    public function test_href_javascript_removido(): void
    {
        $out = $this->clean('<a href="javascript:alert(1)">mau</a>');

        $this->assertStringNotContainsString('javascript:', $out);
        $this->assertStringNotContainsString('href=', $out);
    }

    public function test_target_blank_ganha_rel(): void
    {
        $out = $this->clean('<a href="https://exemplo.com/x" target="_blank">bom</a>');

        $this->assertStringContainsString('target="_blank"', $out);
        $this->assertMatchesRegularExpression('/rel="[^"]*noopener[^"]*noreferrer[^"]*"|rel="[^"]*noreferrer[^"]*noopener[^"]*"/', $out);
    }

    public function test_iframe_removido(): void
    {
        $out = $this->clean('<p>A</p><iframe src="https://evil.example"></iframe>');

        $this->assertStringNotContainsString('<iframe', $out);
        $this->assertStringContainsString('<p>A</p>', $out);
    }

    public function test_classes_caption_left_right_multi(): void
    {
        $out = $this->clean('<figure class="media-left"><img src="/storage/x.webp" alt="a"><figcaption>C</figcaption></figure><figure class="media-right"><img src="/storage/y.webp" alt="b"></figure><div class="two-cols"><p>z</p></div>');

        foreach (['media-left', 'media-right', 'two-cols'] as $cls) {
            $this->assertStringContainsString($cls, $out, "Classe $cls devia sobreviver.");
        }
    }

    public function test_vazio_ou_null_devolve_null(): void
    {
        $this->assertNull(ArticleSanitizer::clean(''));
        $this->assertNull(ArticleSanitizer::clean(null));
    }
}
