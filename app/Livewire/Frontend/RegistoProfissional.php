<?php

namespace App\Livewire\Frontend;

use App\Models\Professional;
use App\Support\AreasAtuacao;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Formulário público de registo de profissional (restrutura.md §4.2).
 *
 * Sem login: submissão direta com `status = 'pendente'`; a equipa interna
 * aprova/rejeita no /admin (Fase 4). Sucesso mostra mensagem equivalente à
 * do registo de empresa.
 */
class RegistoProfissional extends Component
{
    public string $nome_completo = '';

    public string $formacao_academica = '';

    public string $contactos = '';

    /** @var list<string> áreas de atuação submetidas (checkboxes) */
    public array $areas_atuacao = [];

    /** `''` (formulário), `'ok'` (sucesso) ou `'erro'`. */
    public string $estado = '';

    public string $erro = '';

    public function submit(): void
    {
        // Anti-spam real no servidor (o /livewire/update não tem throttle):
        // ver RegistoEmpresa — mesma janela e mesma chave por IP.
        $ip = request()->ip() ?: 'desconhecido';
        $chave = 'registo-profissional:'.$ip;

        if (RateLimiter::tooManyAttempts($chave, 10)) {
            $this->estado = 'erro';
            $this->erro = 'Recebemos demasiados pedidos a partir do seu endereço. Tente novamente dentro de um minuto.';

            return;
        }

        RateLimiter::hit($chave, 60);

        $validador = Validator::make(
            [
                'nome_completo' => $this->nome_completo,
                'formacao_academica' => $this->formacao_academica,
                'contactos' => $this->contactos,
                'areas_atuacao' => $this->areas_atuacao,
            ],
            [
                'nome_completo' => 'required|string|max:200',
                'formacao_academica' => 'required|string|max:300',
                'contactos' => 'required|string|max:200',
                'areas_atuacao' => 'required|array|min:1',
                'areas_atuacao.*' => 'string',
            ],
            [
                'nome_completo.required' => 'Indique o seu nome completo.',
                'nome_completo.max' => 'O nome é demasiado longo.',
                'formacao_academica.required' => 'Indique a sua formação académica.',
                'formacao_academica.max' => 'A formação académica é demasiado longa.',
                'contactos.required' => 'Indique os seus contactos (telefone e/ou email).',
                'contactos.max' => 'Os contactos são demasiado longos.',
                'areas_atuacao.required' => 'Selecione pelo menos uma área de atuação.',
            ]
        );

        if ($validador->fails()) {
            $this->estado = 'erro';
            $this->erro = $validador->errors()->first();

            return;
        }

        // Whitelist das áreas (nunca gravar texto arbitrário do POST).
        $areas = AreasAtuacao::filtrar($this->areas_atuacao);

        if ($areas === []) {
            $this->estado = 'erro';
            $this->erro = 'Selecione pelo menos uma área de atuação válida.';

            return;
        }

        Professional::query()->create([
            'nome_completo' => trim($this->nome_completo),
            'formacao_academica' => trim($this->formacao_academica),
            'contactos' => trim($this->contactos),
            'areas_atuacao' => $areas,
            'status' => 'pendente',
        ]);

        $this->estado = 'ok';
        $this->reset('nome_completo', 'formacao_academica', 'contactos', 'areas_atuacao');
    }

    public function render()
    {
        return view('livewire.frontend.registo-profissional', [
            'areasLista' => AreasAtuacao::LISTA,
        ]);
    }
}
