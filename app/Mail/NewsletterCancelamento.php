<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de "subscrição cancelada" — HTML com identidade PIGEA.
 * Contém uma ação única: voltar a subscrever.
 */
class NewsletterCancelamento extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscrição Cancelada — PIGEA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.cancelamento',
            with: [
                'resubscribeUrl' => route('newsletter.resubscrever', ['token' => $this->subscriber->token]),
            ],
        );
    }
}
