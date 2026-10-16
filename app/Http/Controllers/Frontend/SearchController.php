<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Todos os módulos de conteúdo público do protótipo (tabelas com o mesmo
     * esquema), pesquisados de uma só vez com o driver `database` do Scout.
     */
    private const MODELOS = [
        News::class,
        Project::class,
        Directory::class,
        Technology::class,
        Supplier::class,
        Event::class,
        Interview::class,
        Research::class,
        Job::class,
        Legislation::class,
        Training::class,
    ];

    /**
     * Página de resultados — o protótipo não tem uma (o campo `se` do topo e
     * das laterais apontava para `#`), por isso a lista reaproveita o bloco
     * `list-post` das listagens.
     */
    public function index(Request $request)
    {
        $termo = trim((string) $request->query('se', ''));
        $resultados = [];

        if ($termo !== '') {
            foreach (self::MODELOS as $modelo) {
                $encontrados = $modelo::search($termo, fn ($query) => $query->published()->whereNull('deleted_at'))
                    ->get();

                foreach ($encontrados as $item) {
                    $resultados[] = $item;
                }
            }
        }

        usort($resultados, fn (Model $a, Model $b) => $this->comparar($a, $b, $termo));

        return view('search.index', [
            'termo' => $termo,
            'resultados' => $resultados,
            'total' => count($resultados),
        ]);
    }

    /** Ocorrência no título pesa mais que no resumo; empates ficam por data. */
    private function comparar(Model $a, Model $b, string $termo): int
    {
        $diferenca = $this->relevancia($b, $termo) <=> $this->relevancia($a, $termo);

        return $diferenca !== 0 ? $diferenca : $b->published_at <=> $a->published_at;
    }

    private function relevancia(Model $item, string $termo): int
    {
        $procurado = mb_strtolower($termo);
        $titulo = mb_strtolower((string) $item->title);
        $resumo = mb_strtolower((string) $item->excerpt);

        return match (true) {
            $titulo === $procurado => 4,
            str_starts_with($titulo, $procurado) => 3,
            str_contains($titulo, $procurado) => 2,
            str_contains($resumo, $procurado) => 1,
            default => 0,
        };
    }
}
