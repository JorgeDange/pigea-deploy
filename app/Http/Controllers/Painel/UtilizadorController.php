<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\SavedJob;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

/**
 * Painel do utilizador (`painel-utilizador*.html`) — `role:user`.
 *
 * Dashboard com números reais; formulários *Perfil* (nome/email/telefone/
 * província/bio) e *Definições* (password, visibilidade) passam a ser
 * funcionais (fase.md §2.2) — os campos são de `users` e nunca sobem de
 * privilégio (role/status NUNCA vêm deste formulário — ver pré-requisito de
 * `$fillable` em `User`).
 */
class UtilizadorController extends Controller
{
    public function dashboard(Request $request): View
    {
        $u = $request->user();

        return view('painel-utilizador.dashboard', [
            'enviadas' => $u->applications()->count(),
            'enviadasSemana' => $u->applications()->where('created_at', '>=', now()->subDays(7))->count(),
            'emAnalise' => $u->applications()->where('status', 'em-analise')->count(),
            'guardadas' => $u->savedJobs()->count(),
            'naoLidas' => $u->notifications()->whereNull('read_at')->count(),
            'candidaturas' => $u->applications()->with('job.category')->latest()->limit(5)->get(),
            'recomendadas' => Job::published()->orderByDesc('published_at')->limit(3)->get(),
            'notificacoes' => $u->notifications()->latest()->limit(4)->get(),
        ]);
    }

    public function perfil(Request $request): View
    {
        return view('painel-utilizador.perfil', ['user' => $request->user()]);
    }

    /** Cartões de `vagas guardadas` (remover é POST + redirect). */
    public function guardadas(Request $request): View
    {
        return view('painel-utilizador.guardadas', [
            'guardadas' => $request->user()->savedJobs()
                ->with(['job', 'job.category', 'job.author'])
                ->orderByDesc('saved_jobs.id')
                ->get(),
        ]);
    }

    /** Lista completa de notificações (data no formato `dd/mm/AAAA · HH:ii`). */
    public function notificacoes(Request $request): View
    {
        return view('painel-utilizador.notificacoes', [
            'notificacoes' => $request->user()->notifications()->latest()->get(),
        ]);
    }

    public function guardarPerfil(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:190'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'telefone' => ['nullable', 'string', 'max:40'],
            'provincia' => ['nullable', Rule::in(['luanda', 'benguela', 'huila', 'malanje', 'cuando-cubango', 'zaire'])],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->update([
            'name' => $dados['nome'],
            'email' => $dados['email'],
            'phone' => $dados['telefone'] ?? null,
            'province' => $dados['provincia'] ?? null,
            'bio' => $dados['bio'] ?? null,
        ]);

        return redirect()->route('user.perfil');
    }

    public function definicoes(Request $request): View
    {
        return view('painel-utilizador.definicoes', ['user' => $request->user()]);
    }

    /** Cartão "Segurança": mudança de password (password atual confirmada). */
    public function guardarSeguranca(Request $request): RedirectResponse
    {
        $request->validate([
            'pw-atual' => ['required', 'current_password'],
            'pw-nova' => ['required', Password::min(8)],
            // `confirmed` esperaria *_confirmation; o protótipo usa `pw-conf`.
            'pw-conf' => ['required', 'same:pw-nova'],
        ]);

        $request->user()->update(['password' => Hash::make($request->input('pw-nova'))]);

        return redirect()->route('user.definicoes');
    }

    /** Cartão "Privacidade": select de visibilidade do perfil. */
    public function guardarPrivacidade(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'visibilidade' => ['required', Rule::in(['todos', 'empresas', 'ninguem'])],
        ]);

        $request->user()->forceFill(['profile_visibility' => $dados['visibilidade']])->save();

        return redirect()->route('user.definicoes');
    }

    /** "Marcar todas como lidas" (dashboard.js também o faz; aqui é na BD). */
    public function marcarLidas(Request $request): RedirectResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('user.notificacoes');
    }

    /** Botão "Guardar" das vagas recomendadas (alterna guardar/remover). */
    public function alternarGuardada(Request $request, Job $job): RedirectResponse
    {
        $guardada = SavedJob::query()
            ->where('user_id', $request->user()->id)
            ->where('job_id', $job->id)
            ->first();

        if ($guardada !== null) {
            $guardada->delete();
        } else {
            SavedJob::query()->create(['user_id' => $request->user()->id, 'job_id' => $job->id]);
        }

        return redirect()->back();
    }

    /** Botão "Remover" dos cartões de `vagas guardadas`. */
    public function removerGuardada(Request $request, Job $job): RedirectResponse
    {
        SavedJob::query()
            ->where('user_id', $request->user()->id)
            ->where('job_id', $job->id)
            ->delete();

        return redirect()->route('user.guardadas');
    }
}
