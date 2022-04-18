<?php

namespace PN\MediaBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use PN\MediaBundle\Entity\Image;
use PN\MediaBundle\Entity\ImageSetting;
use PN\MediaBundle\Utils\SimpleImage;
use PN\ServiceBundle\Service\ContainerParameterService;
use PN\ServiceBundle\Utils\Slug;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Upload image
 *
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 * @version 1.0
 */
class UploadImageService
{

    private $allowMimeType = [];
    private $imageClass;
    private $maxUploadSize = 1024000; // 1MB
    private $imagePaths;
    private $imageDimensions;
    private $em;
    private $container;
    private $imageSetting;
    private $tmpImage = null;

    public function __construct(
        ContainerParameterService $containerParameterService,
        EntityManagerInterface $em,
        ImagePaths $imagePaths,
        ImageDimension $imageDimension
    ) {
        $this->em = $em;
        $this->allowMimeType = $containerParameterService->get('pn_media_image.mime_types');
        $this->imageClass = $containerParameterService->get('pn_media_image.image_class');
        $this->imagePaths = $imagePaths;
        $this->imageDimensions = $imageDimension;
    }

    public function uploadSingleImageByUrl(
        $entity,
        $url,
        $type,
        $request = null,
        $imageType = Image::TYPE_MAIN,
        $objectName = "Image",
        $removeOldImage = false
    ): bool|string|Image {
        $info = pathinfo($url);
        $contents = file_get_contents($url);
        $file = '/tmp/'.$info['basename'];
        file_put_contents($file, $contents);
        $this->tmpImage = $file;
        $file = new File($file, $info['basename']);

        return $this->uploadSingleImage($entity, $file, $type, $request, $imageType, $objectName, $removeOldImage);
    }

    public function uploadSingleImageByPath(
        $entity,
        $path,
        $type,
        $request = null,
        $imageType = Image::TYPE_MAIN,
        $objectName = "Image",
        $removeOldImage = false
    ): bool|string|Image {
        $file = new File($path);

        return $this->uploadSingleImage($entity, $file, $type, $request, $imageType, $objectName, $removeOldImage);
    }

