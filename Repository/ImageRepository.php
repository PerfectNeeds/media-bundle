<?php

namespace PN\MediaBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PN\MediaBundle\Entity\Image;

class ImageRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry, string $class = Image::class)
    {
        parent::__construct($registry, $class);
    }

    public function setMainImage(
        $entityType,
        $entityId,
        $image,
        $newImageType = Image::TYPE_MAIN,
        $clearOldImage = false
    ) {
        if ($newImageType == Image::TYPE_MAIN or $clearOldImage == true) {
            $this->clearMain($entityType, $entityId, $newImageType);
        }

        $em = $this->getEntityManager();
        $image->setImageType($newImageType);
        $em->persist($image);
        $em->flush();

        return $image;
    }

    public function clearMain($entityType, $entityId, $newImageType)
    {
        switch ($entityType) {
            case "CMSBundle:Post":
            case "PNContentBundle:Post":
                $SQLTable = "post_image";
                $SQLColumn = "t.post_id";
                break;
        }
        $sql = "UPDATE image i JOIN $SQLTable t on i.id = t.image_id "
            ."SET i.image_type = ".Image::TYPE_TEMP
            ." WHERE i.image_type = ".$newImageType." and $SQLColumn = ?  ";
        $this->getEntityManager()->getConnection()
            ->executeQuery($sql, array($entityId));
    }

    public function getLimitedImageByPost($postId, $limit)
    {

        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT i.id FROM image i "
            ."RIGHT OUTER JOIN post_image pi ON i.id=pi.image_id "
            ."WHERE i.image_type=:imageType and pi.post_id=:postId "
            ."GROUP BY i.id ORDER BY i.id ASC Limit $limit";
        $statement = $connection->prepare($sql);
        $statement->bindValue("imageType", Image::TYPE_GALLERY);
        $statement->bindValue("postId", $postId);
        $queryResult = $statement->executeQuery()->fetchAllAssociative();
        if (count($queryResult) == 0) {
            return null;
        }

        $result = $ids = [];

        foreach ($queryResult as $key => $r) {
            $ids[] = $r['id'];
        }

        if (count($ids) > 0) {
            return $this->findBy(["id" => $ids]);
        }

        return $result;
    }

    public function checkImageNameExistNotId($imageName, $imageId)
    {

        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT count(i.id) FROM image i WHERE i.name LIKE '%$imageName%' and i.id!=:imageId ";
        $statement = $connection->prepare($sql);
        $statement->bindValue("imageId", $imageId);
        $queryResult = $statement->executeQuery()->fetchOne();

        if ($queryResult > 0) {
            return true;
        }

        return false;
    }

}
