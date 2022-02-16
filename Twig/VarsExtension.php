<?php

namespace PN\MediaBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class VarsExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('fileSizeConvert', [VarsRuntime::class, 'fileSizeConvert']),
            new TwigFunction('getDimension', [VarsRuntime::class, 'getDimensionByType']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('webp', [VarsRuntime::class, 'setWebpExtension']),
        ];
    }

    public function getName(): string
    {
        return 'media.twig.extension';
    }

}
