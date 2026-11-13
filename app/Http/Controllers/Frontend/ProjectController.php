<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesContent;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ResolvesContent;

    public function index()
    {
        if (! config('features.projects_publico')) {
            abort(404);
        }

        $items = Project::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(9);

        $categories = Category::where('type', 'projects')->get();

        return view('projects.index', compact('items', 'categories'));
    }

    public function show(Request $request, string $slug)
    {
        if (! config('features.projects_publico')) {
            abort(404);
        }

        $post = $this->resolveContent($slug);

        if ($post === null) {
            abort(404);
        }

        // FASE E2 (anti-bot): visitas a projetos contam como leitura real
        // (sem bots; sessão 1× por 5 minutos), tal como nas notícias.
        \App\Support\ContadorDeVisitas::contar($request, $post, 'vista_projeto');

        [$prev, $next] = $this->adjacentNews($post);

        return view('projects.show', ['post' => $post, 'prev' => $prev, 'next' => $next]);
    }
}