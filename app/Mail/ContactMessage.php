<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de "Mensagem do site" (formulário de contacto de /contactos) —
 * HTML com identidade PIGEA (cabeçalho escuro + barra vermelha) para que o
 * destinatário reconheça a origem e não confunda com spam genérico.
 *
 * `replyTo` = remetente: quem está no destino responde ao autor da mensagem,
 * não a `newsletter@pigea.ao`.
 */
class ContactMessage extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $nome,
        public string $email,
        public string $mensagem,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mensagem pelo site PIGEA — '.$this->nome,
            replyTo: [$this->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contacto',
            text: 'emails.contacto-plain',
        );
    }
}
