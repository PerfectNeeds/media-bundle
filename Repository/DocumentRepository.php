<?php

namespace PN\MediaBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PN\MediaBundle\Entity\Document;

class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $class = Document::class)
    {
        parent::__construct($registry, $class);
    }
}
