<?php

namespace PN\MediaBundle\Service;

use PN\MediaBundle\Utils\ImageWebPConverter;
use PN\ServiceBundle\Lib\UploadPath;
use PN\ServiceBundle\Service\ContainerParameterService;

/**
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 * @version 1.0
 */
class ImageWebPService
{

    private ContainerParameterService $containerParameter;

    public function __construct(ContainerParameterService $containerParameter)
    {
        $this->containerParameter = $containerParameter;
    }

    public function convertToWebP($filePath, $width = null, $height = null)
    {
        $projectDir = $this->containerParameter->get("kernel.project_dir");
        $publicDirectory = rtrim(UploadPath::getWebRoot(), '/');

        $absolutePublicDirectory = "{$projectDir}/{$publicDirectory}";
        $originalFilePath = $filePath;

        if (strpos($filePath, $publicDirectory) !== false) {
            $filePath = substr($filePath, strpos($filePath, $publicDirectory) + strlen($publicDirectory));
        }
        if ($this->containerParameter->has("router.request_context.scheme") and $this->containerParameter->has("router.request_context.host")) {
            $baseUrl = $this->containerParameter->get("router.request_context.scheme")."://".$this->containerParameter->get("router.request_context.host");
            $filePath = str_replace($baseUrl, "", $filePath);
        }


        $fullFilePath = "{$projectDir}/{$publicDirectory}{$filePath}";

        if (!file_exists($fullFilePath)) {
            return $filePath;
        }

        $webPPath = ImageWebPConverter::convertImageToWebPAndCache($absolutePublicDirectory, $fullFilePath, 80, $width,
            $height);

        $assetPath = explode("{$projectDir}/{$publicDirectory}", $webPPath, 2)[1];
        if (strpos($originalFilePath, $publicDirectory) !== false) {
            $baseAssetPath = substr($originalFilePath, 0,
                strpos($originalFilePath, $publicDirectory) + strlen($publicDirectory));
            $assetPath = $baseAssetPath.$assetPath;
        }

        return $assetPath;
    }


}
