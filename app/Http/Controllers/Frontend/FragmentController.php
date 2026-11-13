<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\View\View;

/**
 * Fragmentos AJAX dos separadores da home (editoria_cms.md FASE 1).
 *
 * As 4 abas correspondem às 4 categorias de topo de notícias:
 *   Mineração (slug `mineracao`)  → cat + subcategorias via parent_id;
 *   Água (slug `agua`)            → cat + subcategorias via parent_id;
 *   Ambiente (slug `ambiente`)    → cat + subcategorias via parent_id;
 *   Investigação (slug `investigacao`) → cat + subcategorias via parent_id;
 *
 * Selecção dinamica: resolve a categoria pai pelo slug e inclui ela + todos
 * os seus filhos (via parent_id). Sem hardcode de slugs de notícias.
 *
 * Nota sobre `categories.menu_group`: esse campo serve as 44 subcategorias
 * do Mega Menu (invariante do MegaMenuTest). As 4 secções AJAX correspondem
 * a categorias-pai de `type='news'` criadas pelo ContentSeeder com
 * menu_group=NULL. Para não colidir com a invariante do MegaMenuTest
 * (44 rows), filtramos por parent_id + slug em vez de menu_group.
 */
class FragmentController extends Controller
{
    private const GRUPOS = [
        'noticias-mineracao.html' => ['rotulo' => 'Mineração', 'slug' => 'mineracao'],
        'noticias-agua.html' => ['rotulo' => 'Água', 'slug' => 'agua'],
        'noticias-ambiente.html' => ['rotulo' => 'Ambiente', 'slug' => 'ambiente'],
        'noticias-investigacao.html' => ['rotulo' => 'Investigação', 'slug' => 'investigacao'],
    ];

    public function show(string $arquivo): View
    {
        $cfg = self::GRUPOS[$arquivo] ?? null;

        if ($cfg === null) {
            abort(404);
        }

        $categoria = Category::where('slug', $cfg['slug'])
            ->where('type', 'news')
            ->first();

        if ($categoria === null) {
            abort(404);
        }

        $ids = Category::where('parent_id', $categoria->id)
            ->pluck('id')
            ->push($categoria->id);

        $items = News::published()
            ->with(['category', 'author'])
            ->whereIn('category_id', $ids)
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('fragments.news', [
            'items' => $items,
            'categoria' => $cfg['rotulo'],
        ]);
    }
}
