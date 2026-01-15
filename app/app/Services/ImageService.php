<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Process uploaded image: crop to 1:1 ratio (1080x1080) and convert to WebP
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return array ['path' => string, 'size' => int, 'mime' => string, 'extension' => string]
     */
    public function processProductPhoto($file, string $folder = 'media/product_photo'): array
    {
        // Read the uploaded file
        $image = $this->manager->read($file->getRealPath());

        // Get original dimensions
        $width = $image->width();
        $height = $image->height();

        // Crop to center square (1:1 ratio)
        $minDimension = min($width, $height);
        $image->cover($minDimension, $minDimension);

        // Resize to 1080x1080
        $image->scale(1080, 1080);

        // Generate unique filename with .webp extension
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $originalName . '_' . time() . '.webp';
        $path = $folder . '/' . $filename;

        // Encode to WebP with 85% quality
        $encoded = $image->toWebp(85);

        // Store to public disk
        Storage::disk('public')->put($path, (string) $encoded);

        // Get file size
        $size = Storage::disk('public')->size($path);

        return [
            'path' => $path,
            'size' => $size,
            'mime' => 'image/webp',
            'extension' => 'webp',
        ];
    }

    /**
     * Process payment proof or shipment proof (no crop, just convert to WebP if image)
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return array ['path' => string, 'size' => int, 'mime' => string, 'extension' => string]
     */
    public function processProofImage($file, string $folder): array
    {
        $mime = $file->getClientMimeType();

        // If not an image, just store as-is
        if (!str_starts_with($mime, 'image/')) {
            $path = $file->store($folder, 'public');
            return [
                'path' => $path,
                'size' => $file->getSize(),
                'mime' => $mime,
                'extension' => $file->getClientOriginalExtension(),
            ];
        }

        // Process image: convert to WebP but keep original dimensions
        $image = $this->manager->read($file->getRealPath());

        // Generate unique filename with .webp extension
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $originalName . '_' . time() . '.webp';
        $path = $folder . '/' . $filename;

        // Encode to WebP with 90% quality (higher quality for proofs)
        $encoded = $image->toWebp(90);

        // Store to public disk
        Storage::disk('public')->put($path, (string) $encoded);

        // Get file size
        $size = Storage::disk('public')->size($path);

        return [
            'path' => $path,
            'size' => $size,
            'mime' => 'image/webp',
            'extension' => 'webp',
        ];
    }

    /**
     * Process banner image (no crop, keep original dimensions, convert to WebP).
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return array ['path' => string, 'size' => int, 'mime' => string, 'extension' => string]
     */
    public function processBannerImage($file, string $folder = 'media/banner_image'): array
    {
        $image = $this->manager->read($file->getRealPath());

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $filename = $originalName . '_' . time() . '.webp';
        $path = $folder . '/' . $filename;

        $encoded = $image->toWebp(85);

        Storage::disk('public')->put($path, (string) $encoded);

        $size = Storage::disk('public')->size($path);

        return [
            'path' => $path,
            'size' => $size,
            'mime' => 'image/webp',
            'extension' => 'webp',
        ];
    }
}
