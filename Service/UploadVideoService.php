<?php

namespace PN\MediaBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use PN\MediaBundle\Entity\Video;
use PN\ServiceBundle\Service\ContainerParameterService;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Upload Videos
 * Video
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 * @version 1.0
 */
class UploadVideoService
{

    private $allowMimeType = [];
    private $videoClass;
    private $videoPaths;
    private $em;

    public function __construct(
        ContainerParameterService $containerParameterService,
        EntityManagerInterface $em,
        VideoPaths $videoPaths
    ) {
        $this->em = $em;
        $this->allowMimeType = $containerParameterService->get('pn_media_video.mime_types');
        $this->videoClass = $containerParameterService->get('pn_media_video.video_class');
        $this->videoPaths = $videoPaths;
    }

    public function getMimeTypes()
    {
        return $this->allowMimeType;
    }

    public function uploadSingleVideoByPath(
        $entity,
        $path,
        $type,
        $request = null,
        array $mimeTypes = null,
        $objectName = null
    ): Video|bool|array|string {
        if ($mimeTypes != null) {
            return $this->allowMimeType = $mimeTypes;
        }
        $file = new File($path);

        return $this->uploadSingleVideo($entity, $file, $type, $request, $objectName);
    }

    public function uploadSingleVideo(
        $entity,
        $file,
        $type,
        Request $request = null,
        $objectName = null,
        array $mimeTypes = null,
        $removeOldImage = true
    ): Video|bool|string {
        if ($mimeTypes != null) {
            $this->allowMimeType = $mimeTypes;
        }
        $validate = $this->validate($file, $type, $request);
        if ($validate !== true) {
            return $validate;
        }

        $uploadPath = $this->getUploadPath($type, $entity);

        // Remove old video
        $this->removeOldVideo($entity, $objectName, $removeOldImage);


        $video = $this->uploadVideo($file, $uploadPath);
        $this->setVideoInfo($video);


        $addFunctionName = $this->getAddFunctionName($objectName);

        if (method_exists($entity, $addFunctionName)) {
            $entity->{$addFunctionName}($video);
        } else {
            $setFunctionName = $this->getSetterFunctionName($objectName);
            $entity->{$setFunctionName}($video);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $video;
    }

    /**
     * Upload video from File class and create a Video Entity
     * @param File $file
     * @param string $uploadPath
     * @return Video
     */
    private function uploadVideo(File $file, string $uploadPath): Video
    {
        $video = new $this->videoClass();
        $this->em->persist($video);
        $this->em->flush();
        $video->setFile($file);
        $video->preUpload();
        $video->upload($uploadPath);

        return $video;
    }


    /**
     * Remove old video upload MainVideo again
     *
     * @param Object $entity (Post Entity or any Entity has OneToOne relation like product, etc...
     * @param null $objectName
     * @param bool $removeOldImage
     * @return bool
     * @throws \Exception
     */
    private function removeOldVideo($entity, $objectName = null, $removeOldImage = true): bool
    {
        if (!$removeOldImage) {
            return false;
        }
        $getFunctionName = $this->getGetterFunctionName($objectName);

        if (!method_exists($entity, $getFunctionName)) {
            throw  new \Exception("This method ".$getFunctionName."() is not exist");
        }
        $oldVideo = $entity->{$getFunctionName}();

        // remove old video before upload new one
        if ($oldVideo) {
            $removeFunctionName = $this->getRemoveFunctionName($objectName);
            if (method_exists($entity, $removeFunctionName)) {
                $entity->{$removeFunctionName}($oldVideo);
            } else {
                $setFunctionName = $this->getSetterFunctionName($objectName);
                $entity->{$setFunctionName}(null);
            }
            $this->em->remove($oldVideo);
            $this->em->persist($entity);
            $this->em->flush();
        }

        return true;
    }

    private function getUploadPath($type, $entity)
    {
        if (!$this->videoPaths->has($type)) {
            return new \Exception("Video type is not exist");
        }
        $uploadPath = $this->videoPaths->get($type);
        //        if (is_object($entity->getId()) and method_exists($entity->getId(), 'getId')) {
        //            $videoId = $entity->getId()->getId();
        //        } else {
        //            $videoId = $entity->getId();
        //        }

        return date("Y/m/d")."/".ltrim($uploadPath, "/").'video';
    }

    /**
     * Set video info like size and dimensions
     *
     * @param Video $video
     */
    private function setVideoInfo(Video $video)
    {
        $originalPath = $video->getUploadRootDirWithFileName();
        $size = filesize($originalPath);
        $video->setSize($size);
        $this->em->persist($video);
        $this->em->flush();
    }

    /**
     * set error message
     *
     * @param string $message
     * @param Request|null $request
     * @return boolean|string
     */
    private function setFlashMessage(string $message, Request $request = null): bool|string
    {
        if ($request != null) {
            $request->getSession()->getFlashBag()->add('error', $message);

            return false;
        } else {
            return $message;
        }
    }

    public function validate($file, $type, Request $request = null): bool|string
    {
        if ($file === null) {
            return false;
        }
        $mimeType = $file->getMimeType();
        if (method_exists($file, 'getClientMimeType')) {
            $mimeType = $file->getClientMimeType();
        }

        if (!in_array($mimeType, $this->allowMimeType)) {
            $message = "Filetype not allowed";

            return $this->setFlashMessage($message, $request);
        }
        if (!$this->videoPaths->has($type)) {
            $message = "invalid Video type";

            return $this->setFlashMessage($message, $request);
        }

        return true;
    }

    public function deleteVideo($entity, $video, $objectName = null)
    {
        $removeFunctionName = $this->getRemoveFunctionName($objectName);
        if (method_exists($entity, $removeFunctionName)) {
            $entity->{$removeFunctionName}($video);
        } else {
            $setFunctionName = $this->getSetterFunctionName($objectName);
            $entity->{$setFunctionName}(null);
        }

        $this->em->persist($entity);
        $this->em->flush();

        $this->em->remove($video);
        $this->em->flush();
    }

    private function getGetterFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Video" : $objectName;

        return "get".ucfirst($objectName);
    }

    private function getSetterFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Video" : $objectName;

        return "set".ucfirst($objectName);
    }

    private function getAddFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Video" : $objectName;

        return "add".ucfirst($objectName);
    }

    private function getRemoveFunctionName($objectName = null): string
    {
        $objectName = ($objectName == null) ? "Video" : $objectName;

        return "remove".ucfirst($objectName);
    }

}
