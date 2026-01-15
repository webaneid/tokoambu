<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAiEnhancement;
use App\Models\AiIntegration;
use App\Models\AiLog;
use App\Models\Media;
use App\Services\AiStudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiGatewayController extends Controller
{
    public function __construct(protected AiStudioService $aiStudioService)
    {
    }

    public function features(): JsonResponse
    {
        $integration = AiIntegration::active('gemini');
        $features = $this->aiStudioService->getAvailableFeatures();

        return response()->json([
            'enabled' => (bool) $integration,
            'defaults' => [
                'background_color' => $integration?->default_bg_color ?? '#FFFFFF',
                'use_solid_background' => $integration?->use_solid_background ?? true,
            ],
            'features' => $features->groupBy('category')->map(fn ($items) => $items->values())->toArray(),
        ]);
    }

    public function enhance(Request $request): JsonResponse
    {
        $integration = AiIntegration::active('gemini');
        if (!$integration || !$integration->api_key) {
            abort(503, 'Integrasi Gemini belum dikonfigurasi.');
        }

        $validated = $request->validate([
            'media_id' => ['required', 'exists:media,id'],
            'features' => ['nullable', 'array'],
            'features.*' => ['boolean'],
            'background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'use_solid' => ['required', 'boolean'],
        ]);

        $media = Media::findOrFail($validated['media_id']);
        $this->authorizeMediaAccess($media);

        $availableKeys = $this->aiStudioService->getAvailableFeatures()->pluck('key')->toArray();
        $selectedFeatures = collect($validated['features'] ?? [])
            ->filter(function ($isActive, $key) use ($availableKeys) {
                return (bool) $isActive && in_array($key, $availableKeys, true);
            })
            ->toArray();

        $prompt = $this->aiStudioService->buildFinalPrompt(
            $selectedFeatures,
            $validated['background_color'],
            (bool) $validated['use_solid']
        );

        $log = AiLog::create([
            'provider' => 'gemini',
            'requested_by' => Auth::id(),
            'source_media_id' => $media->id,
            'prompt' => $prompt,
            'request_payload' => [
                'features' => $selectedFeatures,
                'background_color' => $validated['background_color'],
                'use_solid' => (bool) $validated['use_solid'],
            ],
            'status' => 'queued',
        ]);

        ProcessAiEnhancement::dispatch($log->id);

        // Refresh status after dispatch (may have been processed synchronously)
        $log->refresh();

        return response()->json([
            'job_id' => $log->id,
            'status' => $log->status,
        ]);
    }

    public function show(AiLog $aiLog): JsonResponse
    {
        $this->authorizeLogAccess($aiLog);

        return response()->json([
            'id' => $aiLog->id,
            'status' => $aiLog->status,
            'error' => $aiLog->error_message,
            'result_media_id' => $aiLog->result_media_id,
            'prompt' => $aiLog->prompt,
            'requested_at' => $aiLog->created_at,
            'response_meta' => $aiLog->response_meta,
        ]);
    }

    protected function authorizeMediaAccess(Media $media): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->hasRole('Super Admin')) {
            return;
        }

        if ($media->type === 'payment_proof') {
            abort_unless($user->hasRole('Finance'), 403);
            return;
        }

        if ($media->type === 'shipment_proof') {
            abort_unless($user->hasRole('Warehouse') || $user->hasRole('Admin'), 403);
            return;
        }
    }

    protected function authorizeLogAccess(AiLog $log): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->hasRole('Super Admin')) {
            return;
        }

        abort_unless($log->requested_by === $user->id, 403);
    }
}
