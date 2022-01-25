<?php

namespace PN\MediaBundle\Twig;

use PN\MediaBundle\Service\ImageDimension;
use Twig\Extension\RuntimeExtensionInterface;

class VarsRuntime implements RuntimeExtensionInterface
{

    private $imageDimension;

    public function __construct(ImageDimension $imageDimension)
    {
        $this->imageDimension = $imageDimension;
    }

    public function fileSizeConvert($bytes)
    {
        return \PN\ServiceBundle\Utils\General::fileSizeConvert($bytes);
    }

    public function getDimensionByType($type)
    {
        return [
            "width" => $this->imageDimensions->getWidth($type),
            "height" => $this->imageDimensions->getHeight($type),
        ];
    }

}
