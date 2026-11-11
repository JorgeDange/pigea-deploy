<?php

namespace App\Livewire\Frontend;

use App\Models\Company;
use App\Support\AreasAtuacao;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * Formulário público de registo de empresa (restrutura.md §4.1).
 *
 * Sem login: qualquer empresa submete e o registo fica `pendente` até a
 * equipa interna aprovar/rejeitar no /admin (Fase 4). Sucesso mostra a
 * mensagem exata definida pelo cliente.
 */
class RegistoEmpresa extends Component
{
    public string $nome_empresa = '';

    public string $nif = '';

    public string $contactos = '';

    /** @var list<string> áreas de atuação submetidas (checkboxes) */
    public array $areas_atuacao = [];

    /** Redes sociais (opcionais) — rede => URL. */
    public string $facebook = '';

    public string $linkedin = '';

    public string $instagram = '';

    /** `''` (formulário), `'ok'` (sucesso) ou `'erro'`. */
    public string $estado = '';

    public string $erro = '';

    public function submit(): void
    {
        // Anti-spam real no servidor (o /livewire/update não tem throttle):
        // 10 submissões/min por IP e formulário — folgado para não bloquear
        // utilização legítima; a moderação no /admin e a unique de NIF são o
        // último backstop.
        $ip = request()->ip() ?: 'desconhecido';
        $chave = 'registo-empresa:'.$ip;

        if (RateLimiter::tooManyAttempts($chave, 10)) {
            $this->estado = 'erro';
            $this->erro = 'Recebemos demasiados pedidos a partir do seu endereço. Tente novamente dentro de um minuto.';

            return;
        }

        RateLimiter::hit($chave, 60);

        $validador = Validator::make(
            [
                'nome_empresa' => $this->nome_empresa,
                'nif' => $this->nif,
                'contactos' => $this->contactos,
                'areas_atuacao' => $this->areas_atuacao,
                'facebook' => $this->facebook,
                'linkedin' => $this->linkedin,
                'instagram' => $this->instagram,
            ],
            [
                'nome_empresa' => 'required|string|max:200',
                'nif' => 'required|string|max:30|unique:companies,nif',
                'contactos' => 'required|string|max:200',
                'areas_atuacao' => 'required|array|min:1',
                'areas_atuacao.*' => 'string',
                'facebook' => 'nullable|url:http,https|max:200',
                'linkedin' => 'nullable|url:http,https|max:200',
                'instagram' => 'nullable|url:http,https|max:200',
            ],
            [
                'nome_empresa.required' => 'Indique o nome da empresa.',
                'nome_empresa.max' => 'O nome da empresa é demasiado longo.',
                'nif.required' => 'Indique o NIF da empresa.',
                'nif.max' => 'O NIF é demasiado longo.',
                'nif.unique' => 'Já existe um registo com este NIF.',
                'contactos.required' => 'Indique os contactos (telefone e/ou email).',
                'contactos.max' => 'Os contactos são demasiado longos.',
                'areas_atuacao.required' => 'Selecione pelo menos uma área de atuação.',
                'facebook.url' => 'O link do Facebook não é um URL válido.',
                'linkedin.url' => 'O link do LinkedIn não é um URL válido.',
                'instagram.url' => 'O link do Instagram não é um URL válido.',
            ]
        );

        if ($validador->fails()) {
            $this->estado = 'erro';
            $this->erro = $validador->errors()->first();

            return;
        }

        // Whitelist das áreas (o cliente só envia checkboxes, mas o POST é
        // manipulável — nunca gravar texto arbitrário).
        $areas = AreasAtuacao::filtrar($this->areas_atuacao);

        if ($areas === []) {
            $this->estado = 'erro';
            $this->erro = 'Selecione pelo menos uma área de atuação válida.';

            return;
        }

        $redes = array_filter([
            'facebook' => $this->facebook,
            'linkedin' => $this->linkedin,
            'instagram' => $this->instagram,
        ], fn ($url) => filled($url));

        try {
            Company::query()->create([
                'nome_empresa' => trim($this->nome_empresa),
                'nif' => trim($this->nif),
                'contactos' => trim($this->contactos),
                'areas_atuacao' => $areas,
                'redes_sociais' => $redes === [] ? null : $redes,
                'status' => 'pendente',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Corrida com o mesmo NIF: a validação `unique:` passa e a chave
            // única da BD apanha — responder com a mensagem pt do formulário,
            // nunca um 500. Qualquer outra exceção sobe normalmente.
            $msg = strtolower($e->getMessage());
            if (str_contains($msg, 'unique') || str_contains($msg, 'duplicate')) {
                $this->estado = 'erro';
                $this->erro = 'Já existe um registo com este NIF.';

                return;
            }

            throw $e;
        }

        // Mensagem exata definida pelo cliente (restrutura.md §4.1).
        $this->estado = 'ok';
        $this->reset('nome_empresa', 'nif', 'contactos', 'areas_atuacao', 'facebook', 'linkedin', 'instagram');
    }

    public function render()
    {
        return view('livewire.frontend.registo-empresa', [
            'areasLista' => AreasAtuacao::LISTA,
        ]);
    }
}
