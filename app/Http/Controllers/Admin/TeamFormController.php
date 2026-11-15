<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\Institucional;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * CRUD admin dos Membros da Equipa (módulo institucional — Sobre Nós /equipa).
 * Área pública para leitura; aqui só o admin/editor da equipa interna.
 * Todas as imagens são por URL (decisão do cliente).
 *
 * Rotas (prefixo admin.):
 *   GET  /equipa              listar
 *   GET  /equipa/novo         formulário de cadastro
 *   POST /equipa/novo         criar
 *   GET  /equipa/{id}/editar  formulário de edição
 *   PUT  /equipa/{id}         atualizar
 *   DELETE /equipa/{id}       remover (soft delete)
 */
class TeamFormController extends Controller
{
    public function create(): View
    {
        return view('admin.equipa-form', ['membro' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validar($request);

        TeamMember::query()->create($dados);

        return redirect()->route('admin.equipa')->with('msg', 'Membro "' . $dados['nome'] . '" adicionado à equipa.');
    }

    public function edit(int $id): View
    {
        return view('admin.equipa-form', ['membro' => TeamMember::findOrFail($id)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $membro = TeamMember::findOrFail($id);

        $dados = $this->validar($request);
        $membro->update($dados);

        return redirect()->route('admin.equipa')->with('msg', 'Membro "' . $dados['nome'] . '" atualizado.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $membro = TeamMember::find($id);
        $membro?->delete();

        return redirect()->route('admin.equipa')->with('msg', 'Membro removido da equipa.');
    }

    /** @return array<string, mixed> */
    private function validar(Request $request): array
    {
        $seccoes = implode(',', array_keys(Institucional::SECCOES_EQUIPA));

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'cargo_select' => ['required', 'string', 'in:' . $seccoes],
            'cargo_outro' => ['required_if:cargo_select,outros', 'nullable', 'string', 'max:190'],
            'foto_url' => ['nullable', 'url:http,https', 'max:2048'],
            'facebook' => ['nullable', 'url:http,https', 'max:2048'],
            'instagram' => ['nullable', 'url:http,https', 'max:2048'],
            'linkedin' => ['nullable', 'url:http,https', 'max:2048'],
            'ordem' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'nome.required' => 'Indique o nome do membro.',
            'cargo_select.required' => 'Selecione o cargo.',
            'cargo_select.in' => 'Cargo inválido.',
            'cargo_outro.required_if' => 'Defina o cargo no campo "Defina o cargo (Outros)".',
            'foto_url.url' => 'A foto deve ser um URL válido (http/https).',
            'ordem.integer' => 'A ordem tem de ser um número.',
        ]);

        // "Outros" mostra-se pela etiqueta que o admin definiu; nos outros casos
        // o cargo é a secção canônica (o select é a própria hierarquia).
        if (($dados['cargo_select'] ?? '') !== 'outros') {
            $dados['cargo_outro'] = null;
        }

        $dados['ordem'] = (int) ($dados['ordem'] ?? 0);
        $dados['ativo'] = (bool) ($dados['ativo'] ?? false);

        return $dados;
    }
}
