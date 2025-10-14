<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait OldHasMedia
{
    /**
     * Get all media for the model.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Add media to the model.
     */
    public function addMedia(UploadedFile $file, string $collection = 'default', bool $isPrimary = false): Media
    {
        $fileName = $this->generateUniqueFileName($file);
        $path = $file->storeAs($this->getMediaPath($collection), $fileName, 'public');

        if ($isPrimary) {
            $this->media()->where('collection', $collection)->update(['is_primary' => false]);
        }

        return $this->media()->create([
            'file_path' => $path,
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'collection' => $collection,
            'is_primary' => $isPrimary,
            'sort_order' => $this->getNextSortOrder($collection),
        ]);
    }

    /**
     * Add media from URL.
     */
    public function addMediaFromUrl(string $url, string $collection = 'default', bool $isPrimary = false): Media
    {
        $contents = file_get_contents($url);
        $fileName = $this->generateUniqueFileNameFromUrl($url);
        $path = $this->getMediaPath($collection) . '/' . $fileName;

        Storage::disk('public')->put($path, $contents);

        if ($isPrimary) {
            $this->media()->where('collection', $collection)->update(['is_primary' => false]);
        }

        return $this->media()->create([
            'file_path' => $path,
            'file_name' => $fileName,
            'original_name' => basename($url),
            'file_size' => strlen($contents),
            'mime_type' => $this->getMimeTypeFromUrl($url),
            'collection' => $collection,
            'is_primary' => $isPrimary,
            'sort_order' => $this->getNextSortOrder($collection),
        ]);
    }

    /**
     * Get the first media URL for a collection.
     */
    public function getFirstMediaUrl(string $collection = 'default', string $default = ''): string
    {
        $media = $this->getFirstMedia($collection);

        return $media ? $media->getUrl() : $default;
    }

    /**
     * Get the first media for a collection.
     */
    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->media()
            ->where('collection', $collection)
            ->orderBy('is_primary', 'desc')
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Get all media URLs for a collection.
     */
    public function getMediaUrls(string $collection = 'default'): array
    {
        return $this->getMedia($collection)->map(function ($media) {
            return $media->getUrl();
        })->toArray();
    }

    /**
     * Get all media for a collection.
     */
    public function getMedia(string $collection = 'default')
    {
        return $this->media()->where('collection', $collection)->get();
    }

    /**
     * Clear all media for a collection.
     */
    public function clearMediaCollection(string $collection = 'default'): void
    {
        $this->getMedia($collection)->each(function ($media) {
            $media->delete();
        });
    }

    /**
     * Generate unique file name.
     */
    protected function generateUniqueFileName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Generate unique file name from URL.
     */
    protected function generateUniqueFileNameFromUrl(string $url): string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Get media storage path.
     */
    protected function getMediaPath(string $collection = 'default'): string
    {
        $modelName = strtolower(class_basename($this));
        return "media/{$modelName}/{$collection}";
    }

    /**
     * Get next sort order for collection.
     */
    protected function getNextSortOrder(string $collection = 'default'): int
    {
        return $this->media()
                ->where('collection', $collection)
                ->max('sort_order') + 1;
    }

    /**
     * Get mime type from URL.
     */
    protected function getMimeTypeFromUrl(string $url): string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

        return match(strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
    }
}
