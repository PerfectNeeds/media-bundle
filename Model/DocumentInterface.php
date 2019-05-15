<?php

namespace PN\MediaBundle\Model;

interface DocumentInterface {

    public function getId();

    /**
     * @ORM\PreRemove
     */
    public function preRemove();
}
