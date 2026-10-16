<?php

namespace App\Livewire\Frontend;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Formulário de contacto de `/contactos` (fase.md §2.2 — "formulários sem
 * ação → formulários funcionais (Laravel Validation)").
 *
 * Mantém o HTML do protótipo tal como está (incl. `action="#"` e os ids
 * `contactform`/`message` de que o `scripts.js` original depende) e mostra a
 * **mesma** mensagem de sucesso que o script estático mostrava.
 */
class ContactForm extends Component
{
    /** Email público que o próprio protótipo indica na página de contactos. */
    private const DESTINATARIO = 'geral@pigea.ao';

    public string $name = '';

    public string $email = '';

    public string $comments = '';

    /** `''` (inicial), `'ok'` ou `'erro'` — conteúdo do `#message`. */
    public string $estado = '';

    public string $erro = '';

    public function enviar(): void
    {
        $validador = Validator::make(
            [
                'name' => $this->name,
                'email' => $this->email,
                'comments' => $this->comments,
            ],
            [
                'name' => 'required|string|max:150',
                'email' => 'required|email|max:150',
                'comments' => 'nullable|string|max:3000',
            ],
            [
                'name.required' => 'Indique o seu nome.',
                'name.max' => 'O nome é demasiado longo.',
                'email.required' => 'Indique o seu email.',
                'email.email' => 'O email indicado não é válido.',
                'email.max' => 'O email é demasiado longo.',
                'comments.max' => 'A mensagem é demasiado longa.',
            ]
        );

        if ($validador->fails()) {
            $this->estado = 'erro';
            $this->erro = $validador->errors()->first();

            return;
        }

        try {
            Mail::raw(
                "Nome: {$this->name}\nEmail: {$this->email}\n\n{$this->comments}",
                fn ($mensagem) => $mensagem
                    ->to(self::DESTINATARIO)
                    ->subject("Mensagem pelo site PIGEA — {$this->name}")
            );
        } catch (\Throwable $e) {
            $this->estado = 'erro';
            $this->erro = 'Não foi possível enviar a mensagem. Tente novamente mais tarde.';

            return;
        }

        // Como o `scripts.js` também fazia: limpar os campos depois de enviar.
        $this->estado = 'ok';
        $this->erro = '';
        $this->name = '';
        $this->email = '';
        $this->comments = '';
    }

    public function render()
    {
        return view('livewire.frontend.contact-form');
    }
}
