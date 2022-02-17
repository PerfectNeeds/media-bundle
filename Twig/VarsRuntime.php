<?php

namespace PN\MediaBundle\Twig;

use PN\MediaBundle\Service\ImageDimension;
use PN\MediaBundle\Utils\ImageWebPConverter;
use PN\ServiceBundle\Lib\UploadPath;
use PN\ServiceBundle\Utils\General;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\RuntimeExtensionInterface;

class VarsRuntime implements RuntimeExtensionInterface
{

    private ImageDimension $imageDimension;
    private ParameterBagInterface $parameterBag;

    public function __construct(ImageDimension $imageDimension, ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
        $this->imageDimension = $imageDimension;
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
    public function setWebpExtension(string $filePath, bool $returnEmptyOnException = true): string
    {
        $projectDir = $this->parameterBag->get("kernel.project_dir");
        $publicDirectory = rtrim(UploadPath::getWebRoot(), '/');

        $originalFilePath = $filePath;
        if (strpos($filePath, $publicDirectory) !== false) {
            $filePath = substr($filePath, strpos($filePath, $publicDirectory) + strlen($publicDirectory));
        }

        $fullFilePath = "{$projectDir}/{$publicDirectory}{$filePath}";

        if ($returnEmptyOnException && !file_exists($fullFilePath)) {
            return '';
        }

        $webPPath = (new ImageWebPConverter())->convertImageToWebPAndCache($fullFilePath);

        $assetPath = explode("{$projectDir}/{$publicDirectory}", $webPPath, 2)[1];
        if (strpos($originalFilePath, $publicDirectory) !== false) {
            $baseAssetPath = substr($originalFilePath, 0, strpos($originalFilePath, $publicDirectory)+ strlen($publicDirectory));
            $assetPath  = $baseAssetPath.$assetPath;
        }
        
        return $assetPath;
    }

}
