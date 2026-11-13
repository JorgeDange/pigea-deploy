<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Notificação de evento interno para a equipa do painel (admin + editor).
 *
 * Conteúdo em `data` (icone, titulo, texto, href, criado_em):
 *  - seguir o padrão da `partials/notificacoes` e do sino;
 *  - sem dados pessoais;
 *  - sempre com `href` clicável para o sítio a agir.
 */
class EventoPainel extends Notification
{
    /** @param array{icone: string, titulo: string, texto: string, href: string} $dados */
    public function __construct(private array $dados)
    {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, string> */
    public function toArray(object $notifiable): array
    {
        return $this->dados;
    }
}
