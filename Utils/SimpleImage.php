<?php

/*
 * File: SimpleImage.php
 * Author: Simon Jarvis
 * Copyright: 2006 Simon Jarvis
 * Date: 08/11/06
 * Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details:
 * http://www.gnu.org/licenses/gpl.html
 *
 */

namespace PN\MediaBundle\Utils;

class SimpleImage
{

    private static $image;
    private static $imageType;

    private static function load($filename): void
    {
        $image_info = getimagesize($filename);
        self::$imageType = $image_info[2];
        if (self::$imageType == IMAGETYPE_JPEG) {
            self::$image = imagecreatefromjpeg($filename);
        } elseif (self::$imageType == IMAGETYPE_GIF) {
            self::$image = imagecreatefromgif($filename);
        } elseif (self::$imageType == IMAGETYPE_PNG) {
            self::$image = imagecreatefrompng($filename);
        }
    }

    private static function save($filename, $compression = 75, $permissions = null): void
    {
        if (self::$imageType == IMAGETYPE_JPEG) {
            imagejpeg(self::$image, $filename, $compression);
        } elseif (self::$imageType == IMAGETYPE_GIF) {
            imagegif(self::$image, $filename, $compression);
        } elseif (self::$imageType == IMAGETYPE_PNG) {
            if ($compression == 75) {
                $compression = 6; // low quality
            } elseif ($compression > 75) {
                $compression = 9; // low quality
            } else {
                $compression = 0; // 100% quality
            }
            imagepng(self::$image, $filename, $compression);
        }
        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }

    private static function output($imageType = IMAGETYPE_JPEG): void
    {

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg(self::$image);
                break;
            case IMAGETYPE_GIF:
                imagegif(self::$image);
                break;
            case IMAGETYPE_PNG:
                imagepng(self::$image);
                break;
        }
    }

    private static function getWidth(): int
    {
        return intval(imagesx(self::$image));
    }

    private static function getHeight(): int
    {
        return intval(imagesy(self::$image));
    }

    private static function resizeToHeight($height): void
    {
        $ratio = $height / self::getHeight();
        $width = intval(self::getWidth() * $ratio);
        self::resize($width, $height);
    }

    private static function resizeToWidth($width): void
    {
        $ratio = $width / self::getWidth();
        $height = (int)(self::getHeight() * $ratio);
        self::resize($width, $height);
    }

    private static function scale($scale): void
    {
        $width = (int)(self::getWidth() * $scale / 100);
        $height = (int)(self::getHeight() * $scale / 100);
        self::resize($width, $height);
    }

    private static function resize(int $width, int $height): void
    {
        if (self::$imageType == IMAGETYPE_GIF) {
            $new_image = imagecreate($width, $height); // for gif files
        } else {
            $new_image = imagecreatetruecolor($width, $height);
        }
        if ((self::$imageType == IMAGETYPE_GIF) || (self::$imageType == IMAGETYPE_PNG)) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled($new_image, self::$image, 0, 0, 0, 0, $width, $height, self::getWidth(),
            self::getHeight());
        self::$image = $new_image;
    }

    public static function saveNewResizedImage(
        $imagePath,
        $newImagePath,
        $maxWidth,
        $maxHeight,
        $optimization = 75
    ): void {
        self::load($imagePath);

        if ($maxWidth and $maxHeight == null) {
            self::resizeToWidth($maxWidth);
        } elseif ($maxHeight and $maxWidth == null) {
            self::resizeToHeight($maxHeight);
        } else {
            if (self::getWidth() >= self::getHeight()) {
                self::resizeToWidth($maxWidth);
            } else {
                self::resizeToHeight($maxHeight);
            }
        }

        self::save($newImagePath, $optimization);
    }

}