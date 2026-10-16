<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\CompanyProfile;
use App\Models\Job;
use App\Models\Opportunity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/**
 * Painel da empresa (`painel-empresa*.html`) — `role:company`.
 *
 * As próprias vagas da empresa vêm de `vacancies.author_id`; as candidaturas
 * são as `applications` nas suas vagas. O perfil da empresa é a linha de
 * `company_profiles` (criada no primeiro Guardar).
 */
class EmpresaController extends Controller
{
    public function dashboard(Request $request): View
    {
        $u = $request->user();

        $vagasIds = Job::query()->where('author_id', $u->id)->pluck('id');

        return view('painel-empresa.dashboard', [
            'vagas' => $vagasIds->count(),
            'candidaturas' => Application::query()->whereIn('job_id', $vagasIds)->count(),
            'oportunidades' => $u->opportunities()->where('status', 'ativa')->count(),
            'vagasRecentes' => Job::query()->where('author_id', $u->id)->withCount('applications')->orderByDesc('id')->limit(5)->get(),
            'candidaturasRecentes' => Application::query()->with(['user', 'job'])->whereIn('job_id', $vagasIds)->latest()->limit(4)->get(),
        ]);
    }

    public function perfil(Request $request): View
    {
        return view('painel-empresa.perfil', [
            'perfil' => $request->user()->companyProfile,
        ]);
    }

    /** Tabela de oportunidades da própria empresa (eliminar é POST). */
    public function oportunidades(Request $request): View
    {
        return view('painel-empresa.oportunidades', [
            'oportunidades' => $request->user()->opportunities()->orderByDesc('id')->get(),
        ]);
    }

