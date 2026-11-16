<?php

namespace App\Mail;

use App\Models\News;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Email «Aviso de novo artigo» (Fase N2 — envio manual pelo painel).
 *
 * Grelha: [capa] título + excerto + botão «Ler no site». Remetente =
 * mailer da newsletter (newsletter@pigea.ao, stream separada do contacto).
 * Cada email leva o link de cancelamento único do destinatário.
 */
class NewsletterArtigo extends Mailable
{
    use Queueable;

    public function __construct(
        public News $artigo,
        public NewsletterSubscriber $subscriber,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->artigo->title.' — PIGEA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.artigo',
            with: [
                'artigo' => $this->artigo,
                'lerUrl' => route('news.show', $this->artigo->slug),
                'unsubscribeUrl' => route('newsletter.cancelar', ['token' => $this->subscriber->token]),
                'coverUrl' => image_url($this->artigo->image, 'images/logo.webp'),
            ],
        );
    }
}
