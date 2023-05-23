<?php

namespace PN\MediaBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Table("image_type")
 * @ORM\Entity()
 */
#[ORM\Table(name: "image_type")]
#[ORM\Entity]
class ImageType {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    #[ORM\Id]
    #[ORM\Column(name: "id", type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    #[Assert\NotBlank]
    #[ORM\Column(name: "name", type: Types::STRING, length: 255, nullable: true)]
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="ImageSettingHasType", mappedBy="imageType")
     */
    #[ORM\OneToMany(targetEntity: ImageSettingHasType::class, mappedBy: "imageType")]
    protected $imageSettingTypes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->imageSettingTypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ImageType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add imageSettingTypes
     *
     * @param \PN\MediaBundle\Entity\ImageSettingHasType $imageSettingTypes
     * @return ImageType
     */
    public function addImageSettingType(\PN\MediaBundle\Entity\ImageSettingHasType $imageSettingTypes)
    {
        $this->imageSettingTypes[] = $imageSettingTypes;

        return $this;
    }

    /**
     * Remove imageSettingTypes
     *
     * @param \PN\MediaBundle\Entity\ImageSettingHasType $imageSettingTypes
     */
    public function removeImageSettingType(\PN\MediaBundle\Entity\ImageSettingHasType $imageSettingTypes)
    {
        $this->imageSettingTypes->removeElement($imageSettingTypes);
    }

    /**
     * Get imageSettingTypes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImageSettingTypes()
    {
        return $this->imageSettingTypes;
    }

}
