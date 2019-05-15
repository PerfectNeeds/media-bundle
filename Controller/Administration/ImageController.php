<?php

namespace PN\MediaBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use PN\Bundle\CMSBundle\Entity\Post;

/**
 * Image controller.
 *
 * @Route("/image")
 */
class ImageController extends Controller {

    /**
     * Deletes a tasklist entity.
     *
     * @Route("/sort/{post}", name="image_sort", methods={"POST"})
     */
    public function sortAction(Request $request, Post $post) {
        if (!$post) {
            $return = ['error' => 0, "message" => 'Error'];
            return new JsonResponse($return);
        }
        $em = $this->getDoctrine()->getManager();
        $listJson = $request->request->get('json');
        $sortedList = json_decode($listJson);
        $i = 1;
        foreach ($sortedList as $key => $value) {
            if (!array_key_exists($key, $sortedList)) {
                continue;
            }
            $sortedListNod = $sortedList[$key];
            foreach ($sortedListNod as $keyNod => $valueNod) {
                if (!array_key_exists($key, $sortedList)) {
                    continue;
                }
                if (!isset($valueNod->id)) {
                    continue;
                }
                $image = $em->getRepository('MediaBundle:Image')->find($valueNod->id);
                if ($image->getFirstPost()->getId() != $post->getId()) {
                    continue;
                }
                $image->setTarteb($i);
                $em->persist($image);
                $i++;
            }
        }
        $em->flush();

        $return = [
            'error' => 0,
            'message' => 'Successfully sorted',
        ];
        return new JsonResponse($return);
    }

}
