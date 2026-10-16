<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesContent;
use App\Models\Category;
use App\Models\Project;

class ProjectController extends Controller
{
    use ResolvesContent;

    public function index()
    {
        $items = Project::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(9);

        $categories = Category::where('type', 'projects')->get();

        return view('projects.index', compact('items', 'categories'));
    }

    public function show(string $slug)
    {
        $post = $this->resolveContent($slug);

        if ($post === null) {
            abort(404);
        }

        $post->increment('views_count');

        [$prev, $next] = $this->adjacentNews($post);

        return view('projects.show', ['post' => $post, 'prev' => $prev, 'next' => $next]);
    }
}