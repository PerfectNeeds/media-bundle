<?php

namespace PN\MediaBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PN\MediaBundle\Service\ImageDimension;

class VarsRuntime implements RuntimeExtensionInterface {

    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function fileSizeConvert($bytes) {
        return \PN\ServiceBundle\Utils\General::fileSizeConvert($bytes);
    }

    public function getDimensionByType($type) {
        $imageDimensions = $this->container->get(ImageDimension::class);
        return [
            "width" => $imageDimensions->getWidth($type),
            "height" => $imageDimensions->getHeight($type)
        ];
    }

}
