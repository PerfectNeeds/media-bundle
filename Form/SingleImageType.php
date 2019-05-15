<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SingleImageType extends AbstractType {

    public $name = 'file';

    function __construct($name = NULL) {
        if ($name != NULL) {
            $this->name = $name;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->
                add($this->name, 'Symfony\Component\Form\Extension\Core\Type\FileType', array(
                    "required" => FALSE,
                    "attr" => array(
                        "accept" => "image/*",
                    )
                ))
                ->getForm();
    }

    public function getBlockPrefix() {
        return '';
    }

}
