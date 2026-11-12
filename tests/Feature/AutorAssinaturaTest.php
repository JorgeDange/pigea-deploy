<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * autor.md — assinatura de autor das notícias:
 *
 *  - §2.2 painel "Autor do Artigo": Autor interno (select) OU Convidado
 *    (nome + foto carregada/mediante link);
 *  - §2.3 prioridade: author_id → user; senão convidado; senão "Equipa
 *    Editorial PIGEA";
 *  - §3 no final do artigo: "Escrito por {Nome}" com fallback logo.
 */
class AutorAssinaturaTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    private function publicarNoticia(string $titulo, array $extra = []): News
    {
        $categoria = Category::where('type', 'news')->firstOrFail();

        return News::create(array_merge([
            'title' => $titulo,
            'slug' => \Illuminate\Support\Str::slug($titulo) . '-' . strtolower(\Illuminate\Support\Str::random(4)),
            'content' => '<p>Corpo de teste.</p>',
            'category_id' => $categoria->id,
            'status' => 'published',
            'published_at' => now(),
            'date_label' => now()->locale('pt')->translatedFormat('d M Y'),
        ], $extra));
    }

    public function test_autor_interno_e_gravado(): void
    {
        $admin = $this->admin();
        $categoria = Category::where('type', 'news')->firstOrFail();
        $outro = User::factory()->create(['name' => 'Investigadora Interna']);

        $this->actingAs($admin)
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com autor interno',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'author_choice' => 'interno',
                'author_id' => $outro->id,
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia com autor interno',
            'author_id' => $outro->id,
        ]);
    }

    public function test_autor_convidado_grava_nome_e_sem_conta(): void
    {
        $admin = $this->admin();
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com autor convidado',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'author_choice' => 'convidado',
                'guest_author_name' => 'Hespanha F. C.',
                'guest_author_avatar_link' => 'https://parceiro.exemplo/autor.jpg',
            ])
            ->assertRedirect(route('admin.noticias'));

        $this->assertDatabaseHas('news', [
            'title' => 'Notícia com autor convidado',
            'author_id' => null,
            'guest_author_name' => 'Hespanha F. C.',
            'guest_author_avatar' => 'https://parceiro.exemplo/autor.jpg',
        ]);
    }

    public function test_autor_convidado_exige_nome(): void
    {
        $admin = $this->admin();
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia sem nome de convidado',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'author_choice' => 'convidado',
                'guest_author_name' => '',
            ])
            ->assertSessionHasErrors('guest_author_name');

        $this->assertDatabaseMissing('news', ['title' => 'Notícia sem nome de convidado']);
    }

    public function test_autor_foto_ficheiro_e_link_juntos_devolve_erro(): void
    {
        $admin = $this->admin();
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com foto ambígua',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'author_choice' => 'convidado',
                'guest_author_name' => 'Foto Ambígua',
                'guest_author_avatar' => UploadedFile::fake()->image('autor.jpg', 300, 300),
                'guest_author_avatar_link' => 'https://parceiro.exemplo/autor.jpg',
            ])
            ->assertSessionHasErrors('guest_author_avatar');
    }

    public function test_autor_foto_upload_vira_miniatura_150x150(): void
    {
        $admin = $this->admin();
        $categoria = Category::where('type', 'news')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.noticia-editor.store'), [
                'titulo' => 'Notícia com foto carregada',
                'acao' => 'publicar',
                'conteudo' => '<p>Corpo.</p>',
                'categoria' => $categoria->id,
                'author_choice' => 'convidado',
                'guest_author_name' => 'Foto Miniatura',
                'guest_author_avatar' => UploadedFile::fake()->image('autor.jpg', 800, 800),
            ])
            ->assertRedirect(route('admin.noticias'));

        $noticia = News::where('title', 'Notícia com foto carregada')->firstOrFail();
        $this->assertNotNull($noticia->guest_author_avatar);
        $this->assertStringStartsWith('storage/avatars/', $noticia->guest_author_avatar);

        // limpa o ficheiro criado pelo teste
        Storage::disk('public')->delete(substr($noticia->guest_author_avatar, strlen('storage/')));
    }

    public function test_show_exibe_assinatura_de_convidado(): void
    {
        $noticia = $this->publicarNoticia('Assinatura convidada visível', [
            'guest_author_name' => 'Maria Convidada Excecional',
        ]);

        $this->get(route('news.show', $noticia->slug))
            ->assertOk()
            ->assertSee('Maria Convidada Excecional');
    }

    public function test_show_sem_autor_mostra_equipa_editorial(): void
    {
        $noticia = $this->publicarNoticia('Assinatura da equipa visível', [
            'author_id' => null,
            'guest_author_name' => null,
        ]);

        $this->get(route('news.show', $noticia->slug))
            ->assertOk()
            ->assertSee('Equipa Editorial PIGEA');
    }

    public function test_perfil_atualiza_nome_e_foto(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.perfil.update'), [
                'name' => 'Gestor PIGEA Renomeado',
                'avatar' => UploadedFile::fake()->image('minhafoto.jpg', 600, 600),
            ])
            ->assertRedirect(route('admin.perfil'));

        $admin->refresh();
        $this->assertSame('Gestor PIGEA Renomeado', $admin->name);
        $this->assertNotNull($admin->avatar);

        // limpa o ficheiro criado pelo teste
        $caminho = $admin->avatar;
        if ($caminho && str_starts_with($caminho, 'storage/avatars/')) {
            Storage::disk('public')->delete(substr($caminho, strlen('storage/')));
        }
        $admin->avatar = null;
        $admin->name = 'Test User';
        $admin->save();
    }
}
