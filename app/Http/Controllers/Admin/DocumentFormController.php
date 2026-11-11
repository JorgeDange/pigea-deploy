<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectoryDocument;
use App\Support\AreasAtuacao;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * CRUD dos Artigos/Documentos do Diretório (restrutura.md §5.5).
 *
 * Só a equipa interna publica (não há submissão pública nem status na
 * tabela). O modelo não tem title/slug/categoria/publicação — por isso tem
 * controlador próprio em vez do formulário genérico ContentFormController.
 * O link de download é APENAS link (restrutura.md §1: "Vídeos e Documentos
 * suportam apenas Link").
 */
class DocumentFormController extends Controller
{
    public function create(): View
    {
        Gate::authorize('document.manage');

        return $this->formulario(null, 'Novo documento');
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('document.manage');

        $dados = $this->validar($request);

        DirectoryDocument::query()->create($this->dados($dados));

        return redirect()->route('admin.documentos');
    }

    public function edit(int $id): View
    {
        Gate::authorize('document.manage');

        $documento = DirectoryDocument::findOrFail($id);

        return $this->formulario($documento, 'Editar documento');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('document.manage');

        $documento = DirectoryDocument::findOrFail($id);

        $dados = $this->validar($request);

        $documento->update($this->dados($dados));

        return redirect()->route('admin.documentos');
    }

    /**
     * Áreas submetidas (checkboxes) — filtradas contra a whitelist, nunca
     * gravar texto arbitrário do POST no JSON.
     */
    public static function areas(): array
    {
        return AreasAtuacao::LISTA;
    }

    private function formulario(?DirectoryDocument $documento, string $titulo): View
    {
        return view('admin.documento-form', [
            'titulo' => $titulo,
            'documento' => $documento,
            'areasLista' => AreasAtuacao::LISTA,
            'areasSelecionadas' => $documento?->areas_atuacao ?? [],
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'titulo' => ['required', 'string', 'max:190'],
            'descricao' => ['required', 'string', 'max:500'],
            'tipo' => ['required', Rule::in(['artigo', 'documento', 'lei'])],
            'link_download' => ['required', 'url:http,https', 'max:300'],
            'areas_atuacao' => ['required', 'array', 'min:1'],
            'areas_atuacao.*' => ['string'],
        ], [
            'titulo.required' => 'Indique o título.',
            'descricao.required' => 'Indique a descrição.',
            'descricao.max' => 'A descrição é demasiado longa (máx. 500 caracteres).',
            'tipo.required' => 'Selecione o tipo.',
            'link_download.required' => 'Indique o link de download.',
            'link_download.url' => 'O link de download tem de ser um URL válido (ex.: https://…).',
            'areas_atuacao.required' => 'Selecione pelo menos uma área de atuação.',
        ]);
    }

    private function dados(array $validados): array
    {
        return [
            'titulo' => trim($validados['titulo']),
            'descricao' => trim($validados['descricao']),
            'tipo' => $validados['tipo'],
            'link_download' => trim($validados['link_download']),
            'areas_atuacao' => AreasAtuacao::filtrar($validados['areas_atuacao']),
        ];
    }
}
