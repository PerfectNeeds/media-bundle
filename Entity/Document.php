<?php

namespace PN\MediaBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PN\ServiceBundle\Lib\UploadPath;

/**
 * @ORM\MappedSuperclass
 */
#[ORM\MappedSuperclass]
abstract class Document {

    private $filenameForRemove;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: "name", type: Types::STRING, length: 255, nullable: true)]
    protected $name;

    /**
     * @ORM\Column(name="base_path", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: "base_path", type: Types::STRING, length: 255, nullable: true)]
    protected $basePath;

    /**
     * @ORM\Column(name="size", type="float", length=255, nullable=true)
     */
    #[ORM\Column(name: "size", type: Types::FLOAT, length: 255, nullable: true)]
    protected $size;
    protected $file;

    public function __construct() {

    }

    public function getRelationalEntity() {
        $excludeMethods = ['id', "name", 'basePath', 'size', 'file', "filenameForRemove"];

        $allObjects = get_object_vars($this);

        foreach ($allObjects as $objectName => $objectValue) {
            if (in_array($objectName, $excludeMethods)) {
                continue;
            }
            if ($objectValue != NULL) {
                return $objectValue;
            }
        }
        return NULL;
    }

    public function getUploadRootDirWithFileName() {
        // the absolute directory extension where uploaded with image name
        // documents should be saved
        $directory = $this->getBasePath();
        return UploadPath::getUploadRootDir($directory) . "/" . $this->getName();
    }

    public function getAssetPath() {
        return null === $this->name ? null : UploadPath::getUploadDir($this->getBasePath()) . $this->getName();
    }

    public function preUpload($generatedImageName = NULL) {
        if (null !== $this->file) {
            $extension = $this->file->guessExtension();
            if (method_exists($this->file, 'getClientOriginalExtension')) {
                $extension = $this->file->getClientOriginalExtension();
            }
            if ($generatedImageName != NULL) {
                $this->name = $generatedImageName . '-' . $this->getId() . '.' . $extension;
            } else {
                $this->name = $this->getId() . '.' . $extension;
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

        $uploadDirectory = UploadPath::getUploadRootDir($directory);
        if(!file_exists($uploadDirectory)){
            mkdir($uploadDirectory, 0777, true);
        }

        $this->file->move($uploadDirectory, $this->getName());
        unset($this->file);
    }

    public function removeUpload() {
        $this->storeFilenameForRemove();

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

    public function getNameWithoutExtension() {
        return substr($this->name, 0, strrpos($this->name, '.'));
    }

    public function getNameExtension() {
        return substr($this->name, strrpos($this->name, '.') + 1);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
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
     * @return Document
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
     * Set size
     *
     * @param float $size
     *
     * @return Document
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

}
