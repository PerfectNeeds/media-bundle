<?php

namespace PN\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class Image {

    private $filenameForRemove;
    private $filenameForRemoveResize;
    private $id;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="base_path", type="string", length=255, nullable=true)
     */
    protected $basePath;

    /**
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    protected $alt;

    /**
     * @ORM\Column(name="width", type="float", length=255, nullable=true)
     */
    protected $width;

    /**
     * @ORM\Column(name="height", type="float", length=255, nullable=true)
     */
    protected $height;

    /**
     * @ORM\Column(name="size", type="float", length=255, nullable=true)
     */
    protected $size;

    /**
     * @var string
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    protected $tarteb;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $imageType;

    /**
     *
     * @Assert\NotBlank()
     */
    protected $file;

    public function getUploadRootDirWithFileName() {
        // the absolute directory extension where uploaded with image name
        // documents should be saved
        $directory = $this->getBasePath();
        return UploadPath::getUploadRootDir($directory) . "/" . $this->getName();
    }

    public function getAssetPath() {
        return null === $this->name ? null : UploadPath::getUploadDir($this->getBasePath()) . $this->getName();
    }

    public function getAssetPathThumb() {
        return null === $this->name ? null : UploadPath::getUploadDir($this->getBasePath() . 'thumb/') . $this->getName();
    }

    public function preUpload($generatedImageName = NULL) {
        if (null !== $this->file) {
            if ($generatedImageName != NULL) {
                $this->name = $generatedImageName . '-' . $this->getId() . '.' . $this->file->guessExtension();
            } else {
                $this->name = $this->getId() . '.' . $this->file->guessExtension();
            }
        }
    }

    public function upload($directory) {

        if (null === $this->file) {
            return;
        }
        $this->setBasePath($directory);
        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does
        $this->file->move(UploadPath::getUploadRootDir($directory), $this->getName());
        unset($this->file);
    }

    public function removeUpload() {
        $this->storeFilenameForRemove();
        $this->storeFilenameForResizeRemove();

        if ($this->filenameForRemoveResize) {
            if (file_exists($this->filenameForRemoveResize)) {
                $folder = substr($this->filenameForRemoveResize, 0, strrpos($this->filenameForRemoveResize, '/') + 1);
                $this->removeEmptySubFolders($folder);
            }
        }
        if ($this->filenameForRemove) {
            if (file_exists($this->filenameForRemove)) {
                unlink($this->filenameForRemove);
                $folder = substr($this->filenameForRemove, 0, strrpos($this->filenameForRemove, '/') + 1);
                $this->removeEmptySubFolders($folder);
            }
        }
    }

    private function removeEmptySubFolders($path) {
        if (!file_exists($path)) {
            return false;
        }
        $empty = true;
        foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
            $empty &= is_dir($file) && $this->RemoveEmptySubFolders($file);
        }
        return $empty && rmdir($path);
    }

    public function getAbsoluteExtension($directory = null) {
        if ($directory == null) {
            $directory = $this->getBasePath();
        }
        return null === $this->name ? null : UploadPath::getUploadRootDir($directory) . '/' . $this->name;
    }

    private function storeFilenameForRemove($directory = null) {
        $this->filenameForRemove = $this->getAbsoluteExtension($directory);
    }

    public function storeFilenameForResizeRemove() {
        $this->filenameForRemoveResize = $this->getAbsoluteResizeExtension();
    }

    public function getAbsoluteResizeExtension($directory = null) {
        if ($directory == null) {
            $directory = $this->getBasePath();
        }
        $thumpPath = UploadPath::getUploadRootDir($directory) . '/thumb/';
        if (!file_exists($thumpPath)) {
            mkdir($thumpPath, 0777, TRUE);
        }
        return null === $this->name ? null : $thumpPath . $this->name;
    }

    public function getNameWithoutExtension() {
        return substr($this->name, 0, strrpos($this->name, '.'));
    }

    public function getNameExtension() {
        return substr($this->name, strrpos($this->name, '.') + 1);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Image
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setFile($file) {
        $this->file = $file;

        return $this;
    }

    public function getFile() {
        return $this->file;
    }

    /**
     * Set basePath
     *
     * @param string $basePath
     *
     * @return Image
     */
    public function setBasePath($basePath) {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Get basePath
     *
     * @return string
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return Image
     */
    public function setAlt($alt) {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt() {
        return $this->alt;
    }

    /**
     * Set width
     *
     * @param float $width
     *
     * @return Image
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
     *
     * @return Image
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
     * Set size
     *
     * @param float $size
     *
     * @return Image
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return float
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * Set tarteb
     *
     * @param integer $tarteb
     *
     * @return Image
     */
    public function setTarteb($tarteb) {
        $this->tarteb = $tarteb;

        return $this;
    }

    /**
     * Get tarteb
     *
     * @return integer
     */
    public function getTarteb() {
        return $this->tarteb;
    }

    /**
     * Set imageType
     *
     * @param integer $imageType
     *
     * @return Image
     */
    public function setImageType($imageType) {
        $this->imageType = $imageType;

        return $this;
    }

    /**
     * Get imageType
     *
     * @return integer
     */
    public function getImageType() {
        return $this->imageType;
    }

    /**
     * Get id
     *
     * @return integer
     */
    protected function getId() {
        return $this->id;
    }

}
