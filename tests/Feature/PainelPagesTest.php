<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fase 14 — todas as páginas dos 3 painéis (utilizador, empresa e admin)
 * renderizam com os dados de demonstração do `PainelSeeder`.
 *
 * Contagem de GETs: 6 (utilizador) + 8 (empresa) + 13 (admin) = 27 —
 * os POSTs destas rotas estão cobertos nos testes de formulários e nos
 * testes Node funcionais (%TEMP%\opencode\painel-test.js, 30/30).
 */
class PainelPagesTest extends TestCase
{
    private function utilizador(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public static function paginasUtilizador(): array
    {
        return [
            'dashboard' => ['/painel'],
            'perfil' => ['/painel/perfil'],
            'candidaturas' => ['/painel/candidaturas'],
            'guardadas' => ['/painel/guardadas'],
            'notificações' => ['/painel/notificacoes'],
            'definições' => ['/painel/definicoes'],
        ];
    }

    #[DataProvider('paginasUtilizador')]
    public function test_pagina_do_painel_do_utilizador(string $uri): void
    {
        $this->actingAs($this->utilizador('utilizador@pigea.ao'))
            ->get($uri)
            ->assertOk();
    }

    public static function paginasEmpresa(): array
    {
        return [
            'dashboard' => ['/painel-empresa'],
            'perfil' => ['/painel-empresa/perfil'],
            'vagas' => ['/painel-empresa/vagas'],
            'nova vaga' => ['/painel-empresa/vagas/nova'],
            'candidaturas' => ['/painel-empresa/candidaturas'],
            'oportunidades' => ['/painel-empresa/oportunidades'],
            'estatísticas' => ['/painel-empresa/estatisticas'],
            'definições' => ['/painel-empresa/definicoes'],
        ];
    }

    #[DataProvider('paginasEmpresa')]
    public function test_pagina_do_painel_da_empresa(string $uri): void
    {
        $this->actingAs($this->utilizador('empresa@pigea.ao'))
            ->get($uri)
            ->assertOk();
    }

    /**
     * NOMES de rota (não caminhos): os data providers são `static` e correm
     * antes de a aplicação estar pronta — ver RoleAccessTest. O prefixo do
     * admin segue o ADMIN_PATH (.env), por isso não se escreve "/admin".
     */
    public static function paginasAdmin(): array
    {
        return [
            'dashboard' => ['admin.index'],
            'notícias' => ['admin.noticias'],
            'eventos' => ['admin.eventos'],
            'investigação' => ['admin.investigacao'],
            'entrevistas' => ['admin.entrevistas'],
            'legislação' => ['admin.legislacao'],
            'projetos' => ['admin.projetos'],
            'empresas' => ['admin.empresas'],
            'vagas' => ['admin.vagas'],
            'utilizadores' => ['admin.utilizadores'],
            'nova notícia' => ['admin.noticia-nova'],
            'novo projeto' => ['admin.projeto-novo'],
            'configurações' => ['admin.configuracoes'],
        ];
    }

    #[DataProvider('paginasAdmin')]
    public function test_pagina_do_painel_admin(string $rota): void
    {
        $this->actingAs($this->utilizador('test@example.com'))
            ->get(route($rota, [], false))
            ->assertOk();
    }

    /**
     * Gráficos do dashboard — no `admin/index.html` eram 3 caixas com o texto
     * "reservado para Chart.js"; aqui passam a ser gráficos reais.
     *
     * O Chart.js tem de vir de `public/js/` e não de um CDN: a CSP é
     * `script-src 'self'`, por isso um CDN seria sinalizado em cada
     * carregamento pelo endpoint `/csp-report`.
     */
    public function test_dashboard_mostra_os_tres_graficos(): void
    {
        $resposta = $this->actingAs($this->utilizador('test@example.com'))
            ->get(route('admin.index', [], false))
            ->assertOk();

        foreach (['grafico-conteudos', 'grafico-utilizadores', 'grafico-provincias'] as $id) {
            $resposta->assertSee('id="'.$id.'"', false);
        }

        $resposta->assertSee('js/chart.umd.js', false);
    }

    public function test_series_mensais_têm_sempre_12_meses(): void
    {
        $conteudo = $this->actingAs($this->utilizador('test@example.com'))
            ->get(route('admin.index', [], false))
            ->assertOk()
            ->getContent();

        // São 2 séries mensais (linha e barras), cada uma com 12 valores —
        // é o que garante que o eixo x não salta meses nem inventa picos
        // onde houve meses sem registos.
        $this->assertSame(
            2,
            preg_match_all('/data:\s*\[(?:\d+\s*,\s*){11}\d+\]/', $conteudo),
            'Faltam séries mensais de 12 valores no dashboard.'
        );
    }

    public function test_serie_mensal_comeca_doze_meses_atras(): void
    {
        // O eixo tem de começar no mês de há 11 meses (contínuo), não no mês
        // actual: uma série só com o mês corrente não mostra tendência nenhuma.
        $primeiroMes = now()->subMonthsNoOverflow(11)->locale('pt')->translatedFormat('M y');

        $this->actingAs($this->utilizador('test@example.com'))
            ->get(route('admin.index', [], false))
            ->assertOk()
            ->assertSee($primeiroMes, false);
    }
}
