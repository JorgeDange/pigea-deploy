<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Assinatura de autor das notícias (autor.md).
 *
 * Responsabilidades:
 *  - resolver o autor pela regra de prioridade (autor.md §2.3):
 *      1) author_id → utilizador interno (nome, avatar);
 *      2) senão guest_author_name → convidado (nome, guest_author_avatar);
 *      3) senão "Equipa Editorial PIGEA" + logo padrão;
 *  - gerar o URL completo da foto (path local `storage/…` OU link externo);
 *  - converter um upload para miniatura 150x150 (autor.md §2.1) com GD.
 *
 * Performance (autor.md — "não pode afetar o desempenho"):
 *  - tudo corre dentro do request do artigo; sem jobs/filas obrigatórias;
 *  - miniatura 150x150 de poucos KB — leitura barata no show (uma imagem por página);
 *  - `author` faz eager loading nos controladores (NewsController).
 */
class Autor
{
    /** Regra de prioridade do autor.md §2.3. @return array{name: string, avatar: string, interno: bool} */
    public static function assinatura(mixed $noticia): array
    {
        if ($noticia->author_id && $noticia->author !== null) {
            return [
                'name' => $noticia->author->name,
                'avatar' => $noticia->author->avatar,
                'interno' => true,
            ];
        }

        if (! empty($noticia->guest_author_name)) {
            return [
                'name' => $noticia->guest_author_name,
                'avatar' => $noticia->guest_author_avatar,
                'interno' => false,
            ];
        }

        return ['name' => 'Equipa Editorial PIGEA', 'avatar' => null, 'interno' => false];
    }

    /** URL absoluto da foto — aceita path local (`storage/…`) OU link externo (http…). */
    public static function avatarUrl(?string $avatar): string
    {
        if (! $avatar) {
            return asset('images/logo.png'); // fallback autor.md §3
        }

        return str_starts_with($avatar, 'http') ? $avatar : asset($avatar);
    }

    /**
     * Grava upload como miniatura quadrada 150x150 em `storage/app/public/avatars`.
     * Devolve o caminho público relativo para passar a `asset()` (ex.: "storage/avatars/x.jpg").
     */
    public static function guardarMiniatura(UploadedFile $ficheiro, string $prefixo): string
    {
        $mime = (string) $ficheiro->getMimeType();

        $origem = match (true) {
            str_contains($mime, 'png') => @imagecreatefrompng($ficheiro->getRealPath()),
            str_contains($mime, 'webp') => @imagecreatefromwebp($ficheiro->getRealPath()),
            default => @imagecreatefromjpeg($ficheiro->getRealPath()),
        };

        if (! $origem) {
            throw new \RuntimeException('Não foi possível ler a imagem enviada.');
        }

        $larg = imagesx($origem);
        $alt = imagesy($origem);
        $lado = min($larg, $alt);
        $corteX = (int) (($larg - $lado) / 2);
        $corteY = (int) (($alt - $lado) / 2);

        $destino = imagecreatetruecolor(150, 150);
        imagealphablending($destino, false);
        imagesavealpha($destino, true);
        imagecopyresampled($destino, $origem, 0, 0, $corteX, $corteY, 150, 150, $lado, $lado);

        $nome = $prefixo . '-' . Str::random(12) . '.png';
        $relativo = 'avatars/' . $nome;

        ob_start();
        imagepng($destino, null, 6);
        $bytes = ob_get_clean();

        Storage::disk('public')->put($relativo, $bytes);

        imagedestroy($origem);
        imagedestroy($destino);

        return 'storage/' . $relativo;
    }

    /** Apaga um avatar local antigo (best-effort — ficheiros externos nunca são tocados). */
    public static function apagarLocal(?string $avatar): void
    {
        if ($avatar && str_starts_with($avatar, 'storage/avatars/')) {
            $relativo = substr($avatar, strlen('storage/'));
            if (Storage::disk('public')->exists($relativo)) {
                Storage::disk('public')->delete($relativo);
            }
        }
    }
}
