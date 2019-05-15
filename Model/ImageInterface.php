<?php

namespace PN\MediaBundle\Model;

interface ImageInterface {

    public function getId();

    /**
     * @ORM\PreRemove
     */
    public function preRemove();
}
