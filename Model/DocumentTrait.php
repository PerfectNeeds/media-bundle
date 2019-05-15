<?php

namespace PN\MediaBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait DocumentTrait {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\PreRemove
     */
    public function preRemove() {
        $this->removeUpload();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

}
