<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de subscrição (pós formulário público, dupla opt-in) — HTML com identidade PIGEA.
 * Contém 2 acções: confirmar e cancelar (o utilizador pode atuar das duas).
 */
class NewsletterSubscricao extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirme a sua subscrição — PIGEA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.subscricao',
            with: [
                'confirmUrl' => route('newsletter.confirmar', ['token' => $this->subscriber->token]),
                'cancelUrl' => route('newsletter.cancelar', ['token' => $this->subscriber->token]),
            ],
        );
    }
}
