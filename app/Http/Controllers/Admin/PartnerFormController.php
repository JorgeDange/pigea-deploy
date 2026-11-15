<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Support\Institucional;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CRUD admin dos Parceiros (módulo institucional — Sobre Nós /parceiros).
 * Área pública para leitura; gestão interna (role:admin,editor).
 * Imagens por URL (decisão do cliente). `tipo` define a secção pública.
 *
 * Rotas (prefixo admin.):
 *   GET  /parceiros              listar
 *   GET  /parceiros/novo         formulário de cadastro
 *   POST /parceiros/novo         criar
 *   GET  /parceiros/{id}/editar  formulário de edição
 *   PUT  /parceiros/{id}         atualizar
 *   DELETE /parceiros/{id}       remover (soft delete)
 */
class PartnerFormController extends Controller
{
    public function create(): View
    {
        return view('admin.parceiro-form', ['parceiro' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validar($request);

        Partner::query()->create($dados);

        return redirect()->route('admin.parceiros')->with('msg', 'Parceiro "' . $dados['nome'] . '" adicionado.');
    }

    public function edit(int $id): View
    {
        return view('admin.parceiro-form', ['parceiro' => Partner::findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $parceiro = Partner::findOrFail($id);

        $dados = $this->validar($request);
        $parceiro->update($dados);

        return redirect()->route('admin.parceiros')->with('msg', 'Parceiro "' . $dados['nome'] . '" atualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $parceiro = Partner::find($id);
        $parceiro?->delete();

        return redirect()->route('admin.parceiros')->with('msg', 'Parceiro removido.');
    }

    /** @return array<string, mixed> */
    private function validar(Request $request): array
    {
        $tipos = implode(',', array_keys(Institucional::TIPOS_PARCEIRO));

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'tipo' => ['required', 'string', 'in:' . $tipos],
            'imagem_url' => ['nullable', 'url:http,https', 'max:2048'],
            'detalhes' => ['nullable', 'string', 'max:2000'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'nome.required' => 'Indique o nome do parceiro.',
            'tipo.required' => 'Selecione o tipo de parceiro.',
            'tipo.in' => 'Tipo inválido (Empresa ou Profissional).',
            'imagem_url.url' => 'A imagem deve ser um URL válido (http/https).',
            'detalhes.max' => 'Os detalhes têm de ter no máximo 2000 carateres.',
            'ordem.integer' => 'A ordem tem de ser um número.',
        ]);

        $dados['ordem'] = (int) ($dados['ordem'] ?? 0);
        $dados['ativo'] = (bool) ($dados['ativo'] ?? false);

        return $dados;
    }
}
