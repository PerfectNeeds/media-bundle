<?php

namespace PN\MediaBundle\Service;

/**
 * get image upload Path
 * 
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 */
class ImagePaths {

    private static $type = [
        90 => 'dynamic-content/',
        100 => 'banner/',
    ];

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
