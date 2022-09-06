<?php

namespace PN\MediaBundle\Utils;

use Symfony\Component\HttpFoundation\File\File;

class ImageWebPConverter
{

    public static function convertImageToWebP(
        $image,
        string $savePath,
        int $quality = 80,
        int $width = null,
        int $height = null
    ): array {
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

        $fileNameWithoutExtension = substr($file->getFilename(), 0, strrpos($file->getFilename(), '.'));

        $webPPath = self::createWebPPath($savePath, $fileNameWithoutExtension, $quality, $width, $height);
        if (file_exists($webPPath)) {
            return [
                "resource" => null,
                "path" => $webPPath,
            ];
        }

        $imageResource = self::createImageResource($fullPath, $extension, $width, $height);
        self::createSavePathDirectory($savePath);

        imagewebp($imageResource, $webPPath, $quality);

        return [
            "resource" => $imageResource,
            "path" => $webPPath,
        ];
    }

    public static function convertImageToWebPAndCache(
        $absolutePublicDirectory,
        $imagePath,
        int $quality = 80,
        int $width = null,
        int $height = null
    ): string {
        $savePath = str_replace($absolutePublicDirectory, rtrim($absolutePublicDirectory, "/")."/uploads/cache",
            $imagePath);
        if (strpos($savePath, "uploads/cache/uploads") !== false) {
            $savePath = str_replace("uploads/cache/uploads", "uploads/cache", $savePath);
        }

        if (file_exists($savePath)) {
            return $savePath;
        }

        $explodedSavePath = explode("/", $savePath);
        array_pop($explodedSavePath);
        $cacheDirectory = implode("/", $explodedSavePath);
        $image = self::convertImageToWebP($imagePath, $cacheDirectory, $quality, $width, $height);

        return $image['path'];

    }


    /**
     * @param string $path
     * @param string $extension
     * @return false|\GdImage|resource
     * @throws \Exception
     */
    private static function createImageResource(string $path, string $extension, int $width = null, int $height = null)
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
        if ($width != null or $height != null) {
            return self::resize($path, $imageResource, $width, $height);
        }

        self::setColorsAndAlpha($imageResource);

        return $imageResource;
    }


    private static function createWebPPath(
        string $savePath,
        string $filename,
        int $quality,
        int $width = null,
        int $height = null
    ): string {
        if ($quality != 80 or $width != null or $height != null) {
            $fileNameArr = [$filename];
            if ($quality > 0) {
                $fileNameArr[] = "q-{$quality}";
            }
            if ($width > 0) {
                $fileNameArr[] = "w-{$width}";
            }
            if ($height > 0) {
                $fileNameArr[] = "h-{$height}";
            }
            $filename = implode("-", $fileNameArr);
        }

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

    private static function resize(string $path,  $image, int $width, int $height)
    {
        $imageInfo = getimagesize($path);
        $imageType = $imageInfo[2];
        $imageWidth = $imageInfo[0];
        $imageHeight = $imageInfo[1];

        if ($width > 0 and $height == null) {
            $ratio = $width / $imageWidth;
            $height = $imageHeight * $ratio;
        } elseif ($width == null and $height > 0) {
            $ratio = $height / $imageHeight;
            $width = $imageWidth * $ratio;
        }


        if ($imageType == IMAGETYPE_GIF) {
            $newImage = imagecreate($width, $height); // for gif files
        } else {
            $newImage = imagecreatetruecolor($width, $height);
        }

        self::setColorsAndAlpha($newImage);
        if (in_array($imageType, [IMAGETYPE_GIF, IMAGETYPE_PNG])) {
            imagepalettetotruecolor($newImage);
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight);

        return $newImage;
    }
}
