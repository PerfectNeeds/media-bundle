<?php

namespace PN\MediaBundle\Twig;

use PN\MediaBundle\Service\ImageDimension;
use PN\MediaBundle\Utils\ImageWebPConverter;
use PN\ServiceBundle\Lib\UploadPath;
use PN\ServiceBundle\Utils\General;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\RuntimeExtensionInterface;

class VarsRuntime implements RuntimeExtensionInterface
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function fileSizeConvert($bytes)
    {
        return General::fileSizeConvert($bytes);
    }

    public function getDimensionByType($type)
    {
        $imageDimensions = $this->container->get(ImageDimension::class);

        return [
            "width" => $imageDimensions->getWidth($type),
            "height" => $imageDimensions->getHeight($type),
        ];
    }

    /**
     * @throws \Exception
     */
    public function setWebpExtension($filePath, $returnEmptyOnException = true)
    {
        $projectDir = $this->container->getParameter("kernel.project_dir");
        $publicDirectory = rtrim(UploadPath::getWebRoot(), '/');

        $fullFilePath = "{$projectDir}/{$publicDirectory}{$filePath}";

        if ($returnEmptyOnException && !file_exists($fullFilePath)) {
            return '';
        }

        $webPPath = ImageWebPConverter::convertImageToWebPAndCache($fullFilePath);

        return explode("{$projectDir}/{$publicDirectory}", $webPPath, 2)[1];
    }

}
