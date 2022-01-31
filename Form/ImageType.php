<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ImageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('files', FileType::class, array(
                "required" => false,
                "attr" => [
                    "multiple" => "multiple",
                    "accept" => "image/*",
                ],
            ))
            ->getForm();
    }

}
