<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Fase 4 (cms_v.md) — upload de imagens PARA O CORPO do artigo.
 *
 * POST {ADMIN_PATH}/noticias/imagem (autenticado, can:news.manage).
 * - Valida mimetype real (jpg/jpeg/png/webp), máx. 4 MB.
 * - Guarda em storage/app/public/noticias/corpo/AAAA/MM e — com GD —
 *   redimensiona para largura máxima 1200 px e converte para WebP q85
 *   (config image.webp_quality, por omissão 85).
 * - Rate limit (20 uploads/minuto por utilizador) aplicado na rota.
 * - Textos de erro em português. NÃO usa a Spatie Media Library —
 *   a notícia pode ainda não existir (decisão registada em cms_v.md §Fase 4).
 */
class NoticiaImagemController extends Controller
{
    private const MAX_BYTES = 4 * 1024 * 1024; // 4 MB

    private const LARGURA_IDEAL = 1200; // máx. absoluto; recomendado 800

    public function store(Request $request): JsonResponse
    {
        $ficheiro = $request->file('imagem');

        if ($ficheiro === null || ! $ficheiro->isValid()) {
            return response()->json([
                'message' => 'Ficheiro inválido ou ausente.',
            ], 422);
        }

        if ($ficheiro->getSize() > self::MAX_BYTES) {
            return response()->json([
                'message' => 'A imagem excede o máximo de 4MB.',
            ], 422);
        }

        $mime = $ficheiro->getMimeType();
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (! array_key_exists($mime ?? '', $permitidos)) {
            return response()->json([
                'message' => 'Formato não suportado. Usa JPG, PNG ou WebP.',
            ], 422);
        }

        $nome = $this->processar($ficheiro, $ficheiro->getRealPath(), $permitidos[$mime]);

        return response()->json([
            'location' => asset('storage/' . $nome),
        ]);
    }

    private function processar(\Illuminate\Http\UploadedFile $ficheiro, string $caminho, string $extensao): string
    {
        $caminhoRelativo = 'noticias/corpo/' . date('Y/m') . '/' . uniqid('img_', true) . '.webp';

        if (! extension_loaded('gd')) {
            // GD ausente: guarda o original (não bloqueia o utilizador) e loga.
            Log::warning('GD não está ativo — imagem do corpo guardada sem conversão WebP.');
            $fallback = 'noticias/corpo/' . date('Y/m') . '/' . uniqid('img_', true) . '.' . ($ficheiro->getClientOriginalExtension() ?: 'bin');
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

        [$largura, $altura] = getimagesize($caminho);

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

        // Escreve o WebP para um temporário e persiste via Storage (mockável).
        $temporario = tempnam(sys_get_temp_dir(), 'pigea_img_') . '.webp';
        $qualidade = 85;
        imagewebp($imagem, $temporario, $qualidade);
        imagedestroy($imagem);

        Storage::disk('public')->put($caminhoRelativo, file_get_contents($temporario), 'public');
        @unlink($temporario);

        return $caminhoRelativo;
    }
}
