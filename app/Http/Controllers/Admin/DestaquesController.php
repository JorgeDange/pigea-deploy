<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destaque;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * CRUD dos Destaques publicitários (Fase 15 — pub.md).
 *
 * Imagem via upload assíncrono (banner.js) ou link externo; também aqui
 * a imagem chega como caminho `storage/…` ou URL http(s) no campo `imagem_link`.
 * Regras de exposição: ativo + dentro do período + escopo.
 */
class DestaquesController extends Controller
{
    public function index(): View
    {
        Gate::authorize('banner.manage');

        return view('admin.destaques', [
            'destaques' => Destaque::orderBy('ordem')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('banner.manage');

        return $this->formulario(null, 'Novo destaque');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('banner.manage');

        $dados = $this->validar($request);

        $destaque = new Destaque($this->dados($dados));
        $this->guardarImagem($destaque, $request);
        $destaque->save();

        return redirect()->route('admin.destaques');
    }

    public function edit(int $id): View
    {
        Gate::authorize('banner.manage');

        $destaque = Destaque::findOrFail($id);

        return $this->formulario($destaque, 'Editar destaque');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('banner.manage');

        $destaque = Destaque::findOrFail($id);

        $dados = $this->validar($request, $destaque);

        $destaque->fill($this->dados($dados));
        $this->guardarImagem($destaque, $request);
        $destaque->save();

        return redirect()->route('admin.destaques');
    }

    public function destroy(int $id): RedirectResponse
    {
        Gate::authorize('banner.manage');

        $destaque = Destaque::findOrFail($id);
        $url = $destaque->imagem_url;
        $destaque->delete();

        $this->apagarImagemLocal($url);

        return redirect()->route('admin.destaques');
    }

    public function toggle(int $id): RedirectResponse
    {
        Gate::authorize('banner.manage');

        $destaque = Destaque::findOrFail($id);
        $destaque->ativo = ! $destaque->ativo;
        $destaque->save();

        return redirect()->route('admin.destaques');
    }

    private function formulario(?Destaque $destaque, string $titulo): View
    {
        return view('admin.destaque-form', [
            'titulo' => $titulo,
            'destaque' => $destaque,
            'escopos' => Destaque::ESCOPOS,
        ]);
    }

    private function validar(Request $request, ?Destaque $destaque = null): array
    {
        $regras = [
            'titulo' => ['required', 'string', 'max:190'],
            'imagem_link' => ['nullable', 'string', 'max:2048'],
            'link_destino' => ['nullable', 'url:http,https', 'max:2048'],
            'escopo' => ['required', Rule::in(array_keys(Destaque::ESCOPOS))],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'ordem' => ['nullable', 'integer', 'min:1', 'max:20'],
            'ativo' => ['nullable', 'boolean'],
        ];

        if ($destaque === null) {
            $regras['imagem_link'][] = 'required';
        }

        $dados = $request->validate($regras, [
            'titulo.required' => 'Indique o título do destaque.',
            'imagem_link.required' => 'Envie um ficheiro de imagem OU indique o link da imagem.',
            'link_destino.url' => 'O link de destino tem de ser um URL válido (ex.: https://…).',
            'escopo.required' => 'Selecione onde o destaque deve aparecer.',
            'escopo.in' => 'Escopo inválido.',
            'data_fim.after_or_equal' => 'A data de fim tem de ser posterior ou igual à data de início.',
            'ordem.integer' => 'A ordem tem de ser um número.',
            'ordem.max' => 'A ordem máxima é 20.',
        ]);

        $dados['imagem_link'] = $this->normalizarUrlImagem($dados['imagem_link'] ?? null);

        return $dados;
    }

    private function normalizarUrlImagem(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (str_starts_with($url, 'storage/')) {
            if (preg_match('#^storage/(publicidade|banners|destaques|media/banners)/[0-9]{4}/[0-9]{2}/[A-Za-z0-9_.-]+\.[A-Za-z0-9]{2,5}$#', $url)) {
                $relativo = substr($url, strlen('storage/'));
                if (Storage::disk('public')->exists($relativo)) {
                    return $url;
                }
            }
            throw \Illuminate\Validation\ValidationException::withMessages(['imagem_link' => 'A imagem enviada expirou ou não é acessível. Carregue novamente.']);
        }

        // Imagens do tema (`images/all/…`) ou qualquer caminho relativo, tal como já vem nas seeds.
        if (! preg_match('#^(https?:|//)#i', $url) && ! str_starts_with($url, 'storage/')) {
            return $url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $url)) {
            return $url;
        }

        throw \Illuminate\Validation\ValidationException::withMessages(['imagem_link' => 'O link da imagem tem de ser um URL válido (ex.: https://…).']);
    }

    private function dados(array $validados): array
    {
        return [
            'titulo' => trim($validados['titulo']),
            'link_destino' => $validados['link_destino'] ?? null,
            'escopo' => $validados['escopo'],
            'data_inicio' => $validados['data_inicio'] ?? null,
            'data_fim' => $validados['data_fim'] ?? null,
            'ordem' => (int) ($validados['ordem'] ?? 1),
            'ativo' => (bool) ($validados['ativo'] ?? false),
        ];
    }

    private function guardarImagem(Destaque $destaque, Request $request): void
    {
        $link = $request->input('imagem_link');

        if ($link === null) {
            return;
        }

        $link = trim($link);

        if ($link === '') {
            if ($destaque->exists) {
                $antigo = $destaque->imagem_url;
                $destaque->imagem_url = null;
                $this->apagarImagemLocal($antigo);
            }

            return;
        }

        if ($destaque->exists && $link === $destaque->imagem_url) {
            return;
        }

        $antigo = $destaque->imagem_url;
        $destaque->imagem_url = $link;
        $this->apagarImagemLocal($antigo);
    }

    private function apagarImagemLocal(?string $url): void
    {
        if ($url !== null && str_starts_with($url, 'storage/')) {
            Storage::disk('public')->delete(substr($url, strlen('storage/')));
        }
    }
}
