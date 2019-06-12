<?php

namespace PN\MediaBundle\Repository;

use PN\MediaBundle\Entity\Image as Image;
use Doctrine\ORM\EntityRepository;

class ImageRepository extends EntityRepository {

    public function setMainImage($entityType, $entityId, $image, $newImageType = Image::TYPE_MAIN) {
        if ($newImageType == Image::TYPE_MAIN) {
            $this->clearMain($entityType, $entityId, $newImageType);
        }

        $em = $this->getEntityManager();
        $image->setImageType($newImageType);
        $em->persist($image);
        $em->flush();

        return $image;
    }

    public function clearMain($entityType, $entityId, $newImageType) {
        switch ($entityType) {
            case "CMSBundle:Post":
            case "PNContentBundle:Post":
                $SQLTable = "post_image";
                $SQLColumn = "t.post_id";
                break;
        }
        $sql = "UPDATE image i JOIN $SQLTable t on i.id = t.image_id "
                . "SET i.image_type = " . Image::TYPE_TEMP
                . " WHERE i.image_type = " . $newImageType . " and $SQLColumn = ?  ";
        $this->getEntityManager()->getConnection()
                ->executeQuery($sql, array($entityId));
    }

    public function getLimitedImageByPost($postId, $limit) {

        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT i.id FROM image i "
                . "RIGHT OUTER JOIN post_image pi ON i.id=pi.image_id "
                . "WHERE i.image_type=:imageType and pi.post_id=:postId "
                . "GROUP BY i.id ORDER BY i.id ASC Limit $limit";
        $statement = $connection->prepare($sql);
        $statement->bindValue("imageType", Image::TYPE_GALLERY);
        $statement->bindValue("postId", $postId);
        $statement->execute();
        $queryResult = $statement->fetchAll();
        if (count($queryResult) == 0) {
            return NULL;
        }
        $result = array();
        foreach ($queryResult as $key => $r) {
            $result[$key] = $this->getEntityManager()->getRepository('MediaBundle:Image')->find($r['id']);
        }
        return $result;
    }

    public function checkImageNameExistNotId($imageName, $imageId) {

        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT count(i.id) FROM image i WHERE i.name LIKE '%$imageName%' and i.id!=:imageId ";
        $statement = $connection->prepare($sql);
        $statement->bindValue("imageId", $imageId);
        $statement->execute();
        $queryResult = $statement->fetchColumn();

        if ($queryResult > 0) {
            return TRUE;
        }

        return FALSE;
    }

}
