<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * CRUD dos Vídeos do backend — posts de vídeo por link (YouTube, Vimeo...).
 *
 * espelha DocumentFormController (mesmo padrão simples validar+gravar),
 * só com o mínimo para gerir links de vídeo.
 */
class VideoFormController extends Controller
{
    public function create(): View
    {
        Gate::authorize('video.manage');

        return $this->formulario(null, 'Novo vídeo');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('video.manage');

        Video::query()->create($this->dados($request));

        return redirect()->route('admin.videos');
    }

    public function edit(int $id): View
    {
        Gate::authorize('video.manage');

        return $this->formulario(Video::findOrFail($id), 'Editar vídeo');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('video.manage');

        $video = Video::findOrFail($id);
        $video->update($this->dados($request));

        return redirect()->route('admin.videos');
    }

    public function destroy(int $id): RedirectResponse
    {
        Gate::authorize('video.manage');

        Video::findOrFail($id)->delete();

        return redirect()->route('admin.videos');
    }

    private function formulario(?Video $video, string $titulo): View
    {
        return view('admin.video-form', [
            'titulo' => $titulo,
            'video' => $video,
        ]);
    }

    private function dados(Request $request): array
    {
        $dados = $request->validate([
            'titulo' => ['required', 'string', 'max:190'],
            // link direto para o vídeo — qualquer plataforma suportada
            // (YouTube, Vimeo, Facebook, Instagram, mp4 directo); o frontend
            // compatibiliza com os embeds comuns.
            'url' => ['required', 'url:http,https', 'max:300'],
            'resumo' => ['nullable', 'string', 'max:500'],
            'imagem' => ['nullable', 'url:http,https', 'max:300'],
            'published_at' => ['nullable', 'date'],
        ], [
            'titulo.required' => 'Indique o título do vídeo.',
            'url.required' => 'Indique o link do vídeo.',
            'url.url' => 'O link tem de ser um URL válido (ex.: https://www.youtube.com/watch?v=…).',
            'imagem.url' => 'A miniatura tem de ser um URL válido.',
        ]);

        return [
            'titulo' => trim($dados['titulo']),
            'url' => trim($dados['url']),
            'resumo' => trim((string) ($dados['resumo'] ?? '')) ?: null,
            'imagem' => trim((string) ($dados['imagem'] ?? '')) ?: null,
            // published_at default = agora (se vazio, assume a data atual)
            'published_at' => ! empty($dados['published_at']) ? $dados['published_at'] : now(),
        ];
    }
}
