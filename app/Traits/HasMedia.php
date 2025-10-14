<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasMedia
{
    /**
     * Upload a file to the specified directory
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string|null The file path or null on failure
     */
    public function uploadFile(UploadedFile $file, string $directory = 'uploads'): ?string
    {
        try {
            // Generate a unique filename
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Store the file in the public disk
            $path = $file->storeAs($directory, $filename, 'public');

            return $path;
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a file from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
            return true; // File doesn't exist, consider it deleted
        } catch (\Exception $e) {
            Log::error('File deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate file type against allowed types
     *
     * @param UploadedFile $file
     * @param array $allowedTypes
     * @return bool
     */
    public function validateFileType(UploadedFile $file, array $allowedTypes): bool
    {
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($mimeType, $allowedTypes) || in_array($extension, $this->getAllowedImageExtensions());
    }

    /**
     * Get allowed image MIME types
     *
     * @return array
     */
    public function getAllowedImageTypes(): array
    {
        return [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml'
        ];
    }

    /**
     * Get allowed image file extensions
     *
     * @return array
     */
    public function getAllowedImageExtensions(): array
    {
        return ['jpeg', 'jpg', 'png', 'gif', 'webp', 'svg'];
    }

    /**
     * Get the full URL for a file path
     *
     * @param string|null $path
     * @return string|null
     */
    public function getFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        // If the path already contains a full URL, return it
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Generate the full URL using Laravel's asset helper for storage
        return asset('storage/' . $path);
    }

    /**
     * Get file size in human readable format
     *
     * @param string $path
     * @return string
     */
    public function getFileSize(string $path): string
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                $bytes = Storage::disk('public')->size($path);
                return $this->formatBytes($bytes);
            }
            return 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Check if file exists in storage
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Move file from temporary location to permanent location
     *
     * @param string $tempPath
     * @param string $permanentPath
     * @return bool
     */
    public function moveFile(string $tempPath, string $permanentPath): bool
    {
        try {
            return Storage::disk('public')->move($tempPath, $permanentPath);
        } catch (\Exception $e) {
            Log::error('File move failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate thumbnail for image
     *
     * @param string $imagePath
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function generateThumbnail(string $imagePath, int $width = 150, int $height = 150): ?string
    {
        // This would require intervention/image package or similar
        // For now, return the original image path
        return $imagePath;
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param string $directory
     * @return array
     */
    public function uploadMultipleFiles(array $files, string $directory = 'uploads'): array
    {
        $uploadedPaths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile && $this->validateFileType($file, $this->getAllowedImageTypes())) {
                $path = $this->uploadFile($file, $directory);
                if ($path) {
                    $uploadedPaths[] = $path;
                }
            }
        }

        return $uploadedPaths;
    }

    /**
     * Delete multiple files
     *
     * @param array $paths
     * @return bool
     */
    public function deleteMultipleFiles(array $paths): bool
    {
        $allDeleted = true;

        foreach ($paths as $path) {
            if (!$this->deleteFile($path)) {
                $allDeleted = false;
            }
        }

        return $allDeleted;
    }
}
