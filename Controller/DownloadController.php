<?php
namespace PN\MediaBundle\Controller;
use PN\Bundle\MediaBundle\MediaBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $getParameter = $request->query->get('d'); // ex: {{ path('download', {'d': '{"document":'~document.id~'}'}) }}
        $parameter = json_decode($getParameter, true);
        $this->documentId = $parameter['document'];
    }
    public function getDownloadNameAndPath()
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('MediaBundle:Document')->find($this->documentId);
        $return = new \stdClass();
        $return->name = $entity->getName();
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
        $name = $nameAndPath->name;
        return $this->file($path, $name);
    }
}