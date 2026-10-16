<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Os 5 formulários de `/admin/configuracoes` (protótipo admin/configuracoes.html).
 *
 * No protótipo cada cartão tem `<form class="dashboard-form">` sem `action` e um
 * botão *Guardar* que não fazia nada. Aqui cada cartão submete para a sua rota
 * `admin.configuracoes.<secção>`, valida com as regras do Laravel (fase.md §2.2)
 * e grava na tabela `settings`.
 *
 * O cartão **Idiomas** não tem `<form>` no protótipo (só duas `switch` soltas),
 * por isso continua visual — não há o que submeter.
 */
class SettingsController extends Controller
{
    /**
     * Secção (rota) → regras de validação dos campos desse formulário.
     *
     * @var array<string, array<string, string>>
     */
    protected const REGRAS = [
        'geral' => [
            'portal' => 'required|string|max:200',
            'descricao' => 'nullable|string|max:500',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg|max:2048',
            'favicon' => 'nullable|file|mimes:png|max:256',
        ],
        'contactos' => [
            'email' => 'required|email|max:190',
            'telefone' => 'nullable|string|max:40',
            'endereco' => 'nullable|string|max:190',
        ],
        'redes' => [
            'facebook' => 'nullable|string|max:190',
            'instagram' => 'nullable|string|max:190',
            'linkedin' => 'nullable|string|max:190',
            'youtube' => 'nullable|string|max:190',
        ],
        'seo' => [
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ],
        'email' => [
            'template' => 'nullable|in:novo-registo,nova-candidatura,newsletter',
            'texto_email' => 'nullable|string|max:2000',
        ],
    ];

    /** Página com os valores já guardados (vazios = placeholder do protótipo). */
    public function index(): View
    {
        return view('admin.configuracoes', [
            'definicoes' => Setting::todas(),
        ]);
    }

    /** Cartão "Geral" (nome, descrição, logótipo, favicon). */
    public function geral(Request $request): RedirectResponse
    {
        return $this->guardar($request, 'geral');
    }

    /** Cartão "Contactos" (email, telefone, endereço). */
    public function contactos(Request $request): RedirectResponse
    {
        return $this->guardar($request, 'contactos');
    }

    /** Cartão "Redes sociais" (facebook, instagram, linkedin, youtube). */
    public function redes(Request $request): RedirectResponse
    {
        return $this->guardar($request, 'redes');
    }

    /** Cartão "SEO" (meta title e meta description padrão). */
    public function seo(Request $request): RedirectResponse
    {
        return $this->guardar($request, 'seo');
    }

    /** Cartão "Notificações por email" (modelo e texto do email). */
    public function email(Request $request): RedirectResponse
    {
        return $this->guardar($request, 'email');
    }

    /**
     * Valida os campos da secção e grava-os em `settings`.
     *
     * Em caso de falha o próprio Laravel reencaminha para trás com `$errors`
     * (mostrados em `.form-error`, como no login) e mantém o que foi digitado
     * (a vista lê `old()`); num envio válido reencaminha para a mesma página,
     * onde os inputs passam a mostrar o valor guardado.
     */
    protected function guardar(Request $request, string $seccao): RedirectResponse
    {
        $dados = $request->validate(self::REGRAS[$seccao]);

        foreach ($dados as $chave => $valor) {
            if ($request->hasFile($chave)) {
                $valor = $request->file($chave)->store('settings', 'public');
                $this->apagarFicheiroAnterior($chave);
            }

            Setting::guardar([$chave => $valor]);
        }

        return redirect()->route('admin.configuracoes');
    }

    /** Remove o ficheiro substituído para não deixar órfãos em `storage`. */
    protected function apagarFicheiroAnterior(string $chave): void
    {
        $anterior = Setting::obter($chave);

        if ($anterior && Storage::disk('public')->exists($anterior)) {
            Storage::disk('public')->delete($anterior);
        }
    }
}
