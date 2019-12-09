<?php

namespace PN\MediaBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("image_setting_has_type")
 * @ORM\Entity(repositoryClass="PN\MediaBundle\Repository\ImageSettingHasTypeRepository")
 */
class ImageSettingHasType {

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ImageType", inversedBy="imageSettingTypes", cascade={"persist"})
     */
    protected $imageType;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ImageSetting", inversedBy="imageSettingTypes", cascade={"persist"})
     */
    protected $imageSetting;

    /**
     * @ORM\Column(name="radio_button", type="boolean")
     */
    protected $radioButton = true;

    /**
     * @ORM\Column(name="width", type="float", length=255, nullable=true)
     */
    protected $width;

    /**
     * @ORM\Column(name="height", type="float", length=255, nullable=true)
     */
    protected $height;

    /**
     * @ORM\Column(name="thumb_width", type="float", nullable=true)
     */
    protected $thumbWidth = null;

    /**
     * @ORM\Column(name="thumb_height", type="float", nullable=true)
     */
    protected $thumbHeight = null;

    /**
     * @ORM\Column(name="validate_width_and_height", type="boolean")
     */
    protected $validateWidthAndHeight = true;

    /**
     * Set width
     *
     * @param float $width
     * @return ImageSettingHasType
     */
    public function setWidth($width) {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return float
     */
    public function getWidth() {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param float $height
     * @return ImageSettingHasType
     */
    public function setHeight($height) {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return float
     */
    public function getHeight() {
        return $this->height;
    }

    /**
     * Set imageType
     *
     * @param \PN\MediaBundle\Entity\ImageType $imageType
     * @return ImageSettingHasType
     */
    public function setImageType(\PN\MediaBundle\Entity\ImageType $imageType) {
        $this->imageType = $imageType;

        return $this;
    }

    /**
     * Get imageType
     *
     * @return \PN\MediaBundle\Entity\ImageType
     */
    public function getImageType() {
        return $this->imageType;
    }

    /**
     * Set imageSetting
     *
     * @param \PN\MediaBundle\Entity\ImageSetting $imageSetting
     * @return ImageSettingHasType
     */
    public function setImageSetting(\PN\MediaBundle\Entity\ImageSetting $imageSetting) {
        $this->imageSetting = $imageSetting;

        return $this;
    }

    /**
     * Get imageSetting
     *
     * @return \PN\MediaBundle\Entity\ImageSetting
     */
    public function getImageSetting() {
        return $this->imageSetting;
    }

    /**
     * Set radioButton
     *
     * @param boolean $radioButton
     * @return ImageSettingHasType
     */
    public function setRadioButton($radioButton) {
        $this->radioButton = $radioButton;

        return $this;
    }

    /**
     * Get radioButton
     *
     * @return boolean
     */
    public function getRadioButton() {
        return $this->radioButton;
    }

    /**
     * Set thumbWidth
     *
     * @param float $thumbWidth
     * @return ImageSettingHasType
     */
    public function setThumbWidth($thumbWidth) {
        $this->thumbWidth = $thumbWidth;

        return $this;
    }

    /**
     * Get thumbWidth
     *
     * @return float
     */
    public function getThumbWidth() {
        return $this->thumbWidth;
    }

    /**
     * Set thumbHeight
     *
     * @param float $thumbHeight
     * @return ImageSettingHasType
     */
    public function setThumbHeight($thumbHeight) {
        $this->thumbHeight = $thumbHeight;

        return $this;
    }

    /**
     * Get thumbHeight
     *
     * @return float
     */
    public function getThumbHeight() {
        return $this->thumbHeight;
    }

    /**
     * Set validateWidthAndHeight
     *
     * @param boolean $validateWidthAndHeight
     * @return ImageSettingHasType
     */
    public function setValidateWidthAndHeight($validateWidthAndHeight) {
        $this->validateWidthAndHeight = $validateWidthAndHeight;

        return $this;
    }

    /**
     * Get validateWidthAndHeight
     *
     * @return boolean
     */
    public function getValidateWidthAndHeight() {
        return $this->validateWidthAndHeight;
    }

}
