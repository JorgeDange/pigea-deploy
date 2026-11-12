<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Fase 16 (/admin pro) — upload assíncrono das imagens de publicidade.
 *
 * POST {ADMIN_PATH}/banner/imagem — autenticado, can:banner.manage.
 * - Valida mimetype real (jpg/jpeg/png/webp), máx. 4 MB.
 * - Converte para WebP q85 e redimensiona para largura máxima 1600 px.
 * - Guarda em `storage/media/banners/AAAA/MM/` (path neutro para não ser
 *   interceptado por bloqueadores de anúncios com palavras-chave «ad»).
 * - Devolve o URL público e o caminho relativo.
 * - Rate limit aplicado na rota. Mensagens em português.
 */
class PublicidadeImagemController extends Controller
{
    private const MAX_BYTES = 4 * 1024 * 1024; // 4 MB

    private const LARGURA_IDEAL = 1600; // banner pode ser largo (970x250)

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('banner.manage');

        $ficheiro = $request->file('imagem');

        if ($ficheiro === null || ! $ficheiro->isValid()) {
            return response()->json(['message' => 'Ficheiro inválido ou ausente.'], 422);
        }

        if ($ficheiro->getSize() > self::MAX_BYTES) {
            return response()->json(['message' => 'A imagem excede o máximo de 4MB.'], 422);
        }

        $mime = $ficheiro->getMimeType();
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (! array_key_exists($mime ?? '', $permitidos)) {
            return response()->json(['message' => 'Formato não suportado. Usa JPG, PNG ou WebP.'], 422);
        }

        $nome = $this->processar($ficheiro, $ficheiro->getRealPath(), $permitidos[$mime]);

        return response()->json([
            'location' => 'storage/' . $nome,
            'url' => asset('storage/' . $nome),
        ]);
    }

    /**
     * Converte a imagem enviada para WebP, redimensiona e grava no disco
     * `public`, em `media/banners/AAAA/MM`.
     */
    private function processar(\Illuminate\Http\UploadedFile $ficheiro, string $caminho, string $extensao): string
    {
        $caminhoRelativo = 'media/banners/' . date('Y/m') . '/' . uniqid('img_', true) . '.webp';

        if (! extension_loaded('gd')) {
            Log::warning('GD não está ativo — imagem guardada sem conversão WebP.');
            $fallback = 'media/banners/' . date('Y/m') . '/' . uniqid('img_', true) . '.' . ($ficheiro->getClientOriginalExtension() ?: 'bin');
            Storage::disk('public')->put($fallback, $ficheiro->get(), 'public');

            return $fallback;
        }

        $imagem = match ($extensao) {
            'jpg' => imagecreatefromjpeg($caminho),
            'png' => imagecreatefrompng($caminho),
            'webp' => imagecreatefromwebp($caminho),
        };

        if ($imagem === false) {
            throw new \RuntimeException('Não foi possível ler a imagem.');
        }

        [$largura, $altura] = getimagesize($caminho) ?: [0, 0];

        if ($largura > self::LARGURA_IDEAL) {
            $novaLargura = self::LARGURA_IDEAL;
            $novaAltura = (int) round($altura * $novaLargura / $largura);
            $reduzida = imagecreatetruecolor($novaLargura, $novaAltura);
            imagealphablending($reduzida, false);
            imagesavealpha($reduzida, true);
            imagecopyresampled($reduzida, $imagem, 0, 0, 0, 0, $novaLargura, $novaAltura, $largura, $altura);
            imagedestroy($imagem);
            $imagem = $reduzida;
        }

        $temporario = tempnam(sys_get_temp_dir(), 'pigea_pub_') . '.webp';
        imagewebp($imagem, $temporario, 85);
        imagedestroy($imagem);

        Storage::disk('public')->put($caminhoRelativo, file_get_contents($temporario), 'public');
        @unlink($temporario);

        return $caminhoRelativo;
    }
}
