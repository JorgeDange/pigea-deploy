<?php

namespace App\Support;

use App\Models\User;
use App\Notifications\EventoPainel;

/**
 * Único ponto de criação de notificações do painel.
 *
 * Cada notificação é enviada a TODOS os utilizadores com role `admin` ou
 * `editor` — quem não precisa recebe zero. Sem dados pessoais; a mensagem
 * descreve o evento e aponta o sítio a agir (`href`).
 */
class Notificador
{
    /**
     * Cria uma notificação (icone, título, texto, href) na caixa de entrada
     * de todos os admins/editores.
     */
    public static function enviarParaEquipa(string $icone, string $titulo, string $texto, string $href): void
    {
        $equipa = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'editor']))->get();

        if ($equipa->isEmpty()) {
            return;
        }

        $notificacao = new EventoPainel([
            'icone' => $icone,
            'titulo' => $titulo,
            'texto' => $texto,
            'href' => $href,
        ]);

        foreach ($equipa as $utilizador) {
            $utilizador->notify($notificacao);
        }
    }
}
