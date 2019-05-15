<?php

namespace PN\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PN\MediaBundle\Entity\Image as BaseImage;
use PN\MediaBundle\Model\ImageInterface;
use PN\MediaBundle\Model\ImageTrait;

/**
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("image")
 * @ORM\Entity(repositoryClass="PN\MediaBundle\Repository\ImageRepository")
 */
class Image extends BaseImage implements ImageInterface {

    use ImageTrait;

    const TYPE_TEMP = 0;
    const TYPE_MAIN = 1;
    const TYPE_GALLERY = 2;
    const TYPE_ICON = 3;

    /**
     * @ORM\OneToOne(targetEntity="\PN\Bundle\CMSBundle\Entity\Banner", mappedBy="image")
     */
    protected $banner;

    /**
     * @ORM\ManyToMany(targetEntity="\PN\Bundle\CMSBundle\Entity\Post", mappedBy="images")
     */
    protected $posts;

}
