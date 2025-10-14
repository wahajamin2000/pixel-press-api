<?php

namespace App\Traits\Media;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

trait VideoTrait
{
    private $_videoExtensions = ['mp3', 'mp4', 'mov', 'webm'];

    public static function StoreVideo($media, $disk, $path = '', $generateThumb = false, $isPublic = true)
    {
        $mediaData = ['isset' => false];
        $thumbData = ['isset' => false];

        if (!isset($media)) return [
            'result'    => false,
            'media'     => $mediaData,
            'thumb'     => $thumbData,
            'type'      => null,
            'extension' => null,
        ];

        $mediaInfo = self::getMediaInfo($media);

        $mediaData = self::GenerateVideo($media, $disk, $path, $mediaInfo['unique_name']);
        if ($generateThumb && in_array($mediaInfo['extension'], (new self)->_videoExtensions)) {
            // generate thumb
        }

        return $data = [
            'result'    => true,
            'media'     => $mediaData,
            'thumb'     => $thumbData,
            'type'      => Storage::disk($disk)->mimeType($mediaInfo['unique_name']), // mime_content_type($storagePath . $fileNameToStore),
            'extension' => strtolower($mediaInfo['extension']),
        ];
    }

    public static function DeleteVideo($disk, $path, $name = '')
    {
        return Storage::disk($disk)->delete($path . $name);
    }

    public static function GenerateVideo($media, $disk, $path, $fileNameToStore)
    {
        $media->storeAs($path, $fileNameToStore, $disk);

        return $data = [
            'isset' => true,
            'name'  => $fileNameToStore,
            'path'  => Storage::disk($disk)->path($fileNameToStore),
            'size'  => Storage::disk($disk)->size($fileNameToStore),
            'url'   => Storage::disk($disk)->url($fileNameToStore),
        ];
    }
}
