<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\View\View;

class FragmentController extends Controller
{
    /**
     * Os 4 separadores da home carregam estes fragmentos por AJAX
     * (`scripts.js` lê `this.href` e injeta o HTML em `#ajax-content`).
     *
     * A lista de slugs é editorial — é a mesma ordem dos ficheiros
     * `ajax/noticias-*.html` do protótipo; os dados de cada ficha
     * (imagem, data, excerpt, autor, contadores) vêm da BD.
     */
    private const FRAGMENTOS = [
        'noticias-mineracao.html' => [
            'categoria' => 'Mineração',
            'slugs' => [
                'novo-mapa-geologico-digital-de-angola-disponivel-para-consulta',
                'concurso-publico-para-exploracao-de-cobre-no-cuando-cubango',
                'descoberta-de-novo-indicio-de-manganes-em-malanje',
                'catoca-norte-amplia-a-capacidade-de-producao',
                'producao-de-ferro-em-cassinga-retoma-operacoes',
            ],
        ],
        'noticias-agua.html' => [
            'categoria' => 'Água',
            'slugs' => [
                'estudo-hidrogeologico-do-bie-conclui-a-primeira-fase',
                'programa-de-monitorizacao-das-aguas-subterraneas-arranca',
                'sistema-de-alerta-de-cheias-alarga-a-cobertura-no-cuanza',
            ],
        ],
        'noticias-ambiente.html' => [
            'categoria' => 'Ambiente',
            'slugs' => [
                'avaliacao-de-impacto-ambiental-concluida-em-projecto-mineiro',
                'reabilitacao-de-areas-degradadas-avanca-no-namibe',
                'novas-normas-de-emissoes-para-operacoes-mineiras',
                'campanha-de-reflorestamento-atinge-10-000-arvores',
            ],
        ],
        'noticias-investigacao.html' => [
            'categoria' => 'Investigação',
            'slugs' => [
                'hidrogeologia-do-planalto-do-bie-primeiros-resultados',
                'potencial-mineral-da-provincia-do-namibe-avaliado',
                'mapeamento-geologico-a-escala-1-250-000-concluido',
                'mudancas-climaticas-e-recursos-hidricos-em-angola',
            ],
        ],
    ];

    public function show(string $arquivo): View
    {
        if (! isset(self::FRAGMENTOS[$arquivo])) {
            abort(404);
        }

        $fragmento = self::FRAGMENTOS[$arquivo];

        $porSlug = News::published()
            ->with(['category', 'author'])
            ->whereIn('slug', $fragmento['slugs'])
            ->get()
            ->keyBy('slug');

        $items = collect($fragmento['slugs'])
            ->map(fn (string $slug) => $porSlug->get($slug))
            ->filter()
            ->values();

        return view('fragments.news', [
            'items' => $items,
            'categoria' => $fragmento['categoria'],
        ]);
    }
}
