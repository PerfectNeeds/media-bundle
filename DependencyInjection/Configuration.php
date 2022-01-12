<?php

namespace PN\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface {

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder("pn_media");
        $rootNode = $treeBuilder->getRootNode();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $this->addImageSestion($rootNode);
        $this->addDocumentSestion($rootNode);

        return $treeBuilder;
    }

    private function addImageSestion(ArrayNodeDefinition $rootNode) {
        $defaultMimeTypes = ['image/gif', 'image/jpeg', 'image/jpg', 'image/png'];
        $rootNode->children()
                ->arrayNode('image')
                ->addDefaultsIfNotSet()
                ->isRequired()
                ->children()
                ->scalarNode('image_class')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
                ->arrayNode('mime_types')->scalarPrototype()->end()->defaultValue($defaultMimeTypes)->end()
                ->arrayNode('upload_paths')
                ->arrayPrototype()
                ->children()
                ->scalarNode('id')->end()
                ->scalarNode('path')->end()
                ->scalarNode('width')->end()
                ->scalarNode('height')->end()
                ->scalarNode('validateWidthAndHeight')->end()
                ->scalarNode('validateSize')->defaultValue(false)->end()
                ->end()
                ->end()
                ->end()
                ->end()
                ->end()
        ;
    }

    private function addDocumentSestion($rootNode) {
        $defaultMimeTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/mspowerpoint', 'application/powerpoint', 'application/vnd.ms-powerpoint', 'application/x-mspowerpoint', 'application/pdf', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        $rootNode->children()
                ->arrayNode('document')
                ->addDefaultsIfNotSet()
                ->isRequired()
                ->children()
                ->scalarNode('document_class')
                ->isRequired()
                ->cannotBeEmpty()
                ->end()
                ->arrayNode('mime_types')->scalarPrototype()->end()->defaultValue($defaultMimeTypes)->end()
                ->arrayNode('upload_paths')
                ->arrayPrototype()
                ->children()
                ->scalarNode('id')->end()
                ->scalarNode('path')->end()
                ->end()
                ->end()
                ->end()
                ->end()
                ->end()
        ;
    }

}
