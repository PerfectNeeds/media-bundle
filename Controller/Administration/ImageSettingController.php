<?php

namespace PN\MediaBundle\Controller\Administration;

use Doctrine\ORM\EntityManagerInterface;
use PN\MediaBundle\Entity\ImageType;
use PN\ServiceBundle\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
class ImageSettingController extends AbstractController
{

    /**
     * Lists all ImageSetting entities.
     *
     * @Route("/", name="imagesetting_index", methods={"GET"})
     */
    public function indexAction(
        Request $request,
        ContainerParameterService $containerParameterService,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $imageClass = $containerParameterService->get('pn_media_image.image_class');
        $image = new $imageClass();
        $imageTypes = $image->getImageTypes();

        foreach ($imageTypes as $imageTypeId => $imageTypeTitle) {
            $imageType = $em->getRepository(ImageType::class)->find($imageTypeId);
            if (!$imageType) {
                $imageType = new ImageType();
                $imageType->setName($imageTypeTitle);
                $em->persist($imageType);
            }
        }
        $em->flush();

        return $this->render('@PNMedia/Administration/ImageSetting/index.html.twig');
    }

    /**
     * Creates a new Image entity.
     *
     * @Route("/new", name="imagesetting_new", methods={"GET", "POST"})
     */
    public function newAction(
        Request $request,
        UserService $userService,
        CommonFunctionService $commonFunctionService,
        EntityManagerInterface $em
    ) {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');


        $entities = $this->getEntitiesPostEntity($commonFunctionService);
        $routers = $commonFunctionService->getAllEditRoutes();

        $imageSetting = new ImageSetting();
        $form = $this->createForm(ImageSettingType::class, $imageSetting,
            ["entitiesNames" => $entities, "routes" => $routers]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $userService->getUserName();
            $imageSetting->setCreator($userName);
            $imageSetting->setModifiedBy($userName);
            $em->persist($imageSetting);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('imagesetting_index');
        }

        return $this->render('@PNMedia/Administration/ImageSetting/new.html.twig', [
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
    public function editAction(
        Request $request,
        ImageSetting $imageSetting,
        UserService $userService,
        CommonFunctionService $commonFunctionService,
        EntityManagerInterface $em
    ) {

        $entities = $this->getEntitiesPostEntity($commonFunctionService);
        $routers = $commonFunctionService->getAllEditRoutes();
        $editForm = $this->createForm(ImageSettingType::class, $imageSetting,
            ["entitiesNames" => $entities, "routes" => $routers]);
        $editForm->handleRequest($request);


        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userName = $userService->getUserName();
            $imageSetting->setModifiedBy($userName);
            $em->flush();

            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('imagesetting_edit', array('id' => $imageSetting->getId()));
        }

        return $this->render('@PNMedia/Administration/ImageSetting/edit.html.twig', [
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
    public function dataTableAction(Request $request, EntityManagerInterface $em)
    {
        $srch = $request->query->all("search");
        $start = $request->query->getInt("start");
        $length = $request->query->getInt("length");
        $ordr = $request->query->all("order");

        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];

        $count = $em->getRepository(ImageSetting::class)->filter($search, true);
        $imageSettings = $em->getRepository(ImageSetting::class)->filter($search, false, $start, $length);

        return $this->render("@PNMedia/Administration/ImageSetting/datatable.json.twig", [
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "imageSettings" => $imageSettings,
            ]
        );
    }

    private function getEntitiesPostEntity(CommonFunctionService $commonFunctionService)
    {
        return $commonFunctionService->getEntitiesWithObject('post');
    }

}
