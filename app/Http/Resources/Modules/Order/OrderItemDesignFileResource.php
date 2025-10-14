<?php

namespace App\Http\Resources\Modules\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OrderItemDesignFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_filename' => $this->original_filename,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'file_size_human' => $this->formatFileSize($this->file_size),
            'url' => $this->getFileUrl(),
//            'download_url' => route('api.design-files.download', $this->id),
            'thumbnail_url' => $this->isImage() ? $this->getFileUrl() : null,
            'is_image' => $this->isImage(),
            'uploaded_at' => $this->created_at,
            'uploaded_at_human' => $this->created_at->diffForHumans(),
        ];
    }


    /**
     * Format file size in human readable format
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    private function isImage(): bool
    {
        return strpos($this->file_type, 'image/') === 0;
    }

    /**
     * Get file URL
     */
    private function getFileUrl(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
