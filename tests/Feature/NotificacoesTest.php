<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Módulo de Notificações — opção B (doc/relatório do cliente, 2026-11-13).
 *
 * Garante que os eventos reais do site geram notificações na caixa de entrada
 * de toda a equipa (admin+editor) e que "Marcar todas lido" funciona.
 */
class NotificacoesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    private function criarEmpresaPendente(): Company
    {
        return Company::query()->create([
            'nome_empresa' => 'Empresa Teste S.A.',
            'nif' => '50' . random_int(10000000, 99999999),
            'contactos' => 'geral@empresa-teste.com',
            'areas_atuacao' => ['Mineração'],
            'status' => 'pendente',
        ]);
    }

    public function test_empresa_criada_gera_notificacao_para_a_equipa(): void
    {
        $this->criarEmpresaPendente();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => (new \App\Models\User())->getMorphClass(),
        ]);

        foreach (User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'editor']))->get() as $user) {
            $this->assertGreaterThanOrEqual(1, $user->unreadNotifications()->count());
        }
    }

    public function test_noticia_publicada_gera_notificacao(): void
    {
        $categoria = \App\Models\Category::where('type', 'news')->first();
        News::query()->create([
            'title' => 'Notícia de teste notificada',
            'slug' => 'noticia-teste-notificada-' . uniqid(),
            'content' => '<p>Conteúdo.</p>',
            'category_id' => $categoria?->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertGreaterThanOrEqual(1, $this->admin()->unreadNotifications()->count());
    }

    public function test_marcar_todas_como_lido(): void
    {
        $this->criarEmpresaPendente();
        $this->criarEmpresaPendente();

        $naoLidasAntes = $this->admin()->unreadNotifications()->count();
        $this->assertGreaterThan(0, $naoLidasAntes);

        $this->actingAs($this->admin())
            ->post(route('admin.notificacoes.ler'))
            ->assertRedirect();

        $this->assertSame(0, $this->admin()->unreadNotifications()->count());
        $this->assertSame($naoLidasAntes, $this->admin()->readNotifications()->count());
    }

    public function test_anonimo_nao_pode_marcar_lido(): void
    {
        $this->post(route('admin.notificacoes.ler'))
            ->assertRedirect();
    }
}
