<?php

namespace PN\MediaBundle\Controller\Administration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use PN\MediaBundle\Entity\ImageSetting;
use PN\MediaBundle\Form\ImageSettingType;
use PN\ServiceBundle\Service\CommonFunctionService;
use PN\ServiceBundle\Service\ContainerParameterService;

/**
 * Image controller.
 *
 * @Route("/image-setting")
 */
class ImageSettingController extends Controller {

    /**
     * Lists all ImageSetting entities.
     *
     * @Route("/", name="imagesetting_index", methods={"GET"})
     */
    public function indexAction() {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $imageClass = $this->get(ContainerParameterService::class)->get('pn_media_image.image_class');
        $image = new $imageClass();
        $imageTypes = $image->getImageTypes();

        $em = $this->getDoctrine()->getManager();
        foreach ($imageTypes as $imageTypeId => $imageTypeTitle) {
            $imageType = $em->getRepository('PNMediaBundle:ImageType')->find($imageTypeId);
            if (!$imageType) {
                $imageType = new \PN\MediaBundle\Entity\ImageType;
                $imageType->setName($imageTypeTitle);
                $em->persist($imageType);
            }
        }
        $em->flush();
        return $this->render('PNMediaBundle:Administration/ImageSetting:index.html.twig');
    }

    /**
     * Creates a new Image entity.
     *
     * @Route("/new", name="imagesetting_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');


        $entities = $this->getEntitiesPostEntity();
        $routers = $this->get(CommonFunctionService::class)->getAllEditRoutes();

        $imageSetting = new ImageSetting();
        $form = $this->createForm(ImageSettingType::class, $imageSetting, ["entitiesNames" => $entities, "routes" => $routers]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userName = $this->get('user')->getUserName();
            $imageSetting->setCreator($userName);
            $imageSetting->setModifiedBy($userName);
            $em->persist($imageSetting);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('imagesetting_index');
        }

        return $this->render('PNMediaBundle:Administration/ImageSetting:new.html.twig', [
                    'entity' => $imageSetting,
                    'form' => $form->createView(),
                        ]
        );
    }

    /**
     * Edits an existing ImageSetting entity.
     *
     * @Route("/{id}/edit", name="imagesetting_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, ImageSetting $imageSetting) {

        $entities = $this->getEntitiesPostEntity();
        $routers = $this->get(CommonFunctionService::class)->getAllEditRoutes();
        $editForm = $this->createForm(ImageSettingType::class, $imageSetting, ["entitiesNames" => $entities, "routes" => $routers]);
        $editForm->handleRequest($request);


        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userName = $this->get('user')->getUserName();
            $imageSetting->setModifiedBy($userName);
            $em->flush();

            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('imagesetting_edit', array('id' => $imageSetting->getId()));
        }

        return $this->render('PNMediaBundle:Administration/ImageSetting:edit.html.twig', [
                    'imageSetting' => $imageSetting,
                    'edit_form' => $editForm->createView(),
                        ]
        );
    }

    /**
     * Lists all ImageSetting entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="imagesetting_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");

        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];

        $count = $em->getRepository('PNMediaBundle:ImageSetting')->filter($search, TRUE);
        $imageSettings = $em->getRepository('PNMediaBundle:ImageSetting')->filter($search, FALSE, $start, $length);

        return $this->render("PNMediaBundle:Administration/ImageSetting:datatable.json.twig", [
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "imageSettings" => $imageSettings,
                        ]
        );
    }

    private function getEntitiesPostEntity() {
        return $this->get(CommonFunctionService::class)->getEntitiesWithObject('post');
    }

}
