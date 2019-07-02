<?php

namespace PN\MediaBundle\Service;

use Psr\Container\ContainerInterface;
use PN\ServiceBundle\Service\ContainerParameterService;

/**
 * get image upload dimensions
 *
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 */
class ImageDimension {

    private static $type = [];

    public function __construct(ContainerInterface $container) {

        $containerParameterService = $container->get(ContainerParameterService::class);

        $uploadPaths = $containerParameterService->get('pn_media_image.upload_paths');

        if ($containerParameterService->has('pn_content_image.upload_paths')) {
            $contentBundleUploadPaths = $containerParameterService->get('pn_content_image.upload_paths');
            $uploadPaths = array_merge($uploadPaths, $contentBundleUploadPaths);
        }

        foreach ($uploadPaths as $uploadPath) {
            $nod = [];
            if (array_key_exists('width', $uploadPath)) {
                $width = $uploadPath['width'];
                $nod['width'] = (is_numeric($width)) ? $width : null;
            }
            if (array_key_exists('height', $uploadPath)) {
                $height = $uploadPath['height'];
                $nod['height'] = (is_numeric($height)) ? $height : null;
            }
            if (count($nod) > 0) {
                $id = $uploadPath['id'];
                self::$type[$id] = $nod;
            }
        }
    }

    /**
     * @param type $type
     * @return integer
     */
    public static function getWidth($type) {
        if (!array_key_exists($type, self::$type)) {
            return null;
        }
        if (!array_key_exists("width", self::$type[$type])) {
            return null;
        }
        return self::$type[$type]["width"];
    }

    /**
     * @param type $type
     * @return integer
     */
    public static function getHeight($type) {
        if (!array_key_exists($type, self::$type)) {
            return null;
        }
        if (!array_key_exists("height", self::$type[$type])) {
            return null;
        }
        return self::$type[$type]["height"];
    }

    /**
     *
     * @param type $type
     * @return boolean
     */
    public static function has($type) {
        if (!array_key_exists($type, self::$type)) {
            return false;
        }
        return true;
    }

}
