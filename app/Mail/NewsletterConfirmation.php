<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de confirmação da subscrição (dupla opt-in) — FASE N1.
 *
 * Texto simples, sem imagens nem HTML: chega corretamente a qualquer mail
 * client. Contém uma única ação — o link de confirmação, que usa o token
 * aleatório de 64 carateres gravado nesta mesma subscrição.
 */
class NewsletterConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirma a tua subscrição na newsletter do PIGEA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter-confirmacao',
            with: [
                'urlConfirmacao' => route('newsletter.confirmar', ['token' => $this->subscriber->token]),
                'urlCancelamento' => route('newsletter.cancelar', ['token' => $this->subscriber->token]),
            ],
        );
    }
}
