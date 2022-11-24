<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class SingleDocumentType extends AbstractType
{

    public $name = "file";

    function __construct($name = null)
    {
        if ($name != null) {
            $this->name = $name;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($this->name, FileType::class, [
                "required" => false,
                "attr" => [
                    "class" => "form-control",
                    "accept" => "application/pdf|application/msword|application/vnd.openxmlformats-officedocument.wordprocessingml.document|application/vnd.ms-excel|application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                ],
                "constraints" => [
                    new File([
                        "mimeTypes" => [
                            "application/msword",
                            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
                            "application/pdf",
                            "application/vnd.ms-excel",
                            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                        ],
                    ]),
                ],
            ])
            ->getForm();
    }

}
