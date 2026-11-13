<?php

namespace Tests\Feature;

use App\Livewire\Admin\NewsListTable;
use App\Models\News;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase 7 (cms_v.md) — lista de notícias (NewsListTable):
 * busca, filtros, ordenação, duplicar (rascunho + slug único),
 * apagar para o lixo, restaurar.
 */
class NewsListTableTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    public function test_duplicar_cria_rascunho_com_slug_unico(): void
    {
        $original = News::query()->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(NewsListTable::class)
            ->call('duplicar', $original->id);

        $copia = News::query()->where('title', $original->title . ' (cópia)')->first();

        $this->assertNotNull($copia, 'A cópia devia existir.');
        $this->assertSame('draft', $copia->status);
        $this->assertNotSame($original->slug, $copia->slug);

        // Slug realmente único na BD (inclui apagados).
        $this->assertSame(
            1,
            News::query()->withTrashed()->where('slug', $copia->slug)->count(),
        );
    }

    public function test_apagar_vai_para_o_lixo_e_restaurar_devolve(): void
    {
        $registo = News::query()->orderByDesc('id')->firstOrFail();
        $id = $registo->id;

        Livewire::actingAs($this->admin())
            ->test(NewsListTable::class)
            ->call('eliminar', $id);

        $this->assertSoftDeleted('news', ['id' => $id]);

        Livewire::actingAs($this->admin())
            ->test(NewsListTable::class)
            ->set('mostrarApagados', true)
            ->call('restaurar', $id);

        $this->assertDatabaseHas('news', ['id' => $id, 'deleted_at' => null]);
    }

    public function test_busca_por_titulo_restringe_resultados(): void
    {
        $noticia = News::query()->orderByDesc('id')->firstOrFail();
        $outra = News::query()->where('id', '!=', $noticia->id)->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(NewsListTable::class)
            ->set('busca', $noticia->title)
            ->assertSee($noticia->title)
            ->assertDontSee($outra->title);
    }
}
