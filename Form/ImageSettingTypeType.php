<?php

namespace PN\MediaBundle\Form;

use PN\MediaBundle\Entity\ImageSettingHasType;
use PN\MediaBundle\Entity\ImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageSettingTypeType extends AbstractType
{

    private $isSuperUser;

    public function __construct($isSuperUser = false)
    {
        $this->isSuperUser = $isSuperUser;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageType', EntityType::class, [
                'required' => true,
                'class' => ImageType::class,
            ])
            ->add('radioButton', null, array('required' => false))
            ->add('validateWidthAndHeight', null, array('required' => false))
            ->add('validateSize', null, array('required' => false))
            ->add('width', null, array('required' => false))
            ->add('height', null, array('required' => false))
            ->add('thumbWidth', null, array('required' => false))
            ->add('thumbHeight', null, array('required' => false));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ImageSettingHasType::class,
        ]);
    }

}