    public function uploadSingleImage(
        $entity,
        $file,
        $type,
        Request $request = null,
        $imageType = Image::TYPE_MAIN,
        $objectName = "Image",
        $removeOldImage = false
    ) {
        $validate = $this->validate($file, $type, $imageType, $request);
        if ($validate !== true) {
            return $validate;
        }

        $generatedImageName = $this->getGeneratedImageName($type, $entity);
        $uploadPath = $this->getUploadPath($type, $entity);

        // Remove old image if upload MainImage again
        $this->removeOldImage($entity, $imageType, $objectName, $removeOldImage);

        $image = $this->uploadImage($file, $imageType, $uploadPath, $generatedImageName);

        // resize the image and create thumbnail if found a thumbnail sizes in ImageSetting
        $this->resizeImageAndCreateThumbnail($image, $type, $imageType);

        $generatedImageAlt = $this->getImageAlt($type, $entity);
        $this->setImageAlt($image, $generatedImageAlt);


        $addFunctionName = $this->getAddFunctionName($objectName);
        if (method_exists($entity, $addFunctionName)) {
            $entity->{$addFunctionName}($image);
        } else {
            $setFunctionName = $this->getSetterFunctionName($objectName);
            $entity->{$setFunctionName}($image);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $image;
    }

    /**
     * Upload image from File class and create a Image Entity
     * @param File $file
     * @param int $imageType
     * @param string $uploadPath
     * @param string $imageName
     * @return Image
     */
    private function uploadImage(File $file, $imageType, $uploadPath, $imageName = null): Image
    {
        $image = new $this->imageClass();
        $this->em->persist($image);
        $this->em->flush();
        $image->setFile($file);
        $image->setImageType($imageType);
        $image->preUpload($imageName);
        $image->upload($uploadPath);

        return $image;
    }

    /**
     * Remove old image upload MainImage again
     *
     * @param Object $entity (Post Entity or any Entity has OneToOne relation like product, etc...
     * @param int $imageType (if MainImage, Gallery, etc..)
     * @return boolean Description
     */
    private function removeOldImage($entity, $imageType, $objectName, $removeOldImage): bool
    {
        if (!$this->isDeleteOldImage($imageType, $removeOldImage)) {
            return false;
        }
        $oldImage = null;
        $getFunctionName = $this->getGetterFunctionName($objectName);
        if (method_exists($entity, 'getImageByType')) {
            $oldImage = $entity->getImageByType($imageType);
        } elseif (method_exists($entity, $getFunctionName)) {
            $oldImage = $entity->{$getFunctionName}();
        }

        if ($oldImage) {
            $removeFunctionName = $this->getRemoveFunctionName($objectName);
            if (method_exists($entity, $removeFunctionName)) {
                $entity->{$removeFunctionName}($oldImage);
            } else {
                $setFunctionName = $this->getSetterFunctionName($objectName);
                $entity->{$setFunctionName}(null);
            }
            $this->em->remove($oldImage);
            $this->em->persist($entity);
            $this->em->flush();
        }

        return true;
    }

    private function isDeleteOldImage($imageType, $removeOldImage): bool
    {
        if ($imageType == Image::TYPE_MAIN) {
            return true;
        }
        if ($removeOldImage) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param type $imageSettingId
     * @return ImageSetting
     */
    private function getImageSetting($imageSettingId): ?ImageSetting
    {
        if ($this->imageSetting == null or $this->imageSetting->getId() != $imageSettingId) {
            $this->imageSetting = $this->em->getRepository(ImageSetting::class)->find($imageSettingId);
        }

        return $this->imageSetting;
    }

    private function getUploadPath($type, $entity): string
    {
        if (!$this->imagePaths->has($type)) {
            $imageSetting = $this->getImageSetting($type);
            $uploadPath = $imageSetting->getUploadPath();
        } else {
            $uploadPath = $this->imagePaths->get($type);
        }

        //        if (is_object($entity->getId()) and method_exists($entity->getId(), 'getId')) {
        //            $imageId = $entity->getId()->getId();
        //        } else {
        //            $imageId = $entity->getId();
        //        }

        return date("Y/m/d")."/".ltrim($uploadPath, "/").'image';
    }

    private function getClassName($object): ?string
    {
        $path = explode('\\', get_class($object));

        return array_pop($path);
    }

    /**
     *
     * @param int $type
     * @param type $entity
     * @return string|null
     */
    private function getImageAlt($type, $entity = null): ?string
    {
        $generatedImageAlt = null;

        if (!$this->imagePaths->has($type) and $entity !== null) {
            // if the entity instance of Post Entity
            $className = $this->getClassName($entity);
            if ($className == "Post") {
                $mainEntityId = $entity->getRelationalEntityId(); // Product, Category, etc
                $imageSetting = $this->getImageSetting($type);
                $entityName = $imageSetting->getEntityName();
                $generatedImageAlt = $this->getRawName($entityName, $mainEntityId, false);
            }
        }

        return $generatedImageAlt;
    }

    /**
     *
     * @param string $type
     * @param string $entity
     * @return string|null
     */
    private function getGeneratedImageName(string $type, $entity = null): ?string
    {
        $generatedImageName = null;

        if (!$this->imagePaths->has($type) and $entity !== null) {
            // if the entity instance of Post Entity
            $className = $this->getClassName($entity);
            if ($className == "Post") {
                $mainEntityId = $entity->getRelationalEntityId(); // Product, Category, etc
                $imageSetting = $this->getImageSetting($type);
                $entityName = $imageSetting->getEntityName();
                $generatedImageName = $this->getRawName($entityName, $mainEntityId);
            }
        }

        return $generatedImageName;
    }

    /**
     * Set image info like size and dimensions
     *
     * @param Image $image
     */
    private function setImageInfo(Image $image)
    {
        $originalPath = $image->getUploadRootDirWithFileName();
        $size = filesize($originalPath);
        list($width, $height) = getimagesize($originalPath);
        $image->setWidth($width);
        $image->setHeight($height);
        $image->setSize($size);
        $this->em->persist($image);
        $this->em->flush();
    }

    private function setImageAlt(Image $image, $alt = null): void
    {
        if ($alt == null) {
            return;
        }
        $image->setAlt($alt);
        $this->em->persist($image);
        $this->em->flush();
    }

    /**
     *  resize the image and create thumbnail
     * @param Image $image
     * @param int $type ImageSettingId
     * @param type $imageType MainImage, Gallery, etc...
     * @return boolean
     */
    public function resizeImageAndCreateThumbnail(Image $image, $type, $imageType): bool
    {
        $imageSetting = $this->getImageSetting($type);
        if (($imageSetting != null and $imageSetting->getAutoResize() == true) or $this->imageDimensions->has($type) == true) {
            $this->resizeOriginalImage($image, $type, $imageType);
            $this->createThumbnail($image, $type, $imageType);
        }
        $this->setImageInfo($image);

        return true;
    }

    private function resizeOriginalImage(Image $image, $type, $imageType): void
    {
        $quality = 75;
        if ($this->imageDimensions->has($type)) {
            $widthDefault = $this->imageDimensions->getWidth($type);
            $heightDefault = $this->imageDimensions->getHeight($type);
        } else {
            $imageSetting = $this->getImageSetting($type);
            $imageSettingWithType = $imageSetting->getTypeId($imageType);
            if ($imageSettingWithType === false) {
                return;
            }

            if ($imageSetting->getQuality() == ImageSetting::ORIGINAL_RESOLUTION) {
                $quality = 100;
            }
            $widthDefault = $imageSettingWithType->getWidth();
            $heightDefault = $imageSettingWithType->getHeight();
        }

        $originalPath = $image->getUploadRootDirWithFileName();
        list($width, $height) = getimagesize($originalPath);


        if (($widthDefault and $width > $widthDefault) || ($heightDefault and $height > $heightDefault)) {
            (new SimpleImage)->saveNewResizedImage($originalPath, $originalPath, $widthDefault, $heightDefault,
                $quality);
        }
    }

    private function createThumbnail(Image $image, $type, $imageType): void
    {
        if ($this->imagePaths->has($type)) {
            return;
        }

        $imageSetting = $this->getImageSetting($type);
        $imageSettingWithType = $imageSetting->getTypeId($imageType);
        if ($imageSettingWithType === false) {
            return;
        }
        $originalPath = $image->getUploadRootDirWithFileName();
        $thumbWidthDefault = $imageSettingWithType->getThumbWidth();
        $thumbHeightDefault = $imageSettingWithType->getThumbHeight();
        if ($thumbWidthDefault != null || $thumbHeightDefault != null) {
            $resize_2 = $image->getAbsoluteResizeExtension();
            SimpleImage::saveNewResizedImage($originalPath, $resize_2, $thumbWidthDefault, $thumbHeightDefault);
        }
    }

    /**
     * set error message
     *
     * @param string $message
     * @param Request $request
     * @return boolean
     */
    private function setFlashMessage($message, Request $request = null): bool|string
    {
        if ($request != null) {
            $request->getSession()->getFlashBag()->add('error', $message);

            return false;
        } else {
            return $message;
        }
    }

    public function validate($file, $type, $imageType, Request $request = null): bool|string
    {
        if ($file === null) {
            return false;
        }
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $this->allowMimeType)) {
            $message = "Filetype not allowed";

            return $this->setFlashMessage($message, $request);
        }
        if (getimagesize($file->getRealPath()) == false) {
            $message = "invalid Image type";

            return $this->setFlashMessage($message, $request);
        }

        $imageSetting = $this->getImageSetting($type);

        // validate image dimension
        if ($imageSetting != null) {
            $imageSettingWithType = $imageSetting->getTypeId($imageType);
            if ($imageSettingWithType !== false and $imageSettingWithType->getValidateWidthAndHeight() == true) {
                $height = $imageSettingWithType->getHeight();
                $width = $imageSettingWithType->getWidth();

                $validateImageDimension = $this->validateImageDimension($file, $width, $height);

                if (!$validateImageDimension) {
                    $message = "This image dimensions are wrong, please upload one with the right dimensions";

                    return $this->setFlashMessage($message, $request);
                }
            }
            if ($imageSettingWithType !== false and $imageSettingWithType->getValidateSize() == true) {

                $fileSize = $file->getSize();
                if ($fileSize > $this->maxUploadSize) {
                    $message = sprintf("The image uploaded must be max %s", "1MB");

                    return $this->setFlashMessage($message, $request);
                }
            }
        }

        if ($this->imageDimensions->has($type) == true and $this->imageDimensions->getValidateWidthAndHeight($type) == true) {

            $height = $this->imageDimensions->getHeight($type);
            $width = $this->imageDimensions->getWidth($type);

            $validateImageDimension = $this->validateImageDimension($file, $width, $height);

            if (!$validateImageDimension) {
                $message = "This image dimensions are wrong, please upload one with the right dimensions";

                return $this->setFlashMessage($message, $request);
            }
        }
        if ($this->imageDimensions->has($type) == true and $this->imageDimensions->getValidateSize($type) == true) {

            $fileSize = $file->getSize();

            if ($fileSize > $this->maxUploadSize) {
                $message = sprintf("The image uploaded must be max %s", "1MB");

                return $this->setFlashMessage($message, $request);
            }
        }

        return true;
    }

