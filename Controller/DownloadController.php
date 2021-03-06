<?php

namespace PN\MediaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Download controller.
 *
 * @Route("/download")
 */
class DownloadController extends Controller {

    private $documentId; // document id from document Entity

    public function __construct() {
        $request = Request::createFromGlobals();
        $getParameter = str_replace("'", '"', $request->query->get('d')); // ex: {{ path('download', {'d': '{"document":'document.id'}'}) }}
        $parameter = json_decode($getParameter, true);
        $this->documentId = $parameter['document'];
    }

    public function getDownloadNameAndPath() {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MediaBundle:Document')->find($this->documentId);
        $fileName = $entity->getName();
        $orginalEntity = $entity->getRelationalEntity();
        if ($orginalEntity) {
            if (method_exists($orginalEntity, "getTitle")) {
                $fileName = $orginalEntity->getTitle() . "." . $entity->getNameExtension();
            } elseif (method_exists($orginalEntity, "getName")) {
                $fileName = $orginalEntity->getName() . "." . $entity->getNameExtension();
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
    public function DownloadAction() {
        $nameAndPath = $this->getDownloadNameAndPath();
        $path = $nameAndPath->path;
        $name = $nameAndPath->name;
        return $this->file($path, $name);
    }

}
