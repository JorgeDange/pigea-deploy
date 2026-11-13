<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use Tests\TestCase;

/**
 * Módulo Vídeos (admin) — postar vídeos por link (YouTube/Vimeo).
 */
class VideosTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_criar_video_valido_guarda_na_base_de_dados(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.videos.novo.store'), [
                'titulo' => 'Vídeo de teste',
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'resumo' => 'Resumo de teste',
            ])
            ->assertRedirect(route('admin.videos'));

        $this->assertDatabaseHas('videos', [
            'titulo' => 'Vídeo de teste',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
    }

    public function test_url_invalida_devolve_erro(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.videos.novo.store'), [
                'titulo' => 'Link inválido',
                'url' => 'não-é-um-url',
            ])
            ->assertSessionHasErrors('url');

        $this->assertDatabaseMissing('videos', ['titulo' => 'Link inválido']);
    }

    public function test_video_aparece_na_home(): void
    {
        Video::create([
            'titulo' => 'Vídeo visível na home',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'resumo' => 'Aparece na secção de vídeo',
            'published_at' => now(),
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $this->assertStringContainsString('Vídeo visível na home', $html);
    }

    public function test_lista_admin_requer_video_manage(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.videos'))
            ->assertOk();
    }
}
