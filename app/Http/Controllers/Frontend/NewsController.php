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

        $this->contarVista($request, $post);

        [$prev, $next] = $this->adjacentNews($post);

        return view('news.show', ['post' => $post, 'prev' => $prev, 'next' => $next]);
    }

    /**
     * FASE E2 (2026-11-13): conta a vista apenas se:
     *  1) o pedido NÃO for de um bot/crawler conhecido;
     *  2) a sessão não tiver registado ESTA notícia nos últimos 5 minutos.
     *
     * Os bots (Google, Bing, Facebook,…) inflam os números; a mesma sessão
     * em F5 também. As visitas reais por leitura contam uma vez.
     */
    private function contarVista(Request $request, \Illuminate\Database\Eloquent\Model $post): void
    {
        $agente = (string) $request->userAgent();

        if ($agente === '' || preg_match('/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|twitterbot|linkedinbot|whatsapp|telegram|discord|slackbot|applebot|pinterest|preview|embedly|monitor|validator/i', $agente)) {
            return;
        }

        $chave = 'vista_noticia_'.$post->getKey();
        $ultima = session($chave);

        if ($ultima !== null && (now()->timestamp - (int) $ultima) < 300) {
            return;
        }

        session([$chave => now()->timestamp]);

        $post->increment('views_count');
    }
}