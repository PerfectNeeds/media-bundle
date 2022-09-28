<?php

namespace PN\MediaBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PN\ServiceBundle\Service\ContainerParameterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Download controller.
 *
 * @Route("/download")
 */
class DownloadController extends AbstractController
{
    private EntityManagerInterface $em;
    private int $documentId; // document id from document Entity
    private string $documentClass;

    public function __construct(ContainerParameterService $containerParameterService, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->documentClass = $containerParameterService->get('pn_media_document.document_class');
    }


    /**
     * @Route("/", name="download", methods={"GET"})
     */
    public function download(Request $request): Response
    {
        $nameAndPath = $this->getDownloadNameAndPath($request);
        $path = $nameAndPath->path;
        $name = str_replace("/", "-", $nameAndPath->name);
        if (!file_exists($path)) {
            throw $this->createNotFoundException();
        }

        return $this->file($path, $name);
    }

    private function getDownloadNameAndPath(Request $request): \stdClass
    {
        $this->extractDataFromRequest($request);
        if ($this->documentId == null) {
            throw $this->createNotFoundException();
        }
        $entity = $this->em->getRepository($this->documentClass)->find($this->documentId);
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

    private function extractDataFromRequest(Request $request): void
    {
        $getParameter = str_replace("'", '"',
            $request->query->get('d')); // ex: {{ path('download', {'d': '{"document":'document.id'}'}) }}
        $parameter = json_decode($getParameter, true);
        $this->documentId = $parameter['document'];
    }
}
