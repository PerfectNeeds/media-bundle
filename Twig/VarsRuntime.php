<?php

namespace PN\MediaBundle\Twig;

use PN\MediaBundle\Service\ImageDimension;
use PN\MediaBundle\Service\ImageWebPService;
use PN\ServiceBundle\Utils\General;
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

    public function fileSizeConvert($bytes): string
    {
        return (new General)->fileSizeConvert($bytes);
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
    public function setWebpExtension(string $filePath, int $width = null, int $height = null): string
    {
        return $this->imageWebPService->convertToWebP($filePath, $width, $height);
    }

}
