<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class DocumentType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->
                add('files', FileType::class, array(
                    "required" => FALSE,
                    "attr" => array(
                        "multiple" => "multiple",
                        "accept" => "application/pdf|application/msword|application/vnd.openxmlformats-officedocument.wordprocessingml.document|application/vnd.ms-excel|application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    )
                ))
                ->getForm();
    }
}
