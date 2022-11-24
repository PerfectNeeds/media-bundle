<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class SingleImageType extends AbstractType
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
                    "accept" => "image/*",
                ],
                "constraints" => [
                    new File([
                        "mimeTypes" => [
                            "image/png",
                            "image/jpeg",
                            "image/gif",
                        ],
                    ]),
                ],
            ])
            ->getForm();
    }
}
