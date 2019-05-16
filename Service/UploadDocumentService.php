<?php

namespace PN\MediaBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use PN\MediaBundle\Service\DocumentPaths;
use PN\ServiceBundle\Service\ContainerParameterService;

/**
 * Upload Documents
 * Document
 * @author Peter Nassef <peter.nassef@perfectneeds.com>
 * @version 1.0
 */
class UploadDocumentService {

    private $allowMimeType = [];
    private $documentClass;
    private $documentPaths;
    private $em;
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->allowMimeType = $container->get(ContainerParameterService::class)->get('pn_media_document.mime_types');
        $this->documentClass = $container->get(ContainerParameterService::class)->get('pn_media_document.document_class');
        $this->documentPaths = $container->get(DocumentPaths::class);
    }

    public function getMimeTypes() {
        return $this->allowMimeType;
    }

    public function uploadSingleDocumentByPath($entity, $path, $type, $request = null) {
        $file = new File($path);
        return $this->uploadSingleDocument($entity, $file, $type, $request);
    }

    public function uploadSingleDocument($entity, $file, $type, Request $request = null) {
        $validate = $this->validate($file, $type, $request);
        if ($validate !== true) {
            return $validate;
        }

        $uploadPath = $this->getUploadPath($type, $entity);

        // Remove old document
        $this->removeOldDocument($entity);


        $document = $this->uploadDocument($file, $uploadPath);
        $this->setDocumentInfo($document);

        if (method_exists($entity, 'addDocument')) {
            $entity->addDocument($document);
        } else {
            $entity->setDocument($document);
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
    private function uploadDocument(File $file, $uploadPath) {
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
     * @return boolean Description
     */
    private function removeOldDocument($entity) {
        $oldDocument = $entity->getDocument();

        if ($oldDocument) {
            if (method_exists($entity, 'removeDocument')) {
                $entity->removeDocument($oldDocument);
            } else {
                $entity->setDocument(null);
            }
            $this->em->remove($oldDocument);
            $this->em->persist($entity);
            $this->em->flush();
        }
        return true;
    }

    private function getUploadPath($type, $entity) {
        if (!$this->documentPaths->has($type)) {
            return new \Exception("Document type is not exist");
        }
        $uploadPath = $this->documentPaths->get($type);
        if (method_exists($entity->getId(), 'getId')) {
            $documentId = $entity->getId()->getId();
        } else {
            $documentId = $entity->getId();
        }
        return $uploadPath . 'document/' . date("Y/m");
    }

    /**
     * Set document info like size and dimensions
     *
     * @param Document $document
     */
    private function setDocumentInfo(Document $document) {
        $orginalPath = $document->getUploadRootDirWithFileName();
        $size = filesize($orginalPath);
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
    private function setFlashMessage($message, Request $request = null) {
        if ($request != null) {
            $request->getSession()->getFlashBag()->add('error', $message);
            return false;
        } else {
            return $message;
        }
    }

    private function validate($file, $type, Request $request = null) {
        if ($file === null) {
            return false;
        }
        $mimeType = $file->getMimeType();
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

    public function deleteDocument($entity, $image) {
        if (method_exists($entity, 'removeDocument')) {
            $entity->removeDocument($image);
        } else {
            $entity->setDocument(null);
        }
        $this->em->persist($entity);
        $this->em->flush();

        $this->em->remove($image);
        $this->em->flush();
    }

}
