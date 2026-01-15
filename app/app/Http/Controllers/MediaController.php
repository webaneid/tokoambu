<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\AiIntegration;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    protected array $types = ['payment_proof', 'product_photo', 'shipment_proof', 'banner_image'];

    public function index(Request $request)
    {
        $requestedType = $request->get('type');
        $type = $this->resolveType($requestedType);
        $this->authorizeType($type);

        $query = Media::with('uploader')
            ->where('type', $type)
            ->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                    ->orWhere('mime', 'like', "%{$search}%");
            });
        }

        $media = $query->paginate(24)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $media->items(),
                'type' => $type,
            ]);
        }

        $aiIntegration = AiIntegration::active('gemini');

        return view('media.index', [
            'media' => $media,
            'type' => $type,
            'types' => $this->types,
            'search' => $search,
            'aiEnabled' => (bool) $aiIntegration,
            'aiDefaults' => [
                'background_color' => $aiIntegration?->default_bg_color ?? '#FFFFFF',
                'use_solid_background' => $aiIntegration?->use_solid_background ?? true,
            ],
        ]);
    }

    public function listPaymentProof()
    {
        $this->authorizeType('payment_proof');

        $media = Media::paymentProof()
            ->latest()
            ->take(40)
            ->get(['id', 'filename', 'path', 'mime', 'size', 'created_at']);

        return response()->json([
            'data' => $media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'filename' => $m->filename,
                    'mime' => $m->mime,
                    'size' => $m->size,
                    'created_at' => $m->created_at->toDateTimeString(),
                    'url' => $m->url,
                ];
            }),
        ]);
    }

    public function listProductPhoto()
    {
        $this->authorizeType('product_photo');

        $media = Media::productPhoto()
            ->latest()
            ->take(80)
            ->get(['id', 'filename', 'path', 'mime', 'size', 'created_at']);

        return response()->json([
            'data' => $media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'filename' => $m->filename,
                    'mime' => $m->mime,
                    'size' => $m->size,
                    'created_at' => $m->created_at->toDateTimeString(),
                    'url' => $m->url,
                ];
            }),
        ]);
    }

    public function listShipmentProof()
    {
        $this->authorizeType('shipment_proof');

        $media = Media::where('type', 'shipment_proof')
            ->latest()
            ->take(40)
            ->get(['id', 'filename', 'path', 'mime', 'size', 'created_at']);

        return response()->json([
            'data' => $media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'filename' => $m->filename,
                    'mime' => $m->mime,
                    'size' => $m->size,
                    'created_at' => $m->created_at->toDateTimeString(),
                    'url' => $m->url,
                ];
            }),
        ]);
    }

    public function listBannerImage()
    {
        $this->authorizeType('banner_image');

        $media = Media::where('type', 'banner_image')
            ->latest()
            ->take(40)
            ->get(['id', 'filename', 'path', 'mime', 'size', 'created_at']);

        return response()->json([
            'data' => $media->map(function ($m) {
                return [
                    'id' => $m->id,
                    'filename' => $m->filename,
                    'mime' => $m->mime,
                    'size' => $m->size,
                    'created_at' => $m->created_at->toDateTimeString(),
                    'url' => $m->url,
                ];
            }),
        ]);
    }

    public function store(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in($this->types)],
            'file' => ['nullable', 'file', 'max:10240'],
            'files' => ['nullable', 'array', 'max:20'],
            'files.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp'],
            'product_id' => ['nullable', 'exists:products,id'],
            'purchase_id' => ['nullable', 'exists:purchases,id'],
            'payment_id' => ['nullable', 'exists:payments,id'],
            'purchase_payment_id' => ['nullable', 'exists:purchase_payments,id'],
            'gallery_order' => ['nullable', 'integer'],
        ]);

        $this->authorizeType($validated['type']);

        $folder = match ($validated['type']) {
            'payment_proof' => 'media/payment_proof',
            'shipment_proof' => 'media/shipment_proof',
            'banner_image' => 'media/banner_image',
            default => 'media/product_photo',
        };

        // Support both single file and multiple files upload
        $files = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        }

        if (empty($files)) {
            return back()->withErrors(['file' => 'Tidak ada file yang diunggah.']);
        }

        $uploadedMedia = [];

        foreach ($files as $index => $file) {
            // Process image based on type
            if ($validated['type'] === 'product_photo') {
                // Crop to 1:1 ratio + resize to 1080x1080 + convert to WebP
                $processed = $imageService->processProductPhoto($file, $folder);
            } elseif ($validated['type'] === 'banner_image') {
                // Banner images: no crop, keep original dimensions
                $processed = $imageService->processBannerImage($file, $folder);
            } else {
                // For payment_proof and shipment_proof: convert to WebP but don't crop
                $processed = $imageService->processProofImage($file, $folder);
            }

            $media = Media::create([
                'type' => $validated['type'],
                'filename' => $file->getClientOriginalName(),
                'path' => $processed['path'],
                'mime' => $processed['mime'],
                'size' => $processed['size'],
                'metadata' => [
                    'extension' => $processed['extension'],
                    'original_mime' => $file->getClientMimeType(),
                ],
                'uploaded_by' => Auth::id(),
                'product_id' => $validated['product_id'] ?? null,
                'gallery_order' => isset($validated['gallery_order']) ? ($validated['gallery_order'] + $index) : $index,
                'purchase_id' => $validated['purchase_id'] ?? null,
                'payment_id' => $validated['payment_id'] ?? null,
                'purchase_payment_id' => $validated['purchase_payment_id'] ?? null,
            ]);

            $uploadedMedia[] = [
                'id' => $media->id,
                'url' => $media->url,
                'filename' => $media->filename,
                'mime' => $media->mime,
                'size' => $media->size,
                'created_at' => $media->created_at,
                'gallery_order' => $media->gallery_order,
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => count($uploadedMedia) > 1
                    ? count($uploadedMedia) . ' media berhasil diunggah'
                    : 'Media berhasil diunggah',
                'media' => count($uploadedMedia) === 1 ? $uploadedMedia[0] : $uploadedMedia,
            ], 201);
        }

        $successMessage = count($uploadedMedia) > 1
            ? count($uploadedMedia) . ' file berhasil diunggah'
            : 'Media berhasil diunggah';

        return redirect()->route('media.index', ['type' => $validated['type']])
            ->with('success', $successMessage);
    }

    public function destroy(Media $media)
    {
        $this->authorizeType($media->type);
        $this->authorizeOwnership($media);

        Storage::disk('public')->delete($media->path);
        $media->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Media dihapus']);
        }

        return redirect()->back()->with('success', 'Media dihapus');
    }

    public function updateGalleryOrder(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:media,id',
            'orders.*.order' => 'required|integer',
        ]);

        foreach ($validated['orders'] as $item) {
            Media::where('id', $item['id'])
                ->where('type', 'product_photo')
                ->update(['gallery_order' => $item['order']]);
        }

        return response()->json(['message' => 'Urutan gallery berhasil diperbarui']);
    }

    private function resolveType(?string $requested): string
    {
        if ($requested && in_array($requested, $this->types, true) && $this->canAccessType($requested)) {
            return $requested;
        }

        // Prioritaskan akses yang tersedia
        foreach ($this->types as $type) {
            if ($this->canAccessType($type)) {
                return $type;
            }
        }

        abort(403);
    }

    private function canAccessType(string $type): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($type === 'payment_proof') {
            return $user->hasRole('Finance');
        }

        if ($type === 'shipment_proof') {
            return $user->hasRole('Admin') || $user->hasRole('Warehouse') || $user->hasRole('Super Admin');
        }

        if ($type === 'banner_image') {
            return $user->hasRole('Admin') || $user->hasRole('Super Admin');
        }

        // product_photo accessible to all authenticated
        return true;
    }

    private function authorizeType(string $type): void
    {
        abort_unless($this->canAccessType($type), 403);
    }

    private function authorizeOwnership(Media $media): void
    {
        $user = Auth::user();
        if ($user->hasRole('Super Admin')) {
            return;
        }

        if ($media->type === 'payment_proof') {
            abort_unless($user->hasRole('Finance'), 403);
            return;
        }

        if ($media->type === 'shipment_proof') {
            abort_unless($user->hasRole('Admin') || $user->hasRole('Warehouse'), 403);
            return;
        }

        if ($media->type === 'banner_image') {
            abort_unless($user->hasRole('Admin') || $user->hasRole('Super Admin'), 403);
            return;
        }

        // product_photo: allow uploader or finance
        abort_unless($media->uploaded_by === $user->id || $user->hasRole('Finance'), 403);
    }
}
