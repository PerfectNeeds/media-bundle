<?php

namespace PN\MediaBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PN\MediaBundle\Entity\Document;
use PN\MediaBundle\Entity\Video;

class VideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $class = Video::class)
    {
        parent::__construct($registry, $class);
    }
}
