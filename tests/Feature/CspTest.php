<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * CSP Report-Only + endpoint de relatórios (memoria.md decisão 4 / pendências).
 *
 * A política NÃO bloqueia nada (é o objectivo enquanto o frontend mantém
 * scripts inline do protótipo) — serve para recolher relatórios em
 * `POST /csp-report` e decidir quando a tornar obrigatória.
 */
class CspTest extends TestCase
{
    public function test_pagina_publica_serve_a_csp_report_only(): void
    {
        // `assertHeader()` devolve o próprio TestResponse (asserção encadeável),
        // não o valor do cabeçalho — o valor tem de sair de `headers->get()`.
        $resposta = $this->get('/')->assertOk();
        $resposta->assertHeader('Content-Security-Policy-Report-Only');
        $cabecalho = (string) $resposta->headers->get('Content-Security-Policy-Report-Only');

        $this->assertStringContainsString("default-src 'self'", $cabecalho);
        $this->assertStringContainsString('report-uri /csp-report', $cabecalho);
        $this->assertStringContainsString('report-to csp-endpoint', $cabecalho);
        // Iframes dos 6 vídeos externos (os únicos recursos de rede do protótipo).
        $this->assertStringContainsString('frame-src https://www.youtube.com', $cabecalho);
        $this->assertStringContainsString('https://player.vimeo.com', $cabecalho);
    }

    public function test_csp_tambem_nas_paginas_de_auth_e_painel(): void
    {
        $this->get('/login')->assertOk()
            ->assertHeader('Content-Security-Policy-Report-Only');
        // Caminho do admin vem do ADMIN_PATH (.env) — ver RoleAccessTest.
        $this->get(route('admin.index', [], false))->assertRedirect('/login') // sem sessão → continua com o header
            ->assertHeader('Content-Security-Policy-Report-Only');
    }

    /**
     * O SecurityHeaders vive na pilha **global** (bootstrap/app.php) — é o que
     * cobre o 302 pós-`auth` e os 404, que nunca entram no grupo `web`.
     * Regressão: se voltar para o grupo `web`, o mapa de prioridades coloca o
     * `auth` antes dele e estas duas asserções falham.
     */
    public function test_csp_tambem_nas_respostas_de_erro(): void
    {
        $this->get('/rota-que-nao-existe')->assertNotFound()
            ->assertHeader('Content-Security-Policy-Report-Only');
    }

    /*
    |----------------------------------------------------------------------
    | HSTS + proxies de confiança
    |----------------------------------------------------------------------
    |
    | O SecurityHeaders só emite `Strict-Transport-Security` quando
    | `$request->secure()`. Sem proxy confiante, atrás de um nginx/Apache a
    | terminar TLS, o PHP vê `http://` e o HSTS **nunca sairia** — é o que
    | `config/trustedproxy.php` resolve.
    |
    */

    public function test_hsts_nao_sai_em_http(): void
    {
        // Nada de HSTS num pedido simples: senão o browser gravaria a política
        // apontando a um host que ainda não serve HTTPS.
        $this->get('/')->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    /**
     * Regressão de segurança: um cliente pode mandar `X-Forwarded-Proto: https`
     * **por si próprio**. Com a lista de proxies vazia (dev, e por omissão em
     * produção até configurar), esse cabeçalho tem de ser ignorado — senão
     * qualquer um forçava a emissão de HSTS e ficava com o browser preso em
     * HTTPS num host que não o serve.
     */
    public function test_hsts_ignora_x_forwarded_proto_sem_proxy_confia(): void
    {
        config(['trustedproxy.proxies' => null]);

        $this->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('/')
            ->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_sai_por_tras_de_um_proxy_confia(): void
    {
        // Nos testes o REMOTE_ADDR é 127.0.0.1 (é de onde vêm os pedidos).
        config(['trustedproxy.proxies' => '127.0.0.1']);

        $this->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('/')
            ->assertOk()
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_proxy_confia_mas_pedido_chegou_em_http_nao_emite_hsts(): void
    {
        config(['trustedproxy.proxies' => '127.0.0.1']);

        $this->get('/')->assertOk()
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    /**
     * A chave vazia tem de dar null e não `['']`: o framework faz
     * `explode(',', $valor)` e uma string vazia viraria um "proxy" inválido.
     */
    public function test_config_de_proxies_vazia_nao_confia_em_ninguem(): void
    {
        $this->assertNull(config('trustedproxy.proxies'));
    }

    public function test_reporting_endpoints_aponta_para_o_endpoint(): void
    {
        $this->get('/')->assertOk()
            ->assertHeader('Reporting-Endpoints', 'csp-endpoint="/csp-report"');
    }

    public function test_endpoint_de_relatorios_aceita_e_regista(): void
    {
        Log::spy();

        $this->postJson('/csp-report', [
            'csp-report' => [
                'document-uri' => 'http://127.0.0.1:8000/noticias',
                'violated-directive' => "img-src 'self'",
                'blocked-uri' => 'https://exemplo.ao/foto.jpg',
            ],
        ])->assertNoContent(204);

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_endpoint_nao_exige_csrf_e_ignora_corpo_invalido(): void
    {
        // POST sem token (o navegador nunca o tem) — o VerifyCsrfToken está
        // desligado só nesta rota; sem isso devolveria 419.
        $this->post('/csp-report', [], ['Content-Type' => 'application/json'])
            ->assertNoContent(204);

        $this->post('/csp-report', ['isto' => 'não é json-csp'])
            ->assertNoContent(204);
    }

    public function test_endpoint_tem_throttle(): void
    {
        // Limpa o contador (cache é array nos testes) para não herdar os hits
        // dos testes anteriores desta classe.
        cache()->flush();

        // 61 pedidos no mesmo minuto → o 61.º é 429 (throttle:60,1).
        foreach (range(1, 60) as $i) {
            $this->postJson('/csp-report', ['n' => $i])->assertNoContent(204);
        }

        $this->postJson('/csp-report', ['n' => 61])->assertStatus(429);
    }
}
