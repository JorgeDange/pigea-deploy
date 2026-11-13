<?php

namespace Tests\Feature;

use App\Mail\NewsletterConfirmation;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Newsletter (doc/newsletter.md — FASE N1):
 * subscrição pública com dupla opt-in (só email) + gestão no painel.
 */
class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    private function editor(): User
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');

        return $editor;
    }

    // ------------------------------------------------------------------
    // Subscrição pública + dupla opt-in
    // ------------------------------------------------------------------

    public function test_subscricao_cria_pending_e_envia_email_de_confirmacao(): void
    {
        Mail::fake();

        $resposta = $this->post(route('newsletter.subscribe'), [
            'email' => 'joao@example.com',
        ]);

        $resposta->assertRedirect();
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'joao@example.com',
            'status' => 'pending',
            'confirmed_at' => null,
        ]);

        Mail::assertSent(NewsletterConfirmation::class, fn ($mail) => $mail->hasTo('joao@example.com'));
    }

    public function test_subscricao_ajax_devolve_json(): void
    {
        Mail::fake();

        $resposta = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'ajax@example.com',
        ]);

        $resposta->assertOk()->assertJson(['ok' => true]);
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'ajax@example.com']);

        // Com email inválido → 422 em JSON (erros por campo)
        $resposta = $this->postJson(route('newsletter.subscribe'), ['email' => 'nao-e-email']);
        $resposta->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_confirmar_token_marca_confirmed(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'maria@example.com']);
        $sub = NewsletterSubscriber::where('email', 'maria@example.com')->firstOrFail();

        $resposta = $this->get(route('newsletter.confirmar', $sub->token));

        $resposta->assertOk();
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'maria@example.com',
            'status' => 'confirmed',
        ]);
        $this->assertNotNull(NewsletterSubscriber::where('email', 'maria@example.com')->value('confirmed_at'));
    }

    public function test_confirmar_token_invalido_mostra_pagina_de_erro(): void
    {
        $resposta = $this->get(route('newsletter.confirmar', str_repeat('x', 64)));

        $resposta->assertOk();
        $this->assertStringContainsString('Ligação inválida', $resposta->getContent());
    }

    public function test_cancelar_marca_unsubscribed(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'rui@example.com']);
        $sub = NewsletterSubscriber::where('email', 'rui@example.com')->firstOrFail();

        $resposta = $this->get(route('newsletter.cancelar', $sub->token));

        $resposta->assertOk();
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'rui@example.com',
            'status' => 'unsubscribed',
        ]);
    }

    public function test_email_ja_confirmed_nao_duplica_e_nao_envia(): void
    {
        NewsletterSubscriber::create([
            'email' => 'ja@example.com',
            'token' => str_repeat('a', 64),
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'ja@example.com']);

        $this->assertSame(1, NewsletterSubscriber::where('email', 'ja@example.com')->count());
        Mail::assertNothingSent();
    }

    public function test_pending_reenvia_com_novo_token(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'pendente@example.com']);
        $tokenAntigo = NewsletterSubscriber::where('email', 'pendente@example.com')->value('token');

        $this->post(route('newsletter.subscribe'), ['email' => 'pendente@example.com']);

        $sub = NewsletterSubscriber::where('email', 'pendente@example.com')->firstOrFail();
        $this->assertNotSame($tokenAntigo, $sub->token);
        Mail::assertSent(NewsletterConfirmation::class, 2);
    }

    public function test_subscricao_valida_email(): void
    {
        Mail::fake();

        $this->post(route('newsletter.subscribe'), ['email' => 'nao-e-email'])
            ->assertSessionHasErrors(['email']);
        $this->assertSame(0, NewsletterSubscriber::count());
    }

    // ------------------------------------------------------------------
    // Painel admin
    // ------------------------------------------------------------------

    public function test_admin_ve_a_pagina_e_o_subscritor(): void
    {
        NewsletterSubscriber::create([
            'email' => 've@example.com',
            'token' => str_repeat('b', 64),
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $resposta = $this->actingAs($this->admin())->get(route('admin.newsletter'));

        $resposta->assertOk();
        $resposta->assertSee('ve@example.com');
        $resposta->assertSee('Confirmados');
    }

    public function test_admin_adicionar_manualmente_fica_confirmed(): void
    {
        $resposta = $this->actingAs($this->admin())->post(route('admin.newsletter.adicionar'), [
            'email' => 'adicionado@example.com',
        ]);

        $resposta->assertRedirect();
        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'adicionado@example.com',
            'status' => 'confirmed',
        ]);
    }

    public function test_admin_remover(): void
    {
        $sub = NewsletterSubscriber::create([
            'email' => 'apagar@example.com',
            'token' => str_repeat('c', 64),
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $this->actingAs($this->admin())->delete(route('admin.newsletter.remover', $sub->id))->assertRedirect();

        $this->assertDatabaseMissing('newsletter_subscribers', ['email' => 'apagar@example.com']);
    }

    public function test_exportar_csv_so_confirmados(): void
    {
        NewsletterSubscriber::create([
            'email' => 'csv-sim@example.com',
            'token' => str_repeat('d', 64),
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
        NewsletterSubscriber::create([
            'email' => 'csv-nao@example.com',
            'token' => str_repeat('e', 64),
            'status' => 'pending',
        ]);

        $resposta = $this->actingAs($this->admin())->get(route('admin.newsletter.exportar'));

        $resposta->assertOk();
        $this->assertStringContainsString('text/csv', (string) $resposta->headers->get('Content-Type'));

        $conteudo = $resposta->streamedContent();
        $this->assertStringContainsString('csv-sim@example.com', $conteudo);
        $this->assertStringNotContainsString('csv-nao@example.com', $conteudo);
    }

    public function test_anonimo_e_editor_nao_acedem_ao_painel(): void
    {
        $this->get(route('admin.newsletter'))->assertRedirect();
        $this->actingAs($this->editor())->get(route('admin.newsletter'))->assertForbidden();
    }
}
