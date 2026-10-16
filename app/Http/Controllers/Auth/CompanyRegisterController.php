<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Registo de empresa (`/registo-empresa` — `registo-empresa.html`).
 *
 * O protótipo não tem campo de password (decisão do utilizador, 06/10/2026:
 * "funcional com auto-login"): a conta é criada com password aleatória,
 * o utilizador entra de imediato no `/painel-empresa` e define a password
 * em Definições → Segurança. A password aleatória também serve para o
 * reset por email, quando existir.
 */
class CompanyRegisterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'empresa' => ['required', 'string', 'max:190'],
            'nif' => ['required', 'string', 'max:32'],
            'setor' => ['required', 'string', Rule::in([
                'mineracao', 'agua', 'ambiente', 'energia',
                'construcao', 'transportes', 'servicos', 'outro',
            ])],
            'tipo_empresa' => ['nullable', 'string', Rule::in(['suarl', 'lda', 'sa', 'representacao', 'outro'])],
            'responsavel' => ['required', 'string', 'max:190'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique(User::class)],
            'telefone' => ['required', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:190'],
            'morada' => ['nullable', 'string', 'max:190'],
            'veracidade' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($dados): User {
            $user = User::create([
                'name' => $dados['responsavel'],
                'email' => $dados['email'],
                'password' => Hash::make(Str::random(40)),
            ]);
            $user->assignRole('company');

            CompanyProfile::create([
                'user_id' => $user->id,
                'name' => $dados['empresa'],
                'nif' => $dados['nif'],
                'sector' => $dados['setor'],
                'company_type' => $dados['tipo_empresa'] ?? null,
                'phone' => $dados['telefone'],
                'email' => $dados['email'],
                'website' => $dados['website'] ?? null,
                'address' => $dados['morada'] ?? null,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('company.dashboard');
    }
}
