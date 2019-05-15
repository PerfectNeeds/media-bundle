<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SingleDocumentType extends AbstractType {

    public $name = 'file';

    function __construct($name = NULL) {
        if ($name != NULL) {
            $this->name = $name;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->
                add($this->name, 'file', array(
                    "required" => FALSE,
                    "attr" => array(
                        "accept" => "application/pdf|application/msword|application/vnd.openxmlformats-officedocument.wordprocessingml.document|application/vnd.ms-excel|application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    )
                ))
                ->getForm();
    }

    public function getBlockPrefix() {
        return '';
    }

}
