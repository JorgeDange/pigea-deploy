<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesContent;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    use ResolvesContent;

    public function index()
    {
        // Lista, categoria (`?cat=`) e ordenação vivem no componente
        // Livewire `NewsFilter`, que é quem consulta a BD.
        return view('news.index');
    }

    public function show(Request $request, string $slug)
    {
        $post = $this->resolveContent($slug);

        if ($post === null) {
            abort(404);
        }

        // FASE E2: conta apenas leituras reais (sem bots; sessão 1× por 5 min).
        \App\Support\ContadorDeVisitas::contar($request, $post, 'vista_noticia');

        [$prev, $next] = $this->adjacentNews($post);

        return view('news.show', ['post' => $post, 'prev' => $prev, 'next' => $next]);
    }
}
