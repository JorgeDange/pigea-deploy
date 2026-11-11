<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Fase 4 (cms_v.md) — upload de imagens para o corpo do artigo.
 *
 * POST /gestao-pigea/noticias/imagem (autenticado, can:news.manage,
 * throttle 20/min). Devolve JSON { location }. JPG/PNG/WebP ≤ 4MB;
 * o ficheiro é redimensionado (máx. 1200px) e convertido para WebP.
 */
class NoticiaImagemUploadTest extends TestCase
{
    private function editor(): User
    {
        return User::factory()->create();
    }

    public function test_convidado_nao_pode_fazer_upload(): void
    {
        $this->post(route('admin.noticia-imagem.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_formato_nao_suportado_devolve_422(): void
    {
        Storage::fake('public');

        $this->actingAs(User::where('email', 'test@example.com')->firstOrFail())
            ->post(route('admin.noticia-imagem.store'), [
                'imagem' => UploadedFile::fake()->create('malicioso.gif', 128, 'image/gif'),
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Formato não suportado. Usa JPG, PNG ou WebP.');
    }

    public function test_imagem_demasiado_grande_devolve_422(): void
    {
        Storage::fake('public');

        $this->actingAs(User::where('email', 'test@example.com')->firstOrFail())
            ->post(route('admin.noticia-imagem.store'), [
                'imagem' => UploadedFile::fake()->create('grande.png', 5120, 'image/png'), // 5 MB
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'A imagem excede o máximo de 4MB.');
    }

    public function test_upload_valido_converte_para_webp_e_devolve_url(): void
    {
        Storage::fake('public');

        $resposta = $this->actingAs(User::where('email', 'test@example.com')->firstOrFail())
            ->post(route('admin.noticia-imagem.store'), [
                'imagem' => UploadedFile::fake()->image('foto.jpg', 800, 600),
            ]);

        $resposta->assertOk();
        $this->assertNotEmpty($resposta->json('location'));
        $this->assertStringContainsString('/storage/noticias/corpo/', $resposta->json('location'));
        $this->assertStringEndsWith('.webp', $resposta->json('location'));
    }
}
