<?php

namespace App\Services;

use App\Models\AiIntegration;
use App\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;

class GeminiClient
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = ImageManager::gd();
    }

    public function enhanceImage(AiIntegration $integration, Media $sourceMedia, string $prompt, array $options = []): array
    {
        if (!$integration->api_key) {
            throw new RuntimeException('Gemini API key belum dikonfigurasi.');
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($sourceMedia->path)) {
            throw new RuntimeException('File media sumber tidak ditemukan.');
        }

        // Resize image before sending to reduce payload size and processing time
        $image = $this->imageManager->read($disk->path($sourceMedia->path));

        // Max 1536px (Gemini's recommended size for best quality/speed balance)
        if ($image->width() > 1536 || $image->height() > 1536) {
            $image->scaleDown(width: 1536, height: 1536);
        }

        // Encode as JPEG with 85% quality for smaller payload
        $encoded = $image->toJpeg(quality: 85);
        $imageBase64 = base64_encode($encoded);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => 'image/jpeg', // Always JPEG for optimized payload
                                'data' => $imageBase64,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = Http::acceptJson()
            ->timeout(120) // 2 minutes timeout for AI processing
            ->connectTimeout(30) // 30 seconds to establish connection
            ->retry(2, 1000) // Retry 2 times with 1 second delay
            ->post($this->endpoint($integration), $payload);

        if ($response->failed()) {
            throw new RuntimeException('Gemini API error: '.$response->body());
        }

        $data = $response->json();
        $parts = data_get($data, 'candidates.0.content.parts', []);
        $inlinePart = null;
        foreach ($parts as $part) {
            if (isset($part['inlineData']['data'])) {
                $inlinePart = $part;
                break;
            }
        }

        $encoded = $inlinePart['inlineData']['data'] ?? null;
        if (!$encoded) {
            throw new RuntimeException('Gemini API response missing image payload.');
        }

        $binary = base64_decode($encoded);
        if ($binary === false) {
            throw new RuntimeException('Gagal mendekode gambar dari Gemini.');
        }

        $mime = $inlinePart['inlineData']['mimeType'] ?? $options['mime'] ?? 'image/png';
        [$binary, $mime] = $this->enforceSquareAspect($binary, $mime, $options);

        $derivedExtension = str_contains($mime, '/') ? Str::after($mime, '/') : $mime;
        $extension = $options['extension'] ?? $derivedExtension ?: 'png';
        $path = 'media/product_photo/'.Str::uuid().".{$extension}";
        $disk->put($path, $binary);

        $meta = $data;
        Arr::forget($meta, ['candidates.0.content.parts']);

        return [
            'path' => $path,
            'mime' => $mime,
            'size' => $disk->size($path),
            'metadata' => $meta,
        ];
    }

    protected function endpoint(AiIntegration $integration): string
    {
        $configured = config('services.gemini.endpoint');
        $base = $configured ? rtrim($configured, '/') : null;

        if (!$base) {
            $model = $integration->model ?: 'gemini-2.5-flash-image-preview';
            $base = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
        }

        $separator = str_contains($base, '?') ? '&' : '?';
        return $base . $separator . 'key=' . urlencode($integration->api_key);
    }

    protected function enforceSquareAspect(string $binary, string $mime, array $options = []): array
    {
        $targetSize = (int) ($options['size_px'] ?? config('aistudio.output_size', 1024));
        $targetSize = $targetSize > 0 ? $targetSize : 1024;

        $image = $this->imageManager->read($binary);
        $image->cover($targetSize, $targetSize);

        $encoded = $this->encodeImage($image, $mime);

        return [$encoded->toString(), $encoded->mediaType()];
    }

    protected function encodeImage(ImageInterface $image, string $preferredMime): EncodedImageInterface
    {
        $preferredMime = strtolower($preferredMime);

        return match (true) {
            str_contains($preferredMime, 'webp') => $image->toWebp(),
            str_contains($preferredMime, 'jpeg') || str_contains($preferredMime, 'jpg') => $image->toJpeg(quality: 88),
            default => $image->toPng(),
        };
    }
}
