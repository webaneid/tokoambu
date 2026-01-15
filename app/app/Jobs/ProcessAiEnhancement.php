<?php

namespace App\Jobs;

use App\Models\AiIntegration;
use App\Models\AiLog;
use App\Models\Media;
use App\Services\GeminiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiEnhancement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $aiLogId)
    {
    }

    public function handle(GeminiClient $geminiClient): void
    {
        $log = AiLog::find($this->aiLogId);
        if (!$log) {
            return;
        }

        if ($log->status === 'done') {
            return;
        }

        $integration = AiIntegration::active('gemini');
        if (!$integration) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Integrasi Gemini belum diaktifkan.',
            ]);
            return;
        }

        $sourceMedia = $log->sourceMedia;
        if (!$sourceMedia) {
            $log->update([
                'status' => 'failed',
                'error_message' => 'Media sumber tidak ditemukan.',
            ]);
            return;
        }

        $payload = $log->request_payload ?? [];
        $options = [
            'background_color' => $payload['background_color'] ?? $integration->default_bg_color,
            'use_solid_background' => (bool) ($payload['use_solid'] ?? $integration->use_solid_background),
            'features' => $payload['features'] ?? [],
        ];

        try {
            $result = $geminiClient->enhanceImage(
                $integration,
                $sourceMedia,
                $log->prompt ?? '',
                $options
            );
        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('AI enhancement failed', [
                'ai_log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        $newMedia = Media::create([
            'type' => 'product_photo',
            'filename' => 'ai-'.$sourceMedia->filename,
            'path' => $result['path'],
            'mime' => $result['mime'],
            'size' => $result['size'],
            'metadata' => array_merge(
                $sourceMedia->metadata ?? [],
                [
                    'source' => 'ai',
                    'ai_log_id' => $log->id,
                    'options' => $options,
                ]
            ),
            'uploaded_by' => $log->requested_by,
            'product_id' => $sourceMedia->product_id,
            'gallery_order' => 0,
            'purchase_id' => $sourceMedia->purchase_id,
            'payment_id' => $sourceMedia->payment_id,
            'purchase_payment_id' => $sourceMedia->purchase_payment_id,
        ]);

        $log->update([
            'status' => 'done',
            'result_media_id' => $newMedia->id,
            'response_meta' => $result['metadata'] ?? null,
            'error_message' => null,
        ]);
    }
}
