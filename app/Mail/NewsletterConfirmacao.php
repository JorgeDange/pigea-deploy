<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de "subscrição confirmada" — HTML com identidade PIGEA.
 */
class NewsletterConfirmacao extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscrição Confirmada — PIGEA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.confirmacao',
            with: [
                'unsubscribeUrl' => route('newsletter.cancelar', ['token' => $this->subscriber->token]),
            ],
        );
    }
}
