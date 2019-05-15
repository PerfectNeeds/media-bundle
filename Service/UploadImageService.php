<?php

namespace PN\MediaBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use PN\MediaBundle\Entity\Image,
    PN\MediaBundle\Utils\SimpleImage,
    PN\MediaBundle\Entity\ImageSetting,
    PN\MediaBundle\Service\ImagePaths;
use PN\ServiceBundle\Utils\Slug;

/**
 * Upload image
 *
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 * @version 1.0
 */
class UploadImageService {

    private $allowMimeType = array('image/gif', 'image/jpeg', 'image/jpg', 'image/png');
    protected $em;
    protected $container;
    protected $imageSetting;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
    }

    public function uploadSingleImageByPath($entity, $path, $type, $request = null, $imageType = Image::TYPE_MAIN) {
        $file = new File($path);
        return $this->uploadSingleImage($entity, $file, $type, $request, $imageType);
    }

    public function uploadSingleImage($entity, $file, $type, Request $request = null, $imageType = Image::TYPE_MAIN) {
        $validate = $this->validate($file, $request);
        if ($validate !== true) {
            return $validate;
        }

        $generatedImageName = $this->getGeneratedImageName($type, $entity);
        $uploadPath = $this->getUploadPath($type, $entity);

        // Remove old image if upload MainImage again
        $this->removeOldImage($entity, $imageType);

        $image = $this->uploadImage($file, $imageType, $uploadPath, $generatedImageName);

        // resize the image and create thumbnail if found a thumbnail sizes in ImageSetting
        $this->resizeImageAndCreateThumbnail($image, $type, $imageType);

        if (method_exists($entity, 'addImage')) {
            $entity->addImage($image);
        } else {
            $entity->setImage($image);
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
    private function uploadImage(File $file, $imageType, $uploadPath, $imageName = null) {
        $image = new Image();
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
    private function removeOldImage($entity, $imageType) {
        if ($imageType != Image::TYPE_MAIN) {
            return false;
        }
        if (method_exists($entity, 'getImageByType')) {
            $oldImage = $entity->getImageByType($imageType);
        } else {
            $oldImage = $entity->getImage();
        }

        if ($oldImage) {
            if (method_exists($entity, 'removeImage')) {
                $entity->removeImage($oldImage);
            } else {
                $entity->setImage(null);
            }
            $this->em->remove($oldImage);
            $this->em->persist($entity);
            $this->em->flush();
        }
        return true;
    }

    /**
     *
     * @param type $imageSettingId
     * @return ImageSetting
     */
    private function getImageSetting($imageSettingId) {
        if ($this->imageSetting == null or $this->imageSetting->getId() != $imageSettingId) {
            $this->imageSetting = $this->em->getRepository('MediaBundle:ImageSetting')->find($imageSettingId);
        }
        return $this->imageSetting;
    }

    private function getUploadPath($type, $entity) {
        if (!ImagePaths::has($type)) {
            $imageSetting = $this->getImageSetting($type);
            $uploadPath = $imageSetting->getUploadPath();
        } else {
            $uploadPath = ImagePaths::get($type);
        }

        if (method_exists($entity->getId(), 'getId')) {
            $imageId = $entity->getId()->getId();
        } else {
            $imageId = $entity->getId();
        }
        return $uploadPath . 'image/' . date("Y/m");
    }

    /**
     *
     * @param int $type
     * @param type $entity
     * @return string|null
     */
    private function getGeneratedImageName($type, $entity = null) {
        $generatedImageName = null;
        if (!ImagePaths::has($type) and $entity !== null) {

            // if the entity instance of Post Entity
            if (get_class($entity) == "Post") {
                $mainEntityId = $entity->getRelationalEntityId(); // Product, Category, etc
                $imageSetting = $this->getImageSetting($type);
                $entityName = $imageSetting->getEntityName();
                $generatedImageName = $this->getRawName($entityName, $mainEntityId);
            } else {
                $reflect = new \ReflectionClass($entity);
                $entityName = $reflect->getShortName();
            }
        }
        return $generatedImageName;
    }

    /**
     * Set image info like size and dimensions
     *
     * @param Image $image
     */
    private function setImageInfo(Image $image) {
        $orginalPath = $image->getUploadRootDirWithFileName();
        $size = filesize($orginalPath);
        list($width, $height) = getimagesize($orginalPath);
        $image->setWidth($width);
        $image->setHeight($height);
        $image->setSize($size);
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
    public function resizeImageAndCreateThumbnail(Image $image, $type, $imageType) {
        if (ImagePaths::has($type)) {
            return false;
        }
        $imageSetting = $this->getImageSetting($type);
        if ($imageSetting->getAutoResize() == true and $imageType == Image::TYPE_MAIN) {
            $this->resizeOriginalImage($image, $type, $imageType);
            $this->createThumbnail($image, $type, $imageType);
        }
        $this->setImageInfo($image);
        return TRUE;
    }

    private function resizeOriginalImage(Image $image, $type, $imageType) {
        $imageSetting = $this->getImageSetting($type);
        $imageSettingWithType = $imageSetting->getTypeId($imageType);
        if ($imageSettingWithType === null) {
            return false;
        }

        $quality = 75;
        if ($imageSetting->getQuality() == ImageSetting::ORIGINAL_RESOLUTION) {
            $quality = 100;
        }

        $originalPath = $image->getUploadRootDirWithFileName();
        list($width, $height) = getimagesize($originalPath);
        $widthDefault = $imageSettingWithType->getWidth();
        $heightDefault = $imageSettingWithType->getHeight();

        if (($widthDefault and $width > $widthDefault) || ($heightDefault and $height > $heightDefault)) {
            SimpleImage::saveNewResizedImage($originalPath, $originalPath, $widthDefault, $heightDefault, $quality);
        }
    }

    private function createThumbnail(Image $image, $type, $imageType) {
        $imageSetting = $this->getImageSetting($type);
        $imageSettingWithType = $imageSetting->getTypeId($imageType);
        if ($imageSettingWithType === null) {
            return false;
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
    private function setFlashMessage($message, Request $request = null) {
        if ($request != null) {
            $request->getSession()->getFlashBag()->add('error', $message);
            return false;
        } else {
            return $message;
        }
    }

    private function validate($file, Request $request = null) {
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
        return true;
    }

    public function getRawName($entityName, $id = null) {
        if ($id == null or $entityName == null) {
            return null;
        }
        $em = $this->em;
        $entities = $em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
        $className = null;
        foreach ($entities as $entity) {
            $loopEntityName = substr($entity, strrpos($entity, '\\') + 1);
            if (strpos($entity, 'PN\Bundle') === false OR $loopEntityName != $entityName) {
                continue;
            }
            $path = explode('\Entity\\', $entity);
            $className = str_replace('\\', '', str_replace('PN\Bundle', '', $path[0])) . ':' . $path[1];
        }
        if ($className == null) {
            return null;
        }
        $entity = $em->find($className, $id);
        if (!$entity) {
            return null;
        }

        if (method_exists($entity, "getTitle")) {
            return Slug::sanitize($entity->getTitle());
        } elseif (method_exists($entity, "getName")) {
            return Slug::sanitize($entity->getName());
        }
        return null;
    }

    public function deleteImage($entity, $image) {


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

}