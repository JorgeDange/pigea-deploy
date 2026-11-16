<?php

namespace Tests\Feature;

use App\Mail\NewsletterArtigo;
use App\Models\News;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Fase N2 — «Aviso de novo artigo» (envio manual pelo painel):
 * só ativos, no máximo 100 por clique, retoma para os restantes,
 * modo teste não toca nos subscritores.
 */
class NewsletterArtigoTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    private function artigo(): News
    {
        $artigo = News::published()->orderByDesc('published_at')->firstOrFail();
        $artigo->forceFill(['excerpt' => 'Resumo de teste para a newsletter.'])->save();

        return $artigo;
    }

    private function criarAtivos(int $n): void
    {
        for ($i = 0; $i < $n; $i++) {
            NewsletterSubscriber::create([
                'email' => "sub{$i}@example.com",
                'token' => str_repeat((string) (($i % 9) + 1), 64),
                'status' => 'active',
                'confirmed_at' => now(),
            ]);
        }
    }

    public function test_envia_apenas_aos_ativos_e_grava_edicao(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $artigo = $this->artigo();
        $this->criarAtivos(3);
        // pendente e cancelado NÃO recebem:
        NewsletterSubscriber::create(['email' => 'pendente@x.com', 'token' => str_repeat('2', 64), 'status' => 'pending', 'confirmed_at' => null]);
        NewsletterSubscriber::create(['email' => 'cancelado@x.com', 'token' => str_repeat('3', 64), 'status' => 'cancelled', 'confirmed_at' => now()]);

        $this->actingAs($admin)
            ->post(route('admin.newsletter.enviar'), ['artigo_id' => $artigo->id])
            ->assertRedirect();

        Mail::assertSent(NewsletterArtigo::class, 3);

        $this->assertDatabaseHas('newsletter_editions', [
            'article_id' => $artigo->id,
            'total' => 3,
            'enviados' => 3,
            'falhados' => 0,
            'estado' => 'done',
        ]);
        $this->assertDatabaseHas('newsletter_edition_items', ['email' => 'sub0@example.com', 'estado' => 'sent']);
    }

    public function test_lote_maximo_100_por_clique(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $artigo = $this->artigo();
        $this->criarAtivos(105);

        $this->actingAs($admin)
            ->post(route('admin.newsletter.enviar'), ['artigo_id' => $artigo->id])
            ->assertRedirect();

        Mail::assertSent(NewsletterArtigo::class, 100);
        $this->assertDatabaseHas('newsletter_editions', ['article_id' => $artigo->id, 'enviados' => 100, 'total' => 105, 'estado' => 'pending']);

        // segundo clique: envia os 5 restantes e fecha a edição
        $this->actingAs($admin)
            ->post(route('admin.newsletter.enviar'), ['artigo_id' => $artigo->id])
            ->assertRedirect();

        Mail::assertSent(NewsletterArtigo::class, 105);
        $this->assertDatabaseHas('newsletter_editions', ['article_id' => $artigo->id, 'enviados' => 105, 'estado' => 'done']);
    }

    public function test_modo_teste_nao_envia_para_subscritores(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $artigo = $this->artigo();
        $this->criarAtivos(2);

        $this->actingAs($admin)
            ->post(route('admin.newsletter.enviar'), ['artigo_id' => $artigo->id, 'teste' => '1'])
            ->assertRedirect();

        Mail::assertSent(NewsletterArtigo::class, 1);
        Mail::assertSent(NewsletterArtigo::class, fn ($mail) => $mail->hasTo($admin->email));
        $this->assertDatabaseEmpty('newsletter_editions');
    }

    public function test_pagina_mostra_artigos_e_subscritores(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.newsletter.enviar'))
            ->assertOk()
            ->assertSee('Aviso de novo artigo')
            ->assertSee('artigo_id');
    }
}
