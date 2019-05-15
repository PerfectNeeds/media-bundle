<?php

namespace PN\MediaBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;

class VarsRuntime implements RuntimeExtensionInterface {

    public function fileSizeConvert($bytes) {
        return \PN\Utils\General::fileSizeConvert($bytes);
    }

}
