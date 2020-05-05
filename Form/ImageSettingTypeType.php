<?php

namespace PN\MediaBundle\Form;

use PN\MediaBundle\Entity\ImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageSettingTypeType extends AbstractType {

    private $isSuperUser;

    public function __construct($isSuperUser = FALSE) {
        $this->isSuperUser = $isSuperUser;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('imageType', EntityType::class, [
                    'required' => TRUE,
                    'class' => ImageType::class
                ])
                ->add('radioButton', NULL, array('required' => false))
                ->add('validateWidthAndHeight', NULL, array('required' => false))
                ->add('validateSize', NULL, array('required' => false))
                ->add('width', NULL, array('required' => false))
                ->add('height', NULL, array('required' => false))
                ->add('thumbWidth', NULL, array('required' => false))
                ->add('thumbHeight', NULL, array('required' => false))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'PN\MediaBundle\Entity\ImageSettingHasType'
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'md_bundle_medibundle_imagesettingtype';
    }

}
