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

        $originalFilePath = $filePath;
        if (strpos($filePath, $publicDirectory) !== false) {
            $filePath = substr($filePath, strpos($filePath, $publicDirectory) + strlen($publicDirectory));
        }

        if ($this->container->hasParameter("router.request_context.scheme") and $this->container->hasParameter("router.request_context.host")) {
            $baseUrl = $this->container->getParameter("router.request_context.scheme")."://".$this->container->getParameter("router.request_context.host");
            $filePath = str_replace($baseUrl, "", $filePath);
        }

        $fullFilePath = "{$projectDir}/{$publicDirectory}{$filePath}";

        if ($returnEmptyOnException && !file_exists($fullFilePath)) {
            return '';
        }

        $webPPath = ImageWebPConverter::convertImageToWebPAndCache($fullFilePath);

        $assetPath = explode("{$projectDir}/{$publicDirectory}", $webPPath, 2)[1];
        if (strpos($originalFilePath, $publicDirectory) !== false) {
            $baseAssetPath = substr($originalFilePath, 0,
                strpos($originalFilePath, $publicDirectory) + strlen($publicDirectory));
            $assetPath = $baseAssetPath.$assetPath;
        }

        return $assetPath;
    }

}
