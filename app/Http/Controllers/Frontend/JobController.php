<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesContent;
use App\Models\Application;
use App\Models\Category;
use App\Models\Job;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    use ResolvesContent;

    public function index()
    {
        $items = Job::published()
            ->with(['category', 'author'])
            ->orderBy('id')
            ->paginate(8);

        $categories = Category::where('type', 'jobs')->get();

        return view('jobs.index', compact('items', 'categories'));
    }

    public function show(string $slug)
    {
        $post = $this->resolveContent($slug);

        if ($post === null) {
            abort(404);
        }

        $post->increment('views_count');

        [$prev, $next] = $this->adjacentNews($post);

        return view('jobs.show', ['post' => $post, 'prev' => $prev, 'next' => $next]);
    }

    /**
     * Formulário "Formulário de Candidatura" (`emprego-single.html`). Visitante
     * sem sessão candidata-se com nome+email; autenticado fica ligado à conta
     * (aparece nas "As minhas candidaturas" do painel).
     */
    public function candidatar(Request $request, string $slug): RedirectResponse
    {
        $post = $this->resolveContent($slug);

        if ($post === null) {
            abort(404);
        }

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'cv' => ['nullable', 'string', 'max:500'],
            'mensagem' => ['nullable', 'string', 'max:2000'],
        ]);

        Application::query()->create([
            'user_id' => $request->user()?->id,
            'job_id' => $post->id,
            'name' => $dados['nome'],
            'email' => $dados['email'],
            'cv_url' => $dados['cv'] ?? null,
            'message' => $dados['mensagem'] ?? null,
            'status' => 'em-analise',
        ]);

        if ($request->user() !== null) {
            Notification::query()->create([
                'user_id' => $request->user()->id,
                'icon' => 'fa-paper-plane',
                'title' => 'Candidatura enviada',
                'text' => 'a sua candidatura foi enviada com sucesso.',
            ]);
        }

        return redirect()->route('jobs.show', $slug);
    }
}