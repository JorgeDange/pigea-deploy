<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\News;
use App\Models\Project;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fase 14 — todas as páginas públicas renderizam com os dados reais do
 * `ContentSeeder` (migrado e semeado pelo `tests/bootstrap.php`).
 */
class PublicPagesTest extends TestCase
{
    /**
     * Páginas públicas do protótipo (fase.md §4.7) + os 4 fragmentos AJAX
     * das tabs da home que o `scripts.js` continua a pedir.
     */
    public static function paginasPublicas(): array
    {
        return [
            'home' => ['/'],
            'notícias' => ['/noticias'],
            'projetos' => ['/projetos'],
            'tecnologias' => ['/tecnologias'],
            'fornecedores' => ['/fornecedores'],
            'diretório' => ['/diretorio'],
            'empregos' => ['/empregos'],
            'formação' => ['/formacao'],
            'legislação' => ['/legislacao'],
            'eventos' => ['/eventos'],
            'investigação' => ['/investigacao'],
            'entrevistas' => ['/entrevistas'],
            'pesquisa (sem termo)' => ['/pesquisa'],
            'sobre' => ['/sobre'],
            'contactos' => ['/contactos'],
            'login' => ['/login'],
            'registo' => ['/register'],
            'password esquecida' => ['/forgot-password'],
            'reset de password' => ['/reset-password/token-de-teste'],
            'registo de empresa' => ['/registo-empresa'],
            'página 404 do protótipo' => ['/404'],
            'fragmento AJAX mineração' => ['/ajax/noticias-mineracao.html'],
            'fragmento AJAX água' => ['/ajax/noticias-agua.html'],
            'fragmento AJAX ambiente' => ['/ajax/noticias-ambiente.html'],
            'fragmento AJAX investigação' => ['/ajax/noticias-investigacao.html'],
        ];
    }

    #[DataProvider('paginasPublicas')]
    public function test_pagina_publica_renderiza(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    public function test_home_mostra_noticias_reais_do_seeder(): void
    {
        $titulo = News::published()->orderBy('id')->value('title');

        $this->assertNotNull($titulo, 'O ContentSeeder deve ter publicado notícias.');
        $this->get('/')->assertOk()->assertSee($titulo);
    }

    public function test_detalhe_de_noticia_com_slug_real(): void
    {
        $noticia = News::published()->orderBy('id')->first();

        $this->get('/noticias/'.$noticia->slug)
            ->assertOk()
            ->assertSee($noticia->title);
    }

    public function test_detalhe_de_projeto_com_slug_real(): void
    {
        $projeto = Project::published()->orderBy('id')->first();

        $this->get('/projetos/'.$projeto->slug)
            ->assertOk()
            ->assertSee($projeto->title);
    }

    public function test_detalhe_de_vaga_com_slug_real(): void
    {
        $vaga = Job::published()->orderBy('id')->first();

        $this->get('/empregos/'.$vaga->slug)
            ->assertOk()
            ->assertSee($vaga->title);
    }

    public function test_slug_inexistente_devolve_404(): void
    {
        $this->get('/noticias/slug-que-nao-existe')->assertNotFound();
    }
}
