<?php

namespace PN\MediaBundle\Twig;

use PN\MediaBundle\Service\ImageDimension;
use PN\MediaBundle\Service\ImageWebPService;
use PN\ServiceBundle\Lib\UploadPath;
use PN\ServiceBundle\Utils\General;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class VarsRuntime implements RuntimeExtensionInterface
{

    private ImageDimension $imageDimension;
    private ImageWebPService $imageWebPService;

    public function __construct(ImageDimension $imageDimension, ImageWebPService $imageWebPService)
    {
        $this->imageDimension = $imageDimension;
        $this->imageWebPService = $imageWebPService;
    }

    public function fileSizeConvert($bytes)
    {
        return General::fileSizeConvert($bytes);
    }

    public function getDimensionByType($type): array
    {
        return [
            "width" => $this->imageDimension->getWidth($type),
            "height" => $this->imageDimension->getHeight($type),
        ];
    }

    /**
     * @throws \Exception
     */
    public function setWebpExtension($filePath, $width = null, $height = null)
    {
        return $this->imageWebPService->convertToWebP($filePath, $width, $height);
    }

}
