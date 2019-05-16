<?php

namespace PN\MediaBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use PN\ServiceBundle\Service\ContainerParameterService;

/**
 * get document upload Path
 *
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 */
class DocumentPaths {

    private static $type = [];

    public function __construct(ContainerInterface $container) {
        $uploadPaths = $container->get(ContainerParameterService::class)->get('pn_media_document.upload_paths');
        foreach ($uploadPaths as $uploadPath) {
            $id = $uploadPath['id'];
            $path = rtrim($uploadPath['path'], '/') . '/';
            self::$type[$id] = $path;
        }
    }

    /**
     * @param type $type
     * @return type
     */
    public static function get($type) {
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
    public static function has($type) {
        if (!array_key_exists($type, self::$type)) {
            return false;
        }
        return true;
    }

}
