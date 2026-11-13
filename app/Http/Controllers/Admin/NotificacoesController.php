<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

/**
 * Notificações do painel (módulo real, opção B — 2026-11-13).
 *
 * POST /gestao-pigea/notificacoes/ler — marca todas as notificações do
 * utilizador autenticado (admin ou editor) como lidas. Sem body — a ação
 * é idempotente.
 */
class NotificacoesController extends Controller
{
    public function lerTodas(): RedirectResponse
    {
        $user = auth()->user();

        if ($user !== null) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return back();
    }
}
