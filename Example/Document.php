<?php

namespace PN\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PN\MediaBundle\Entity\Document as BaseDocument;
use PN\MediaBundle\Model\DocumentInterface;
use PN\MediaBundle\Model\DocumentTrait;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("document")
 * @ORM\Entity(repositoryClass="PN\MediaBundle\Repository\DocumentRepository")
 */
class Document extends BaseDocument implements DocumentInterface {

    use DocumentTrait;
}
