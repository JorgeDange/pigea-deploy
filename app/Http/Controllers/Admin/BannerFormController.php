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
 * A imagem é `imagem_url`: aceita **upload OU link** (restrutura.md §1 —
 * "Imagens suportam Upload e Link"). O Spatie Media fica APENAS em News
 * (restrutura.md §7.5), por isso o upload é guardado no disco `public`
 * (pasta `banners/`) e o caminho gravado na coluna, como os demais uploads
 * do painel (NewsFormController, ProjectFormController).
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
            'imagem_link' => ['nullable', 'url:http,https', 'max:300'],
            'imagem_upload' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'link_destino' => ['nullable', 'url:http,https', 'max:300'],
            'posicao' => ['required', Rule::in(['topo', 'sidebar', 'rodape'])],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'ativo' => ['nullable', 'boolean'],
        ];

        // FASE 7 (bug apanhado por teste): na CRIAÇÃO o banner tem de nascer
        // com imagem — upload OU link. Na EDIÇÃO, sem nenhum dos dois mantém
        // a imagem atual (guardarImagem já o trata).
        if ($banner === null) {
            $regras['imagem_link'][] = 'required_without:imagem_upload';
        }

        return $request->validate($regras, [
            'titulo.required' => 'Indique o título do banner.',
            'imagem_link.required_without' => 'Envie um ficheiro de imagem OU indique o link da imagem.',
            'imagem_link.url' => 'O link da imagem tem de ser um URL válido (ex.: https://…).',
            'imagem_upload.image' => 'O ficheiro de imagem não é válido.',
            'imagem_upload.mimes' => 'A imagem tem de ser PNG, JPG ou WebP (máx. 4MB).',
            'imagem_upload.max' => 'A imagem não pode exceder 4MB.',
            'link_destino.url' => 'O link de destino tem de ser um URL válido (ex.: https://…).',
            'posicao.required' => 'Selecione a posição do banner.',
            'data_fim.after_or_equal' => 'A data de fim tem de ser posterior ou igual à data de início.',
        ]);
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
     * Grava a imagem: prioridade ao upload; sem upload mantém a atual (edição).
     * O upload substitui o ficheiro anterior se fôr nosso (prefixo `storage/`).
     */
    private function guardarImagem(Banner $banner, Request $request): void
    {
        if (! $request->hasFile('imagem_upload')) {
            // Sem upload: com link indicado (criação ou edição) grava o link;
            // sem nenhum dos dois mantém a imagem atual (edição).
            if (filled($request->input('imagem_link'))) {
                $this->apagarImagemLocal($banner->imagem_url);
                $banner->imagem_url = $request->input('imagem_link');
            }

            return;
        }

        $antiga = $banner->imagem_url;

        $banner->imagem_url = 'storage/'.$request->file('imagem_upload')->store('banners', 'public');

        $this->apagarImagemLocal($antiga);
    }

    private function apagarImagemLocal(?string $url): void
    {
        if ($url !== null && str_starts_with($url, 'storage/')) {
            Storage::disk('public')->delete(substr($url, strlen('storage/')));
        }
    }
}
