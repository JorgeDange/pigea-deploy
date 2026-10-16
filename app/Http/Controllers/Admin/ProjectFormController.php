<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * `admin/projeto-novo.html` — formulário de criação de projeto.
 *
 * O select `estado` do protótipo usa o ciclo de vida do projeto; mapeia-se para
 * a mesma convenção da tabela `/admin/projetos` do painel
 * (Ativo → `published`, Em análise → `draft`, Expirada → `rejected`).
 * Documentos/imagens múltiplos: guardados em `storage` como JSON (`documents`,
 * `images`) **sem exibição** — o protótipo não tem galeria nem lista de
 * documentos. A primeira imagem alimenta também `image` (cartões públicos).
 */
class ProjectFormController extends Controller
{
    public function create(): View
    {
        return view('admin.projeto-novo');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'minereral' => ['nullable', Rule::in(['ferro', 'ouro', 'diamantes', 'litio', 'manganes', 'cobre', 'fosfatos'])],
            'provincia' => ['nullable', Rule::in(['huila', 'namibe', 'uige', 'cuanza-sul', 'benguela', 'cuando-cubango'])],
            'fase' => ['nullable', Rule::in(['prospeccao', 'exploracao', 'producao'])],
            'operador' => ['nullable', 'string', 'max:190'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['nullable', Rule::in(['ativo', 'em-analise', 'expirada'])],
            'descricao' => ['nullable', 'string', 'max:500'],
            'documentos' => ['nullable', 'array'],
            'documentos.*' => ['file', 'mimes:pdf', 'max:10240'],
            'imagens' => ['nullable', 'array'],
            'imagens.*' => ['file', 'mimes:png,jpg,jpeg', 'max:4096'],
        ]);

        $estado = $dados['estado'] ?? '';

        $imagens = $request->hasFile('imagens')
            ? array_map(fn ($f) => 'storage/'.$f->store('projects', 'public'), (array) $request->file('imagens'))
            : [];
        $documentos = $request->hasFile('documentos')
            ? array_map(fn ($f) => 'storage/'.$f->store('projects', 'public'), (array) $request->file('documentos'))
            : [];

        $publicadoEm = $estado === '' ? null : now();

        Project::query()->create([
            'author_id' => $request->user()->id,
            'title' => $dados['nome'],
            'slug' => $this->slugUnico($dados['nome']),
            'excerpt' => $dados['descricao'] ?? null,
            'content' => $dados['descricao'] ?? null,
            'status' => match ($estado) {
                'ativo' => 'published',
                'expirada' => 'rejected',
                default => 'draft',
            },
            'published_at' => $publicadoEm,
            'date_label' => $publicadoEm?->locale('pt')->translatedFormat('d M Y'),
            'province' => $dados['provincia'] ?? null,
            'mineral' => $dados['minereral'] ?? null,
            'phase' => $dados['fase'] ?? null,
            'operator' => $dados['operador'] ?? null,
            'area' => $dados['area'] ?? null,
            'documents' => $documentos,
            'images' => $imagens,
            'image' => $imagens[0] ?? null,
        ]);

        return redirect()->route('admin.projetos');
    }

    /** Slug derivado do nome, sufixado até ficar único. */
    private function slugUnico(string $titulo): string
    {
        $base = Str::slug($titulo);
        $candidato = $base;
        $n = 1;

        while (Project::query()->withTrashed()->where('slug', $candidato)->exists()) {
            $candidato = $base.'-'.$n++;
        }

        return $candidato;
    }
}
