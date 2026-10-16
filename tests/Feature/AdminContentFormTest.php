<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ContentFormController;
use App\Livewire\Admin\ContentTable;
use App\Models\Directory;
use App\Models\Event;
use App\Models\Interview;
use App\Models\Job;
use App\Models\Legislation;
use App\Models\News;
use App\Models\Project;
use App\Models\Research;
use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Formulários genéricos do painel admin (ContentFormController).
 *
 * Cobre as duas funções que estavam mortas no protótipo:
 *
 *  - os botões "Novo evento/estudo/entrevista/legislação" eram `type="submit"`
 *    sem `<form>` — não faziam nada;
 *  - o botão caneta ("Editar") era renderizado sem `wire:click` nem link —
 *    também não fazia nada, e o protótipo não tem página de edição.
 */
class AdminContentFormTest extends TestCase
{
    private function admin(): User
    {
        return User::where('email', 'test@example.com')->firstOrFail();
    }

    /** Tipo → modelo, para localizar um registo real daquele tipo. */
    public static function tipos(): array
    {
        return [
            'noticias' => ['noticias', News::class],
            'eventos' => ['eventos', Event::class],
            'investigacao' => ['investigacao', Research::class],
            'entrevistas' => ['entrevistas', Interview::class],
            'legislacao' => ['legislacao', Legislation::class],
            'projetos' => ['projetos', Project::class],
            'vagas' => ['vagas', Job::class],
            'empresas' => ['empresas', Directory::class],
        ];
    }

    /** Os 4 tipos cuja criação era um botão morto no protótipo. */
    public static function tiposCriaveis(): array
    {
        return [
            'eventos' => ['eventos'],
            'investigacao' => ['investigacao'],
            'entrevistas' => ['entrevistas'],
            'legislacao' => ['legislacao'],
        ];
    }

    #[DataProvider('tiposCriaveis')]
    public function test_pagina_de_criacao_abre_para_os_quatro_tipos(string $tipo): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.conteudo.novo', ['tipo' => $tipo]))
            ->assertOk()
            ->assertSee('name="titulo"', false);
    }

    #[DataProvider('tipos')]
    public function test_pagina_de_edicao_abre_para_todos_os_tipos(string $tipo, string $modelo): void
    {
        $id = $modelo::query()->firstOrFail()->id;

        $this->actingAs($this->admin())
            ->get(route('admin.conteudo.editar', ['tipo' => $tipo, 'id' => $id]))
            ->assertOk()
            ->assertSee('name="titulo"', false)
            // Em edição o formulário tem de ser PUT, não POST para a criação.
            ->assertSee('_method', false);
    }

    #[DataProvider('tiposCriaveis')]
    public function test_criar_registo_valido_guarda_na_bd(string $tipo): void
    {
        $titulo = 'Registo de teste '.strtoupper($tipo);

        $this->actingAs($this->admin())
            ->post(route('admin.conteudo.novo.store', ['tipo' => $tipo]), [
                'titulo' => $titulo,
                'estado' => 'publicar',
                'excerto' => 'Resumo de teste.',
                'conteudo' => 'Corpo de teste.',
            ])
            ->assertRedirect(route(ContentFormController::TIPOS[$tipo]['lista']));

        $this->assertDatabaseHas($this->tabelaDo($tipo), [
            'title' => $titulo,
            'status' => 'published',
            'author_id' => $this->admin()->id,
        ]);
    }

    public function test_criar_sem_titulo_devolve_erros(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.conteudo.novo.store', ['tipo' => 'eventos']), ['titulo' => ''])
            ->assertSessionHasErrors('titulo');

        $this->assertDatabaseMissing('events', ['title' => '']);
    }

    #[DataProvider('tipos')]
    public function test_editar_actualiza_os_campos(string $tipo, string $modelo): void
    {
        $registo = $modelo::query()->firstOrFail();
        $tituloAntigo = $registo->title;
        $excertoAntigo = $registo->excerpt;
        $conteudoAntigo = $registo->content;

        $this->actingAs($this->admin())
            ->put(route('admin.conteudo.atualizar', ['tipo' => $tipo, 'id' => $registo->id]), [
                'titulo' => $tituloAntigo.' (editado)',
                // Sem `slug`, `estado`, `excerto` nem `conteudo`: o slug tem de
                // ficar intacto (senão quebrava a URL publicada), o estado como
                // estava, e um POST incompleto não pode apagar conteúdo.
            ])
            ->assertRedirect();

        $atualizado = $registo->fresh();

        $this->assertDatabaseHas($registo->getTable(), [
            'id' => $registo->id,
            'title' => $tituloAntigo.' (editado)',
            'slug' => $registo->slug,
        ]);

        $this->assertSame($excertoAntigo, $atualizado->excerpt, 'POST parcial apagou o excerto.');
        $this->assertSame($conteudoAntigo, $atualizado->content, 'POST parcial apagou o conteúdo.');
        $this->assertSame($registo->status, $atualizado->status, 'O estado devia ficar como estava.');
    }

    public function test_editar_pode_mudar_o_estado(): void
    {
        $evento = Event::query()->where('status', 'published')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.conteudo.atualizar', ['tipo' => 'eventos', 'id' => $evento->id]), [
                'titulo' => $evento->title,
                'estado' => 'rascunho',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('events', ['id' => $evento->id, 'status' => 'draft']);
    }

    public function test_tipo_desconhecido_devolve_404(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.conteudo.novo', ['tipo' => 'coisa-qualquer']))
            ->assertNotFound();

        // Tipo editável mas não criável: a criação tem de continuar a dar 404,
        // senão "criar notícia" fugia do NewsFormController validado.
        $this->actingAs($this->admin())
            ->get(route('admin.conteudo.novo', ['tipo' => 'noticias']))
            ->assertNotFound();
    }

    public function test_permissao_granular_e_exigida(): void
    {
        $admin = $this->admin();
        $evento = Event::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.conteudo.editar', ['tipo' => 'eventos', 'id' => $evento->id]))
            ->assertOk();

        // Revogar só a permissão (mantendo a role) fecha a porta — mesmo
        // contrato do GranularAuthTest: o `role:` abre, o Gate decide.
        $admin->roles->first()->revokePermissionTo('event.manage');

        $this->actingAs($admin)
            ->get(route('admin.conteudo.editar', ['tipo' => 'eventos', 'id' => $evento->id]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.conteudo.atualizar', ['tipo' => 'eventos', 'id' => $evento->id]), ['titulo' => 'X'])
            ->assertForbidden();
    }

    public function test_botao_caneta_da_tabela_abre_a_edicao(): void
    {
        $noticia = News::query()->firstOrFail();

        Livewire::actingAs($this->admin())
            ->test(ContentTable::class, ['tipo' => 'noticias'])
            ->call('editar', $noticia->id)
            ->assertRedirect(route('admin.conteudo.editar', ['tipo' => 'noticias', 'id' => $noticia->id]));
    }

    public function test_botoes_novo_estao_ligados_nas_quatro_paginas(): void
    {
        foreach (array_keys(self::tiposCriaveis()) as $tipo) {
            $this->actingAs($this->admin())
                ->get(route(ContentFormController::TIPOS[$tipo]['lista']))
                ->assertOk()
                ->assertSee(route('admin.conteudo.novo', ['tipo' => $tipo]), false);
        }
    }

    /** Tabela de um tipo, para as asserções de BD. */
    private function tabelaDo(string $tipo): string
    {
        return self::tipos()[$tipo][1]::query()->getModel()->getTable();
    }
}
