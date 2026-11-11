<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Tests\TestCase;

/**
 * Fase 11 (cms_v.md) — painel de ligação genérico, imagens por URL e
 * categoria «Outros».
 */
class NoticiaEditorExtrasTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_link_generico_e_guardado(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com link genérico',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo da notícia.</p>',
                'categoria' => $categoria->id,
                'link' => 'https://www.exemplo.pt/artigo-completo',
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia com link genérico',
            'link' => 'https://www.exemplo.pt/artigo-completo',
        ]);
    }

    public function test_capa_via_url_grava_link_em_vez_de_ficheiro(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com capa por URL',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo da notícia.</p>',
                'categoria' => $categoria->id,
                'capa_fonte' => 'link',
                'capa_link' => 'https://www.exemplo.pt/imagem-da-capa.jpg',
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia com capa por URL',
            'image' => 'https://www.exemplo.pt/imagem-da-capa.jpg',
        ]);
    }

    public function test_capa_ficheiro_e_link_juntos_devolve_erro(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        $resposta = $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com capa final',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo da notícia.</p>',
                'categoria' => $categoria->id,
                'capa_fonte' => 'link',
                'capa' => \Illuminate\Http\UploadedFile::fake()->image('foto.jpg', 800, 600),
                'capa_link' => 'https://www.exemplo.pt/imagem.jpg',
            ]);

        $resposta->assertSessionHasErrors('capa');
        $this->assertDatabaseMissing('news', ['title' => 'Notícia com capa final']);
    }

    public function test_categoria_outros_e_selecionada_e_gravada(): void
    {
        $outros = Category::where('type', 'news')->where('slug', 'outros')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia da categoria Outros',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo da notícia.</p>',
                'categoria' => $outros->id,
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia da categoria Outros',
            'category_id' => $outros->id,
        ]);
    }
}
