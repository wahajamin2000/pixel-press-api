<?php

namespace App\Traits\Media;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

trait ImageTrait
{
    private $_imageExtensions = ['jpg', 'png', 'jpeg'];

    public static function StoreImage($media, $disk, $path = '', $generateThumb = true, $isPublic = true)
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
        $mediaData = self::GenerateImage($media, $disk, $path, $mediaInfo['unique_name']);
        if ($generateThumb && in_array(strtolower($mediaInfo['extension']), (new self)->_imageExtensions)) {
            $thumbData = self::GenerateImageThumb($media, $disk, $path, $mediaInfo['unique_name']);
        }

        return $data = [
            'result'    => true,
            'media'     => $mediaData,
            'thumb'     => $thumbData,
            'type'      => Storage::disk($disk)->mimeType("$path/{$mediaInfo['unique_name']}"), // mime_content_type($storagePath . $fileNameToStore),
            'extension' => strtolower($mediaInfo['extension']),
        ];
    }

    public static function GenerateImage($media, $disk, $path, $fileNameToStore)
    {
//        $disk ='custom';
        $media->storeAs($path, $fileNameToStore, $disk);
//        $media->move(public_path().'/files/', $fileNameToStore);
//        $media->store('D:\Workspace\guard-tracker-media\user', $fileNameToStore);
        return $data = [
            'isset' => true,
            'name'  => $fileNameToStore,
            'path'  => Storage::disk($disk)->path("$path/$fileNameToStore"),
            'size'  => Storage::disk($disk)->size("$path/$fileNameToStore"),
            'url'   => Storage::disk($disk)->url("$path/$fileNameToStore"),
        ];
    }

    public static function GenerateImageThumb($media, $disk, $path, $fileNameToStore, $width = 200, $height = 200, $isPublic = true)
    {
        ini_set('memory_limit', '1000M');
        $fileNameToStore = 'thumb_' . $fileNameToStore;

        // intervention > 3.0
        $media = ImageManager::gd()->read($media)->scale(200, 200);
        $media->save(Storage::disk($disk)->path("$path/$fileNameToStore"));

        // intervention < 3.0
        // $media = Image::make($media)->resize($width, $height, function ($constraint) {
        //     $constraint->aspectRatio();
        // });
        // $media->stream();
        // Storage::disk($disk)->put("$path/$fileNameToStore", $media);

        return $data = [
            'isset' => true,
            'name'  => $fileNameToStore,
            'path'  => Storage::disk($disk)->path("$path/$fileNameToStore"),
            'size'  => Storage::disk($disk)->size("$path/$fileNameToStore"),
            'url'   => Storage::disk($disk)->url("$path/$fileNameToStore"),
        ];
    }

    public static function DeleteImage($disk, $path, $name = '')
    {
        return Storage::disk($disk)->delete("$path/$name");
    }
}