    private function validateImageDimension($file, $width = null, $height = null): bool
    {
        list($currentWidth, $currentHeight) = getimagesize($file->getRealPath());

        if ($width != null and $currentWidth != $width) {
            return false;
        }
        if ($height != null and $currentHeight != $height) {
            return false;
        }

        return true;
    }

    public function getRawName($entityName, $id = null, $sanitize = true): ?string
    {
        if ($id == null or $entityName == null) {
            return null;
        }
        $em = $this->em;
        $entities = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $className = null;
        foreach ($entities as $entity) {
            $loopEntityName = substr($entity, strrpos($entity, '\\') + 1);
            if (strpos($entity, 'PN\Bundle') === false or $loopEntityName != $entityName) {
                continue;
            }
            $path = explode('\Entity\\', $entity);
            $className = str_replace('\\', '', str_replace('PN\Bundle', '', $path[0])).':'.$path[1];
        }
        if ($className == null) {
            return null;
        }
        $entity = $em->find($className, $id);
        if (!$entity) {
            return null;
        }

        $title = null;

        if (method_exists($entity, "getTitle")) {
            $title = $entity->getTitle();
        } elseif (method_exists($entity, "getName")) {
            $title = $entity->getName();
        }

        if ($sanitize == true and $title != null) {
            return Slug::sanitize($title);
        }

        return $title;
    }

    public function deleteImage($entity, $image): void
    {
        if (method_exists($entity, 'removeImage')) {
            $entity->removeImage($image);
        } else {
            $entity->setImage(null);
        }
        $this->em->persist($entity);
        $this->em->flush();

        $this->em->remove($image);
        $this->em->flush();
    }

    private function getGetterFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Image" : $objectName;

        return "get".ucfirst($objectName);
    }

    private function getSetterFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Image" : $objectName;

        return "set".ucfirst($objectName);
    }

    private function getAddFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Image" : $objectName;

        return "add".ucfirst($objectName);
    }

    private function getRemoveFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Image" : $objectName;

        return "remove".ucfirst($objectName);
    }
}
