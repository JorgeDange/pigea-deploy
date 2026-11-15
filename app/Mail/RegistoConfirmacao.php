<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email transacional "O seu pedido foi recebido — em processamento"
 * enviado ao candidato logo após a submissão pública do Diretório
 * (empresa ou profissional). From: geral@pigea.ao (mailer `geral`).
 */
class RegistoConfirmacao extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $tipo,   // 'empresa' | 'profissional'
        public string $nome,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.geral_from.address'),
                config('mail.geral_from.name'),
            ),
            subject: 'Recebemos o seu registo — PIGEA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registo.confirmacao',
            with: [
                'tipo' => $this->tipo,
                'nome' => $this->nome,
            ],
        );
    }
}
