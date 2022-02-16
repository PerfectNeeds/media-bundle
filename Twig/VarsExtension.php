<?php

namespace PN\MediaBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class VarsExtension extends AbstractExtension
{

    public function getFunctions()
    {
        return array(
            new TwigFunction('fileSizeConvert', array(VarsRuntime::class, 'fileSizeConvert')),
            new TwigFunction('getDimension', array(VarsRuntime::class, 'getDimensionByType')),
        );
    }

    public function getFilters()
    {
        return [
            new TwigFilter('webp', [VarsRuntime::class, 'setWebpExtension']),
        ];
    }

    public function getName()
    {
        return 'media.twig.extension';
    }

}
