<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * CRUD da Publicidade — banners (restrutura.md §5.6).
 *
 * A imagem é `imagem_url`: aceita **upload assíncrono** (via XHR em
 * banner.js → PublicidadeImagemController) ou link externo. A imagem
 * chega aqui SEMPRE como `imagem_link`:
 *  - string `storage/…` (upload interno, validada em normalizarUrlImagem);
 *  - URL http(s) externa;
 *  - vazio = remover a imagem atual.
 */
class BannerFormController extends Controller
{
    public function create(): View
    {
        Gate::authorize('banner.manage');

        return $this->formulario(null, 'Novo banner');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('banner.manage');

        $dados = $this->validar($request);

        $banner = new Banner($this->dados($dados));
        $this->guardarImagem($banner, $request);
        $banner->save();

        return redirect()->route('admin.banners');
    }

    public function edit(int $id): View
    {
        Gate::authorize('banner.manage');

        $banner = Banner::findOrFail($id);

        return $this->formulario($banner, 'Editar banner');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('banner.manage');

        $banner = Banner::findOrFail($id);

        $dados = $this->validar($request, $banner);

        $banner->fill($this->dados($dados));
        $this->guardarImagem($banner, $request);
        $banner->save();

        return redirect()->route('admin.banners');
    }

    private function formulario(?Banner $banner, string $titulo): View
    {
        return view('admin.banner-form', [
            'titulo' => $titulo,
            'banner' => $banner,
        ]);
    }

    private function validar(Request $request, ?Banner $banner = null): array
    {
        $regras = [
            'titulo' => ['required', 'string', 'max:190'],
            'imagem_link' => ['nullable', 'string', 'max:2048'],
            'link_destino' => ['nullable', 'url:http,https', 'max:300'],
            'posicao' => ['required', Rule::in(['topo', 'sidebar', 'rodape', 'lateral'])],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'ativo' => ['nullable', 'boolean'],
        ];

        // Na CRIAÇÃO o banner tem de nascer com imagem. Na EDIÇÃO, sem
        // imagem_link preenchido mantém a atual.
        if ($banner === null) {
            $regras['imagem_link'][] = 'required';
        }

        $dados = $request->validate($regras, [
            'titulo.required' => 'Indique o título do banner.',
            'imagem_link.required' => 'Envie um ficheiro de imagem OU indique o link da imagem.',
            'link_destino.url' => 'O link de destino tem de ser um URL válido (ex.: https://…).',
            'posicao.required' => 'Selecione a posição do banner.',
            'data_fim.after_or_equal' => 'A data de fim tem de ser posterior ou igual à data de início.',
        ]);

        $dados['imagem_link'] = $this->normalizarUrlImagem($dados['imagem_link'] ?? null);

        return $dados;
    }

    /**
     * Aceita:
     *  - URL externo (http/https);
     *  - caminho interno `storage/{publicidade|banners|destaques}/AAAA/MM/…`,
     *    gravado por nós no disco `public`.
     */
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
            'posicao' => $validados['posicao'],
            'data_inicio' => $validados['data_inicio'] ?? null,
            'data_fim' => $validados['data_fim'] ?? null,
            'ativo' => (bool) ($validados['ativo'] ?? false),
        ];
    }

    /**
     * Com o upload assíncrono, a imagem chega como caminho interno ou URL
     * externa no campo `imagem_link`. Sem nada preenchido mantém a atual;
     * string vazia = remoção explícita.
     */
    private function guardarImagem(Banner $banner, Request $request): void
    {
        $link = $request->input('imagem_link');

        if ($link === null) {
            return;
        }

        $link = trim($link);

        if ($link === '') {
            if ($banner->exists) {
                $antigo = $banner->imagem_url;
                $banner->imagem_url = null;
                $this->apagarImagemLocal($antigo);
            }

            return;
        }

        if ($banner->exists && $link === $banner->imagem_url) {
            return; // nada mudou
        }

        $antigo = $banner->imagem_url;
        $banner->imagem_url = $link;
        $this->apagarImagemLocal($antigo);
    }

    private function apagarImagemLocal(?string $url): void
    {
        if ($url !== null && str_starts_with($url, 'storage/')) {
            Storage::disk('public')->delete(substr($url, strlen('storage/')));
        }
    }
}
