<?php

namespace App\Traits\Media;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

trait DocumentTrait
{
    private $_documentExtensions = ['pdf', 'doc', 'docx', 'csv', 'xlx', 'txt', 'pptx', 'divx', 'html'];

    public static function StoreDocument($media, $disk, $path = '', $generateThumb = false, $isPublic = true)
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

        $path = trim($path, '/');
        $mediaData = self::GenerateDocument($media, $disk, $path, $mediaInfo['unique_name']);
        if ($generateThumb && in_array($mediaInfo['extension'], (new self)->_documentExtensions)) {
            // generate thumb. ho jae ga savae document b
        }

        return $data = [
            'result'    => true,
            'media'     => $mediaData,
            'thumb'     => $thumbData,
            'type'      => Storage::disk($disk)->mimeType("$path/{$mediaInfo['unique_name']}"), // mime_content_type($storagePath . $fileNameToStore),
            'extension' => strtolower($mediaInfo['extension']),
        ];
    }

    public static function DeleteDocument($disk, $path, $name = '')
    {
        return Storage::disk($disk)->delete("$path/$name");
    }

    public static function GenerateDocument($media, $disk, $path, $fileNameToStore)
    {
        $media->storeAs($path, $fileNameToStore, $disk);

        return $data = [
            'isset' => true,
            'name'  => $fileNameToStore,
            'path'  => Storage::disk($disk)->path("$path/$fileNameToStore"),
            'size'  => Storage::disk($disk)->size("$path/$fileNameToStore"),
            'url'   => Storage::disk($disk)->url("$path/$fileNameToStore"),
        ];
    }
}
