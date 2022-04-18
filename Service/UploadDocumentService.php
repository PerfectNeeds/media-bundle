<?php

namespace PN\MediaBundle\Service;

use PN\MediaBundle\Entity\Document;
use PN\ServiceBundle\Service\ContainerParameterService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Upload Documents
 * Document
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 * @version 1.0
 */
class UploadDocumentService
{

    private $allowMimeType = [];
    private $documentClass;
    private $documentPaths;
    private $em;
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->allowMimeType = $container->get(ContainerParameterService::class)->get('pn_media_document.mime_types');
        $this->documentClass = $container->get(ContainerParameterService::class)->get('pn_media_document.document_class');
        $this->documentPaths = $container->get(DocumentPaths::class);
    }

    public function getMimeTypes()
    {
        return $this->allowMimeType;
    }

    public function uploadSingleDocumentByPath(
        $entity,
        $path,
        $type,
        $request = null,
        array $mimeTypes = null,
        $objectName = null
    ) {
        if ($mimeTypes != null) {
            return $this->allowMimeType = $mimeTypes;
        }
        $file = new File($path);

        return $this->uploadSingleDocument($entity, $file, $type, $request, $objectName);
    }

    public function uploadSingleDocument(
        $entity,
        $file,
        $type,
        Request $request = null,
        $objectName = null,
        array $mimeTypes = null,
        $removeOldImage = true
    ) {
        if ($mimeTypes != null) {
            $this->allowMimeType = $mimeTypes;
        }
        $validate = $this->validate($file, $type, $request);
        if ($validate !== true) {
            return $validate;
        }

        $uploadPath = $this->getUploadPath($type, $entity);

        // Remove old document
        $this->removeOldDocument($entity, $objectName, $removeOldImage);


        $document = $this->uploadDocument($file, $uploadPath);
        $this->setDocumentInfo($document);


        $addFunctionName = $this->getAddFunctionName($objectName);

        if (method_exists($entity, $addFunctionName)) {
            $entity->{$addFunctionName}($document);
        } else {
            $setFunctionName = $this->getSetterFunctionName($objectName);
            $entity->{$setFunctionName}($document);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $document;
    }

    /**
     * Upload document from File class and create a Document Entity
     * @param File $file
     * @param string $uploadPath
     * @return Document
     */
    private function uploadDocument(File $file, $uploadPath)
    {
        $document = new $this->documentClass();
        $this->em->persist($document);
        $this->em->flush();
        $document->setFile($file);
        $document->preUpload();
        $document->upload($uploadPath);

        return $document;
    }


    /**
     * Remove old document upload MainDocument again
     *
     * @param Object $entity (Post Entity or any Entity has OneToOne relation like product, etc...
     * @param null $objectName
     * @param bool $removeOldImage
     * @return bool
     * @throws \Exception
     */
    private function removeOldDocument($entity, $objectName = null, $removeOldImage = true)
    {
        if (!$removeOldImage) {
            return false;
        }
        $getFunctionName = $this->getGetterFunctionName($objectName);

        if (!method_exists($entity, $getFunctionName)) {
            throw  new \Exception("This method ".$getFunctionName."() is not exist");
        }
        $oldDocument = $entity->{$getFunctionName}();

        // remove old document before upload new one
        if ($oldDocument) {
            $removeFunctionName = $this->getRemoveFunctionName($objectName);
            if (method_exists($entity, $removeFunctionName)) {
                $entity->{$removeFunctionName}($oldDocument);
            } else {
                $setFunctionName = $this->getSetterFunctionName($objectName);
                $entity->{$setFunctionName}(null);
            }
            $this->em->remove($oldDocument);
            $this->em->persist($entity);
            $this->em->flush();
        }

        return true;
    }

    private function getUploadPath($type, $entity)
    {
        if (!$this->documentPaths->has($type)) {
            return new \Exception("Document type is not exist");
        }
        $uploadPath = $this->documentPaths->get($type);
        if (method_exists($entity->getId(), 'getId')) {
            $documentId = $entity->getId()->getId();
        } else {
            $documentId = $entity->getId();
        }

        return date("Y/m/d")."/".ltrim($uploadPath, "/").'document';
    }

    /**
     * Set document info like size and dimensions
     *
     * @param Document $document
     */
    private function setDocumentInfo(Document $document)
    {
        $originalPath = $document->getUploadRootDirWithFileName();
        $size = filesize($originalPath);
        $document->setSize($size);
        $this->em->persist($document);
        $this->em->flush();
    }

    /**
     * set error message
     *
     * @param string $message
     * @param Request $request
     * @return boolean
     */
    private function setFlashMessage($message, Request $request = null)
    {
        if ($request != null) {
            $request->getSession()->getFlashBag()->add('error', $message);

            return false;
        } else {
            return $message;
        }
    }

    public function validate($file, $type, Request $request = null)
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
        if (!$this->documentPaths->has($type)) {
            $message = "invalid Document type";

            return $this->setFlashMessage($message, $request);
        }

        return true;
    }

    public function deleteDocument($entity, $document, $objectName = null)
    {
        $removeFunctionName = $this->getRemoveFunctionName($objectName);
        if (method_exists($entity, $removeFunctionName)) {
            $entity->{$removeFunctionName}($document);
        } else {
            $setFunctionName = $this->getSetterFunctionName($objectName);
            $entity->{$setFunctionName}(null);
        }

        $this->em->persist($entity);
        $this->em->flush();

        $this->em->remove($document);
        $this->em->flush();
    }

    private function getGetterFunctionName($objectName = null)
    {
        $objectName = ($objectName == null) ? "Document" : $objectName;

        return "get".ucfirst($objectName);
    }

    private function getSetterFunctionName($objectName = null)
    {
        $objectName = ($objectName == null) ? "Document" : $objectName;

        return "set".ucfirst($objectName);
    }

    private function getAddFunctionName($objectName = null)
    {
        $objectName = ($objectName == null) ? "Document" : $objectName;

        return "add".ucfirst($objectName);
    }

    private function getRemoveFunctionName($objectName = null)
    {
        $objectName = ($objectName == null) ? "Document" : $objectName;

        return "remove".ucfirst($objectName);
    }

}
