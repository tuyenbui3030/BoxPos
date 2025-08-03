<?php

namespace Packages\MaterialCatalog\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Packages\Log\Traits\Loggable;

class CloudflareR2Service
{
    use Loggable;

    private string $disk = 'r2';
    private string $bucket;
    private string $publicUrl;

    public function __construct()
    {
        $this->bucket = config('filesystems.disks.r2.bucket');
        $this->publicUrl = config('filesystems.disks.r2.url');
    }

    /**
     * Upload multiple images to R2
     */
    public function uploadImages(array $images, string $folder = 'materials'): array
    {
        $uploadedImages = [];

        foreach ($images as $image) {
            if ($image && $image instanceof UploadedFile && $image->isValid()) {
                try {
                    $uploadedImage = $this->uploadSingleImage($image, $folder);
                    if ($uploadedImage) {
                        $uploadedImages[] = $uploadedImage;
                    }
                } catch (\Exception $e) {
                    $this->logError($e, [
                        'action' => 'upload_image_to_r2',
                        'image_name' => $image->getClientOriginalName(),
                        'folder' => $folder,
                    ]);
                    // Continue with other images even if one fails
                    continue;
                }
            }
        }

        return $uploadedImages;
    }

    /**
     * Upload single image to R2
     */
    public function uploadSingleImage(UploadedFile $image, string $folder = 'materials'): ?array
    {
        try {
            // Generate unique filename
            $filename = $this->generateUniqueFilename($image);
            $path = $folder . '/' . $filename;

            // Upload to R2
            $uploaded = Storage::disk($this->disk)->put($path, file_get_contents($image->getRealPath()), [
                'ContentType' => $image->getMimeType(),
                'CacheControl' => 'max-age=31536000', // 1 year cache
            ]);

            if ($uploaded) {
                $this->logActivity('image_uploaded_to_r2', [
                    'path' => $path,
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                ]);

                return [
                    'path' => $path,
                    'url' => $this->getPublicUrl($path),
                    'original_name' => $image->getClientOriginalName(),
                    'size' => $image->getSize(),
                    'mime_type' => $image->getMimeType(),
                ];
            }

            return null;
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'upload_single_image_to_r2',
                'image_name' => $image->getClientOriginalName(),
                'folder' => $folder,
            ]);
            throw $e;
        }
    }

    /**
     * Delete images from R2
     */
    public function deleteImages(array $images): void
    {
        foreach ($images as $image) {
            try {
                $path = is_array($image) ? $image['path'] : $image;
                if (Storage::disk($this->disk)->exists($path)) {
                    Storage::disk($this->disk)->delete($path);
                    
                    $this->logActivity('image_deleted_from_r2', [
                        'path' => $path,
                    ]);
                }
            } catch (\Exception $e) {
                $this->logError($e, [
                    'action' => 'delete_image_from_r2',
                    'image_path' => $path ?? 'unknown',
                ]);
                // Continue with other images even if one fails
                continue;
            }
        }
    }

    /**
     * Get public URL for an image
     */
    public function getPublicUrl(string $path): string
    {
        return rtrim($this->publicUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $image): string
    {
        $extension = $image->getClientOriginalExtension();
        $timestamp = now()->format('Y/m/d');
        $uniqueId = Str::uuid();
        
        return $timestamp . '/' . $uniqueId . '.' . $extension;
    }

    /**
     * Check if R2 is properly configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->bucket) && 
               !empty(config('filesystems.disks.r2.key')) && 
               !empty(config('filesystems.disks.r2.secret'));
    }
}