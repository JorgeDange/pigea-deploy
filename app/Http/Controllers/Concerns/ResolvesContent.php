<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Directory;
use App\Models\Event;
use App\Models\Interview;
use App\Models\Job;
use App\Models\Legislation;
use App\Models\News;
use App\Models\Project;
use App\Models\Research;
use App\Models\Supplier;
use App\Models\Technology;
use App\Models\Training;
use Illuminate\Database\Eloquent\Model;

trait ResolvesContent
{
    /**
     * Todas as fichas de listagem do protótipo apontam para a mesma página de
     * artigo, por isso as rotas de detalhe têm de resolver slugs vindos de
     * qualquer módulo de conteúdo.
     */
    protected function resolveContent(string $slug): ?Model
    {
        $models = [
            News::class,
            Project::class,
            Job::class,
            Supplier::class,
            Event::class,
            Legislation::class,
            Research::class,
            Interview::class,
            Technology::class,
            Directory::class,
            Training::class,
        ];

        foreach ($models as $model) {
            $row = $model::published()->where('slug', $slug)->first();

            if ($row !== null) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Artigo anterior/seguinte da mesma tabela (ordem de id), para a navegação
     * "Prev Post"/"Next Post" do protótipo.
     *
     * @return array{0: ?Model, 1: ?Model}
     */
    protected function adjacentNews(?Model $post): array
    {
        if ($post === null || ! method_exists($post, 'scopePublished')) {
            return [null, null];
        }

        $model = $post::class;

        $prev = $model::published()
            ->where('id', '<', $post->id)
            ->orderByDesc('id')
            ->first();

        $next = $model::published()
            ->where('id', '>', $post->id)
            ->orderBy('id')
            ->first();

        return [$prev, $next];
    }
}
