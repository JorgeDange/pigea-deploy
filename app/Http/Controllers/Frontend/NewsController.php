<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesContent;

class NewsController extends Controller
{
    use ResolvesContent;

    public function index()
    {
        // Lista, categoria (`?cat=`) e ordenação vivem no componente
        // Livewire `NewsFilter`, que é quem consulta a BD.
        return view('news.index');
    }

    public function show(string $slug)
    {
        $post = $this->resolveContent($slug);

        if ($post === null) {
            abort(404);
        }

        $post->increment('views_count');

        [$prev, $next] = $this->adjacentNews($post);

        return view('news.show', ['post' => $post, 'prev' => $prev, 'next' => $next]);
    }
}