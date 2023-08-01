<?php

namespace PN\MediaBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PN\ServiceBundle\Lib\UploadPath;

/**
 * @ORM\MappedSuperclass
 */
#[ORM\MappedSuperclass]
abstract class Video
{
    private ?string $filenameForRemove = null;
    protected $file;

    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: "name", type: Types::STRING, length: 255, nullable: true)]
    protected ?string $name = null;

    /**
     * @ORM\Column(name="base_path", type="string", length=255, nullable=true)
     */
    #[ORM\Column(name: "base_path", type: Types::STRING, length: 255, nullable: true)]
    protected ?string $basePath = null;

    /**
     * @ORM\Column(name="size", type="float", length=255, nullable=true)
     */
    #[ORM\Column(name: "size", type: Types::FLOAT, nullable: true)]
    protected ?float $size = null;

    /**
     * @var string
     * @ORM\Column(name="tarteb", type="smallint", nullable=true)
     */
    #[ORM\Column(name: "tarteb", type: Types::SMALLINT, nullable: true)]
    protected ?int $tarteb = null;

    /**
     * @ORM\Column(name="created", type="datetime")
     */
    #[ORM\Column(name: "created", type: Types::DATETIME_MUTABLE)]
    protected ?\DateTimeInterface $created = null;


    public function getUploadRootDirWithFileName(): string
    {
        // the absolute directory extension where uploaded with image name
        // documents should be saved
        $directory = $this->getBasePath();

        return UploadPath::getUploadRootDir($directory) . $this->getName();
    }

    public function getAssetPath(): ?string
    {
        return null === $this->name ? null : UploadPath::getUploadDir($this->getBasePath()) . $this->getName();
    }

    public function preUpload($generatedVideoName = null): void
    {
        if (null !== $this->file) {
            if ($generatedVideoName != null) {
                $this->name = $generatedVideoName . '-' . $this->getId() . '.' . $this->file->guessExtension();
            } else {
                $this->name = $this->getId() . '.' . $this->file->guessExtension();
            }
        }
    }

    public function upload($directory): void
    {

        if (null === $this->file) {
            return;
        }
        $this->setBasePath($directory);
        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does
        $uploadDirectory = UploadPath::getUploadRootDir($directory);
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $this->file->move($uploadDirectory, $this->getName());
        unset($this->file);
    }

    public function removeUpload(): void
    {
        $this->storeFilenameForRemove();

        if ($this->filenameForRemove) {
            if (file_exists($this->filenameForRemove)) {
                unlink($this->filenameForRemove);
                $folder = substr($this->filenameForRemove, 0, strrpos($this->filenameForRemove, '/') + 1);
                $this->removeEmptySubFolders($folder);
            }
        }
    }

    private function removeEmptySubFolders($path): bool
    {
        if (!file_exists($path)) {
            return false;
        }
        $empty = true;
        foreach (glob($path . DIRECTORY_SEPARATOR . "*") as $file) {
            $empty &= is_dir($file) && $this->RemoveEmptySubFolders($file);
        }

        return $empty && rmdir($path);
    }

    public function getAbsoluteExtension($directory = null): ?string
    {
        if ($directory == null) {
            $directory = $this->getBasePath();
        }

        return null === $this->name ? null : UploadPath::getUploadRootDir($directory) . $this->name;
    }

    private function storeFilenameForRemove($directory = null): void
    {
        $this->filenameForRemove = $this->getAbsoluteExtension($directory);
    }


    public function getNameWithoutExtension(): string
    {
        return substr($this->name, 0, strrpos($this->name, '.'));
    }

    public function getNameExtension(): string
    {
        return substr($this->name, strrpos($this->name, '.') + 1);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getBasePath(): ?string
    {
        return $this->basePath;
    }

    public function setBasePath(?string $basePath): static
    {
        $this->basePath = $basePath;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(?float $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getTarteb(): ?int
    {
        return $this->tarteb;
    }

    public function setTarteb(?int $tarteb): static
    {
        $this->tarteb = $tarteb;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }
}