    public function guardarPerfil(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'empresa' => ['required', 'string', 'max:190'],
            'nif' => ['required', 'string', 'max:32'],
            'setor' => ['nullable', Rule::in(['mineracao', 'agua', 'ambiente', 'energia', 'construcao', 'servicos'])],
            'provincia' => ['nullable', Rule::in(['luanda', 'benguela', 'huila', 'malanje', 'zaire'])],
            'endereco' => ['nullable', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190'],
            'telefone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:190'],
            'descricao' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
            'certificacoes' => ['nullable', 'array'],
            'certificacoes.*' => ['file', 'mimes:pdf,png,jpg,jpeg', 'max:4096'],
        ]);

        $perfil = $request->user()->companyProfile()->firstOrNew(['user_id' => $request->user()->id]);
        $perfil->fill([
            'name' => $dados['empresa'],
            'nif' => $dados['nif'],
            'sector' => $dados['setor'] ?? null,
            'province' => $dados['provincia'] ?? null,
            'address' => $dados['endereco'] ?? null,
            'email' => $dados['email'],
            'phone' => $dados['telefone'] ?? null,
            'website' => $dados['website'] ?? null,
            'description' => $dados['descricao'] ?? null,
        ]);

        if ($request->hasFile('logo')) {
            if ($perfil->logo && Storage::disk('public')->exists($perfil->logo)) {
                Storage::disk('public')->delete($perfil->logo);
            }
            $perfil->logo = $request->file('logo')->store('empresas', 'public');
        }

        $perfil->save();

        return redirect()->route('company.perfil');
    }

    public function vagaNova(): View
    {
        return view('painel-empresa.vaga-nova', [
            'categorias' => \App\Models\Category::query()->where('type', 'jobs')->orderBy('name')->get(),
        ]);
    }

    /** Cria vaga (pública assim que publicada — visível em `/empregos`). */
    public function guardarVaga(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'titulo' => ['required', 'string', 'max:190'],
            // O select do protótipo usa valores em texto (geologia, mineracao…);
            // ligamos à categoria quando o slug existe em `categories`.
            'categoria' => ['nullable', Rule::in(['geologia', 'mineracao', 'ambiente', 'agua', 'sig', 'engenharia'])],
            'tipo' => ['nullable', Rule::in(['efetivo', 'temporario', 'estagio', 'consultoria'])],
            'provincia' => ['nullable', Rule::in(['luanda', 'benguela', 'huila', 'malanje', 'cuando-cubango'])],
            'prazo' => ['nullable', 'date'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'requisitos' => ['nullable', 'string', 'max:2000'],
            'responsabilidades' => ['nullable', 'string', 'max:2000'],
        ]);

        $categoria = ! empty($dados['categoria'])
            ? \App\Models\Category::query()->where('type', 'jobs')->where('slug', $dados['categoria'])->first()
            : null;

        // O formulário só tem um botão de submissão ("Publicar"); o "Guardar
        // rascunho" do protótipo é `type="button"` (inerte também aqui).
        Job::query()->create([
            'author_id' => $request->user()->id,
            'category_id' => $categoria?->id,
            'title' => $dados['titulo'],
            'slug' => $this->slugUnico($dados['titulo']),
            'excerpt' => $dados['descricao'] ?? null,
            'content' => $dados['descricao'] ?? null,
            'status' => 'published',
            'published_at' => now(),
            'date_label' => now()->locale('pt')->translatedFormat('d M Y'),
            'contract_type' => $dados['tipo'] ?? null,
            'province' => $dados['provincia'] ?? null,
            'deadline' => $dados['prazo'] ?? null,
            'requirements' => $dados['requisitos'] ?? null,
            'responsibilities' => $dados['responsabilidades'] ?? null,
        ]);

        return redirect()->route('company.vagas');
    }

    /** "Eliminar" (dashboard "Vagas recentes" — só as vagas da própria empresa). */
    public function eliminarVaga(Request $request, Job $job): RedirectResponse
    {
        // Gate de posse (Fase 13) — 403 como antes, mas centralizado.
        Gate::authorize('delete-vacancy', $job);

        $job->delete();

        return redirect()->back();
    }

    public function estatisticas(Request $request): View
    {
        $u = $request->user();
        $vagasIds = Job::query()->where('author_id', $u->id)->pluck('id');

        return view('painel-empresa.estatisticas', [
            'candidaturas' => Application::query()->whereIn('job_id', $vagasIds)->count(),
            'oportunidadesAtivas' => $u->opportunities()->where('status', 'ativa')->count(),
            'vagasPublicadas' => $vagasIds->count(),
            // "Resumo por vaga": títulos + candidaturas reais; "Visualizações"
            // e "Taxa de conversão" não têm fonte (→ "—" como no admin).
            'vagasResumo' => Job::query()->where('author_id', $u->id)->withCount('applications')->orderByDesc('id')->limit(5)->get(),
        ]);
    }

    public function definicoes(Request $request): View
    {
        return view('painel-empresa.definicoes', ['user' => $request->user()]);
    }

    /** Cartão "Segurança" do painel da empresa (mesma regra do utilizador). */
    public function guardarSeguranca(Request $request): RedirectResponse
    {
        $request->validate([
            'pw-atual' => ['required', 'current_password'],
            'pw-nova' => ['required', \Illuminate\Validation\Rules\Password::min(8)],
            // `confirmed` esperaria *_confirmation; o protótipo usa `pw-conf`.
            'pw-conf' => ['required', 'same:pw-nova'],
        ]);

        $request->user()->update(['password' => \Illuminate\Support\Facades\Hash::make($request->input('pw-nova'))]);

        return redirect()->route('company.definicoes');
    }

    /** "Eliminar" da tabela de oportunidades (só as da própria empresa). */
    public function eliminarOportunidade(Request $request, Opportunity $oportunidade): RedirectResponse
    {
        // Gate de posse (Fase 13) — 403 como antes, mas centralizado.
        Gate::authorize('delete-opportunity', $oportunidade);

        $oportunidade->delete();

        return redirect()->route('company.oportunidades');
    }

    /** Slug derivado do título, sufixado até ficar único. */
    private function slugUnico(string $titulo): string
    {
        $base = Str::slug($titulo);
        $candidato = $base;
        $n = 1;

        while (Job::query()->withTrashed()->where('slug', $candidato)->exists()) {
            $candidato = $base.'-'.$n++;
        }

        return $candidato;
    }
}
