<?php

namespace PN\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Download controller.
 *
 * @Route("/download")
 */
class DownloadController extends Controller
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

    public function getDownloadNameAndPath()
    {
        $em = $this->getDoctrine()->getManager();
        if($this->documentId == null){
            throw $this->createNotFoundException();
        }
        $entity = $em->getRepository('MediaBundle:Document')->find($this->documentId);
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
    public function DownloadAction()
    {
        $nameAndPath = $this->getDownloadNameAndPath();
        $path = $nameAndPath->path;
        $name = str_replace("/", "-", $nameAndPath->name);

        return $this->file($path, $name);
    }

}
