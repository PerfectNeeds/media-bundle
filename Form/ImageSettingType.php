<?php

namespace PN\MediaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use PN\MediaBundle\Entity\ImageSetting;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ImageSettingType extends AbstractType {

    private $entitiesNames = [];
    private $routes = [];

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $this->entitiesNames = $options['entitiesNames'];
        $this->routes = $options['routes'];

        $builder
                ->add('entityName', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => $this->entitiesNames
                ])
                ->add('backRoute', ChoiceType::class, [
                    'placeholder' => 'Choose an option',
                    'choices' => $this->routes
                ])
                ->add('uploadPath')
                ->add('gallery')
                ->add('autoResize', NULL, array('required' => false))
                ->add('quality', ChoiceType::class, array(
                    'choices' => array(
                        'Web Resolution (75%)' => ImageSetting::WEB_RESOLUTION,
                        'Original Resolution (100%)' => ImageSetting::ORIGINAL_RESOLUTION,
                    ),
                ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'PN\MediaBundle\Entity\ImageSetting',
            'entitiesNames' => FALSE,
            'routes' => FALSE,
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix() {
        return 'md_bundle_medibundle_imagesetting';
    }

}
