<?php

namespace PN\MediaBundle\Controller\Administration;

use Doctrine\ORM\EntityManagerInterface;
use PN\MediaBundle\Entity\ImageSetting;
use PN\MediaBundle\Form\ImageSettingTypeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PN\MediaBundle\Entity\ImageSettingHasType;

/**
 * @Route("/image-setting")
 */
class ImageSettingTypeController extends AbstractController
{

    /**
     * @Route("/{id}", requirements={"id" = "\d+"}, name="imagesetting_type_index", methods={"GET"})
     */
    public function indexAction(ImageSetting $imageSetting): Response
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        return $this->render('@PNMedia/Administration/ImageSettingType/index.html.twig',
            ['imageSetting' => $imageSetting]);
    }

    /**
     * @Route("{id}/new", requirements={"id" = "\d+"}, name="imagesetting_type_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, ImageSetting $imageSetting, EntityManagerInterface $em): Response
    {

        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $imageSettingHasType = new ImageSettingHasType();
        $form = $this->createForm(ImageSettingTypeType::class, $imageSettingHasType);
        $form->handleRequest($request);
        $imageSettingHasType->setImageSetting($imageSetting);


        $checkIfExist = $em->getRepository(ImageSettingHasType::class)->findOneBy(array(
            'imageSetting' => $imageSettingHasType->getImageSetting(),
            'imageType' => $imageSettingHasType->getImageType(),
        ));
        if ($checkIfExist != null) {
            $this->addFlash('error', 'This record is already exist');
        }
        if ($form->isSubmitted() && $form->isValid() && $checkIfExist == null) {

            $em->persist($imageSettingHasType);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('imagesetting_type_index', ['id' => $imageSetting->getId()]);
        }

        return $this->render('@PNMedia/Administration/ImageSettingType/new.html.twig', [
                'entity' => $imageSetting,
                'form' => $form->createView(),
                'imageSetting' => $imageSetting,
            ]
        );
    }

    /**
     * Edits an existing ImageSettingType entity.
     *
     * @Route("/{imageSettingId}/{imageTypeId}/edit", requirements={"imageSettingId" = "\d+", "imageTypeId" = "\d+"}, name="imagesetting_type_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, $imageSettingId, $imageTypeId, EntityManagerInterface $em): Response
    {
        $imageSettingHasType = $em->getRepository(ImageSettingHasType::class)->findOneBy(array(
            'imageSetting' => $imageSettingId,
            'imageType' => $imageTypeId,
        ));
        if (!$imageSettingHasType) {
            throw $this->createNotFoundException();
        }
        $editForm = $this->createForm(ImageSettingTypeType::class, $imageSettingHasType);

        $editForm->handleRequest($request);


        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Successfully updated');

            return $this->redirect($this->generateUrl('imagesetting_type_edit',
                ['imageSettingId' => $imageSettingId, 'imageTypeId' => $imageTypeId]));
        }

        return $this->render('@PNMedia/Administration/ImageSettingType/edit.html.twig', [
                'imageSettingHasType' => $imageSettingHasType,
                'edit_form' => $editForm->createView(),
            ]
        );
    }

    /**
     * Deletes an imageSettingType entity.
     *
     * @Route("/{imageSettingId}/{imageTypeId}", requirements={"id" = "\d+"}, name="imagesetting_type_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, $imageSettingId, $imageTypeId, EntityManagerInterface $em): Response
    {
        $imageSettingHasType = $em->getRepository(ImageSettingHasType::class)->findOneBy(array(
            'imageSetting' => $imageSettingId,
            'imageType' => $imageTypeId,
        ));

        if (!$imageSettingHasType) {
            throw $this->createNotFoundException('Unable to find ImageSettingHasType entity.');
        }
        $em->remove($imageSettingHasType);
        $em->flush();

        return $this->redirectToRoute('imagesetting_type_index', ['id' => $imageSettingId]);
    }

    /**
     * Lists all ImageSetting entities.
     *
     * @Route("/data/table/{id}", defaults={"_format": "json"}, name="imagesetting_type_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request, ImageSetting $imageSetting, EntityManagerInterface $em): Response
    {

        $srch = $request->query->all("search");
        $start = $request->query->getInt("start");
        $length = $request->query->getInt("length");
        $ordr = $request->query->all("order");

        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];
        $search->imageSetting = $imageSetting->getId();

        $count = $em->getRepository(ImageSettingHasType::class)->filter($search, true);
        $imageSettingTypes = $em->getRepository(ImageSettingHasType::class)->filter($search, false, $start,
            $length);

        return $this->render("@PNMedia/Administration/ImageSettingType/datatable.json.twig", [
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "imageSettingTypes" => $imageSettingTypes,
                "imageSetting" => $imageSetting,
            ]
        );
    }

}
