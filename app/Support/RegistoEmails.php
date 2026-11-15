<?php

namespace App\Support;

use App\Mail\RegistoAprovado;
use App\Mail\RegistoConfirmacao;
use App\Mail\RegistoRejeitado;
use App\Models\Company;
use App\Models\Professional;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envio dos emails transacionais do Diretório (confirmação de submissão e
 * decisão da equipa interna).
 *
 * Regras de segurança:
 *  - Usa sempre o mailer `geral` (From: geral@pigea.ao), nunca a stream da
 *    newsletter (mailer por omissão) — streams separadas por completo;
 *  - Falhas de email nunca rebentam a operação do site (registo/decisão) —
 *    regista Log::warning e segue em frente;
 *  - Registos antigos sem email na coluna (nullable) são ignorados em silêncio.
 */
final class RegistoEmails
{
    public static function confirmacao(Company|Professional $item): void
    {
        self::enviar($item, fn () => new RegistoConfirmacao(self::tipo($item), self::nome($item)));
    }

    public static function aprovado(Company|Professional $item): void
    {
        $directorioUrl = route('directory.index');

        self::enviar($item, fn () => new RegistoAprovado(self::tipo($item), self::nome($item), $directorioUrl));
    }

    public static function rejeitado(Company|Professional $item): void
    {
        self::enviar($item, fn () => new RegistoRejeitado(self::tipo($item), self::nome($item)));
    }

    /** @param Company|Professional $item */
    private static function enviar(Company|Professional $item, callable $mailable): void
    {
        try {
            $email = trim((string) $item->email);

            if ($email === '') {
                Log::info('[registo] candidatura #'.$item->getTable().'/'.$item->id.' sem email — notificação por email ignorada.');

                return;
            }

            Mail::mailer('geral')->to($email)->send($mailable());
        } catch (\Throwable $e) {
            Log::warning('[registo] falha ao enviar email ('.$item->getTable().'/'.$item->id.'): '.$e->getMessage());
        }
    }

    private static function tipo(Company|Professional $item): string
    {
        return $item instanceof Company ? 'empresa' : 'profissional';
    }

    private static function nome(Company|Professional $item): string
    {
        return $item instanceof Company
            ? (string) $item->nome_empresa
            : (string) $item->nome_completo;
    }
}
