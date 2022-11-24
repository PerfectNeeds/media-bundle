<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("files", FileType::class, [
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
