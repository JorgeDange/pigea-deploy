<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Tests\TestCase;

/**
 * Fase 8.1 (cms_v.md) — autosave do editor.
 *
 * POST /gestao-pigea/noticias/autosave — sanitiza o corpo, NUNCA publica
 * (status fica como estava), só funciona em registos existentes.
 */
class NoticiaAutosaveTest extends TestCase
{
    public function test_autosave_grava_sem_publicar(): void
    {
        $admin = User::where('email', 'test@example.com')->firstOrFail();
        $noticia = News::query()->where('status', 'published')->firstOrFail();
        $tituloOriginal = $noticia->title;

        $resposta = $this->actingAs($admin)
            ->postJson(route('admin.noticia-autosave'), [
                'id' => $noticia->id,
                'titulo' => $tituloOriginal . ' (autosave)',
                'conteudo' => '<p>Update autosave.</p><script>alert(1)</script>',
            ]);

        $resposta->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('id', $noticia->id);

        $this->assertDatabaseHas('news', [
            'id' => $noticia->id,
            'title' => $tituloOriginal . ' (autosave)',
            'status' => 'published', // continua publicado (autosave NUNCA publica)
        ]);

        // Sanitizado no guardar
        $atualizada = News::findOrFail($noticia->id);
        $this->assertStringNotContainsString('<script', $atualizada->content ?? '');
        $this->assertStringContainsString('Update autosave.', $atualizada->content ?? '');
    }

    public function test_autosave_recusa_sem_autenticacao(): void
    {
        $noticia = News::query()->firstOrFail();

        $this->postJson(route('admin.noticia-autosave'), [
            'id' => $noticia->id,
            'titulo' => 'x',
        ])->assertStatus(401, 403);
    }

    public function test_autosave_id_invalido_devolve_422(): void
    {
        $admin = User::where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->postJson(route('admin.noticia-autosave'), [
                'id' => 99999999,
                'titulo' => 'x',
            ])->assertStatus(422);
    }
}
