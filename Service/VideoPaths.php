<?php

namespace PN\MediaBundle\Service;

use PN\ServiceBundle\Service\ContainerParameterService;

/**
 * get video upload Path
 *
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 */
class VideoPaths
{

    private static $type = [];

    public function __construct(ContainerParameterService $containerParameterService)
    {
        $uploadPaths = $containerParameterService->get('pn_media_video.upload_paths');

        foreach ($uploadPaths as $uploadPath) {
            $id = $uploadPath['id'];
            $path = rtrim($uploadPath['path'], '/').'/';
            self::$type[$id] = $path;
        }
    }

    /**
     * @param type $type
     * @return type
     */
    public static function get($type)
    {
        if (!array_key_exists($type, self::$type)) {
            return null;
        }

        return self::$type[$type];
    }

    /**
     *
     * @param type $type
     * @return boolean
     */
    public static function has($type)
    {
        if (!array_key_exists($type, self::$type)) {
            return false;
        }

        return true;
    }

}
