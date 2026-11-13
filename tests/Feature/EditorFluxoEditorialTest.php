<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Fase 6 (cms_v.md) — fluxo editorial por papel.
 *
 * Editor submete para revisão; admin publica/devolve/despublica.
 * Interruptor central: config('cms.editor_pode_publicar').
 */
class EditorFluxoEditorialTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    private function editor(): User
    {
        $u = User::factory()->create();
        $u->assignRole('editor');

        return $u;
    }

    public function test_editor_submete_para_revisao_quando_config_impede_publicar(): void
    {
        Config::set('cms.editor_pode_publicar', false);

        $editor = $this->editor();

        $resposta = $this->actingAs($editor)->post(route('admin.noticia-editor.store'), [
            'titulo' => 'Notícia do editor para revisão',
            'acao' => 'publicar',
            'conteudo' => '<p>Corpo do editor.</p>',
            'categoria' => \App\Models\Category::where('type', 'news')->value('id'),
        ]);

        // Sem permissão para publicar: devolvido com erro (não cria notícia publicada).
        $resposta->assertRedirect();
        $this->assertNull(News::where('title', 'Notícia do editor para revisão')->where('status', 'published')->first());
    }

    public function test_editor_com_config_permissa_publica_diretamente(): void
    {
        Config::set('cms.editor_pode_publicar', true);

        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.noticia-editor.store'), [
            'titulo' => 'Notícia do editor publicada',
            'acao' => 'publicar',
            'conteudo' => '<p>Corpo do editor.</p>',
            'categoria' => \App\Models\Category::where('type', 'news')->value('id'),
        ])->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', ['title' => 'Notícia do editor publicada', 'status' => 'published']);
    }

    public function test_submeter_para_revisao_grava_em_revisao(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.noticia-editor.store'), [
            'titulo' => 'Notícia submetida para revisão',
            'acao' => 'submeter_revisao',
            'conteudo' => '<p>Corpo.</p>',
        ])->assertRedirect(route('admin.noticia-editar', ['noticia' => News::where('title', 'Notícia submetida para revisão')->firstOrFail()->id]));

        $this->assertDatabaseHas('news', ['title' => 'Notícia submetida para revisão', 'status' => 'em_revisao', 'published_at' => null]);
    }

    public function test_admin_despublica_noticia_publicada(): void
    {
        $publicada = News::query()->where('status', 'published')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.noticia-editor.update', ['noticia' => $publicada->id]), [
                'titulo' => $publicada->title,
                'acao' => 'despublicar',
                'conteudo' => $publicada->content,
            ])
            ->assertRedirect(route('admin.noticia-editar', ['noticia' => $publicada->id]));

        $this->assertDatabaseHas('news', ['id' => $publicada->id, 'status' => 'draft']);
    }

    public function test_editor_nao_pode_despublicar_apenas_admin(): void
    {
        $publicada = News::query()->where('status', 'published')->firstOrFail();
        $editor = $this->editor();

        $this->actingAs($editor)
            ->put(route('admin.noticia-editor.update', ['noticia' => $publicada->id]), [
                'titulo' => $publicada->title,
                'acao' => 'despublicar',
                'conteudo' => $publicada->content,
            ])
            ->assertRedirect();

        $this->assertSame('published', News::findOrFail($publicada->id)->status, 'Editor não podia despublicar.');
    }

    public function test_slug_unico_check_devolve_unico(): void
    {
        $admin = $this->admin();
        $existente = News::query()->firstOrFail();

        $resposta = $this->actingAs($admin)
            ->getJson(route('admin.noticia-slug-unico', ['slug' => $existente->slug]));

        $this->assertFalse((bool) $resposta->json('unico'));

        $resposta = $this->actingAs($admin)
            ->getJson(route('admin.noticia-slug-unico', ['slug' => 'slug-definitivo-nao-existe-98765']));

        $this->assertTrue((bool) $resposta->json('unico'));
    }
}
