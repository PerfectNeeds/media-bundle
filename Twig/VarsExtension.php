<?php

namespace PN\MediaBundle\Twig;

use PN\MediaBundle\Twig\VarsRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class VarsExtension extends AbstractExtension {

    public function getFunctions() {
        return array(
            new TwigFunction('fileSizeConvert', array(VarsRuntime::class, 'fileSizeConvert')),
            new TwigFunction('getDimension', array(VarsRuntime::class, 'getDimensionByType')),
        );
    }

    public function getName() {
        return 'media.twig.extension';
    }

}
