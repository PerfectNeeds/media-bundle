<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VideoBL
 *
 * @author toni
 */

namespace PN\MediaBundle\Utils;

class VideoBL {

    public static function explode_url($url) {
        if (preg_match('~(?:video\.google\.(?:com|com\.au|co\.uk|de|es|fr|it|nl|pl|ca|cn)/(?:[^"]*?))?(?:(?:www|au|br|ca|es|fr|de|hk|ie|in|il|it|jp|kr|mx|nl|nz|pl|ru|tw|uk)\.)?youtube\.com(?:[^"]*?)?(?:&|&amp;|/|\?|;|\%3F|\%2F)(?:video_id=|v(?:/|=|\%3D|\%2F))([0-9a-z-_]{11})~imu', $url, $match)) {
            $ID = $match[1];
            $url = "http://gdata.youtube.com/feeds/api/videos/$ID?v=2&alt=jsonc";
            $type = "y";
        } elseif (preg_match('~(?:www\.)?vimeo\.com/([0-9]{1,12})~imu', $url, $match)) {
            $ID = $match[1];
            $url = "http://vimeo.com/api/v2/video/$ID.json";
            $type = "v";
        } else {
            return -1;
        }
        $array['id'] = $ID;
        $array['url'] = $url;
        $array['type'] = $type;
        return $array;
    }

    public static function get_json($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $json = json_decode(curl_exec($ch));
        curl_close($ch);
        return $json;
    }

    public static function fetch_json($json, $type) {
        if ($type == "y") {
            $array['image'] = $json->data->thumbnail->hqDefault;
            $array['title'] = $json->data->title;
            $array['desc'] = $json->data->description;
        } else {
            $json = $json[0];
            $array['image'] = $json->thumbnail_large;
            $array['title'] = $json->title;
            $array['desc'] = $json->description;
        }
        return $array;
    }

}

?>