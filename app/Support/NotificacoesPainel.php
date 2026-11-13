<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Notificações do painel — módulo real (opção B, 2026-11-13).
 *
 * Lê a tabela `notifications` (Laravel polymorphic), scoped ao utilizador
 * autenticado. Devolve a mesma forma de antes (`icone/titulo/texto/tempo/
 * href`) + `lida`, consumida pelo partial e pelo sino de notificações —
 * nunca devolve dados de outros utilizadores.
 */
class NotificacoesPainel
{
    /** @param \App\Models\User|null $user Por omissão: o utilizador autenticado. */
    public static function obter(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        if ($user === null) {
            return [];
        }

        /** @var Collection<int, DatabaseNotification> $notificacoes */
        $notificacoes = $user->notifications()->latest('created_at')->latest('id')->limit(8)->get();

        return $notificacoes->map(fn (DatabaseNotification $n) => [
            'id' => strtolower((string) $n->id),
            'icone' => $n->data['icone'] ?? 'fa-bell',
            'titulo' => $n->data['titulo'] ?? 'Notificação',
            'texto' => $n->data['texto'] ?? '',
            'tempo' => $n->created_at?->diffForHumans(),
            'href' => $n->data['href'] ?? null,
            'lida' => $n->read_at !== null,
        ])->all();
    }

    /** Quantidade de notificações por ler para o utilizador autenticado. */
    public static function naoLidas(?User $user = null): int
    {
        $user = $user ?? auth()->user();

        if ($user === null) {
            return 0;
        }

        return $user->unreadNotifications()->count();
    }
}
