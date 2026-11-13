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

    public function test_provincia_e_gravada_no_editor(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com província Huíla',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'provincia' => 'huila',
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia com província Huíla',
            'province' => 'huila',
        ]);
    }

    public function test_provincia_texto_livre_e_aceite_para_noticias_internacionais(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        // FASE E1 — 2026-11-13: o texto livre é o comportamento pedido (Holanda,
        // Turquia…); já não rejeitamos «províncias desconhecidas».
        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia internacional texto livre',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'provincia' => 'outros',
                'provincia_outro' => 'Holanda',
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia internacional texto livre',
            'province' => 'Holanda',
        ]);
    }

    public function test_provincia_outros_sem_texto_e_rejeitada(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia outros sem texto',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'provincia' => 'outros',
            ])
            ->assertSessionHasErrors('provincia_outro');

        $this->assertDatabaseMissing('news', ['title' => 'Notícia outros sem texto']);
    }

    public function test_provincia_outros_com_texto_longo_demais_e_rejeitada(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia outros texto longo',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'provincia' => 'outros',
                'provincia_outro' => str_repeat('a', 101),
            ])
            ->assertSessionHasErrors('provincia_outro');

        $this->assertDatabaseMissing('news', ['title' => 'Notícia outros texto longo']);
    }

    public function test_provincia_outro_e_ignorado_fora_de_outros(): void
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        // Aprovado pelo cliente (E1.1): fora de «outros», o campo auxiliar não
        // é guardado (o lixo do formulário não vai parar à coluna).
        $this->actingAs($this->admin())
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com campo auxiliar ignorado',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'provincia' => 'huila',
                'provincia_outro' => 'Holanda',
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia com campo auxiliar ignorado',
            'province' => 'huila',
        ]);
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
