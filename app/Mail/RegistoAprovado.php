<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email transacional "O seu registo foi aprovado" — enviado ao candidato
 * quando a equipa interna aprova o Diretório (empresas/profissionais).
 * From: geral@pigea.ao (mailer `geral`).
 */
class RegistoAprovado extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $tipo,           // 'empresa' | 'profissional'
        public string $nome,
        public string $directorioUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.geral_from.address'),
                config('mail.geral_from.name'),
            ),
            subject: 'O seu registo foi aprovado — PIGEA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registo.aprovado',
            with: [
                'tipo' => $this->tipo,
                'nome' => $this->nome,
                'directorioUrl' => $this->directorioUrl,
            ],
        );
    }
}
