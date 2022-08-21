<?php

namespace PN\MediaBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PN\MediaBundle\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Download controller.
 *
 * @Route("/download")
 */
class DownloadController extends AbstractController
{

    private $documentId; // document id from document Entity

    public function __construct()
    {
        $request = Request::createFromGlobals();
        $getParameter = str_replace("'", '"',
            $request->query->get('d')); // ex: {{ path('download', {'d': '{"document":'document.id'}'}) }}
        $parameter = json_decode($getParameter, true);
        $this->documentId = $parameter['document'];
    }

    public function getDownloadNameAndPath(EntityManagerInterface $em)
    {
        if ($this->documentId == null) {
            throw $this->createNotFoundException();
        }
        $entity = $em->getRepository(Document::class)->find($this->documentId);
        if (!$entity) {
            throw $this->createNotFoundException();
        }
        $fileName = $entity->getName();
        $originalEntity = $entity->getRelationalEntity();
        if ($originalEntity) {
            if (method_exists($originalEntity, "getTitle")) {
                $fileName = $originalEntity->getTitle().".".$entity->getNameExtension();
            } elseif (method_exists($originalEntity, "getName")) {
                $fileName = $originalEntity->getName().".".$entity->getNameExtension();
            }
        }
        $return = new \stdClass();


        $return->name = $fileName;
        $return->path = $entity->getAssetPath();

        return $return;
    }

    /**
     * test page.
     *
     * @Route("/", name="download", methods={"GET"})
     */
    public function downloadAction(EntityManagerInterface $em)
    {
        $nameAndPath = $this->getDownloadNameAndPath($em);
        $path = $nameAndPath->path;
        $name = str_replace("/", "-", $nameAndPath->name);
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        return $this->file($path, $name);
    }

}
