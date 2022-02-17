<?php

namespace PN\MediaBundle\Utils;

use Symfony\Component\HttpFoundation\File\File;

class ImageWebPConverter
{

    public static function convertImageToWebP($image, string $savePath, int $quality = 80): array
    {
        $file = ($image instanceof File) ? $image : new File($image);
        $fullPath = $file->getRealPath();

        $extension = $file->guessExtension();

        if ($file->guessExtension() === "webp") {
            return [
                "resource" => null,
                "path" => $fullPath,
            ];
        }
        if (!function_exists('imagewebp')) {
            throw new \Exception("imagewebp function is undefined, Please install php_gd extension");
        }
        $imageResource = self::createImageResource($fullPath, $extension);

        $fileNameWithoutExtension = substr($file->getFilename(), 0, strrpos($file->getFilename(), '.'));

        self::createSavePathDirectory($savePath);
        $webPPath = self::createWebPPath($savePath, $fileNameWithoutExtension);

        if (!file_exists($webPPath)) {
            imagewebp($imageResource, $webPPath, $quality);
        }

        return [
            "resource" => $imageResource,
            "path" => $webPPath,
        ];
    }

    public static function convertImageToWebPAndCache($imagePath, int $quality = 80): string
    {
        $savePath = str_replace("uploads/", "uploads/cache/", $imagePath);
        $explodedSavePath = explode("/", $savePath);
        array_pop($explodedSavePath);
        $cacheDirectory = implode("/", $explodedSavePath);
        if (file_exists($savePath)) {
            return $savePath;
        }


        $image = self::convertImageToWebP($imagePath, $cacheDirectory, $quality);

        return $image['path'];

    }


    /**
     * @param string $path
     * @param string $extension
     * @return false|\GdImage|resource
     * @throws \Exception
     */
    private static function createImageResource(string $path, string $extension)
    {
        if ($extension === 'png') {
            $imageResource = imagecreatefrompng($path);
            imageinterlace($imageResource, false);
        } elseif ($extension === 'jpeg' || $extension === 'jpg') {
            $imageResource = imagecreatefromjpeg($path);
        } elseif ($extension === 'bmp') {
            $imageResource = imagecreatefrombmp($path);
        } elseif ($extension === 'gif') {
            $imageResource = imagecreatefromgif($path);
        } else {
            throw new \Exception("No valid file type provided for {$path}");
        }
        self::setColorsAndAlpha($imageResource);

        return $imageResource;
    }

    private static function createWebPPath(string $savePath, string $filename): string
    {
        return "{$savePath}/{$filename}.webp";
    }

    private static function createSavePathDirectory(string $savePath): void
    {
        if (!file_exists($savePath)) {
            mkdir($savePath, 0777, true);
        }
    }

    private static function setColorsAndAlpha($image): void
    {
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }
}
