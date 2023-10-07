<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\DependencyInjection;

use Pimcore\Bundle\CoreBundle\DependencyInjection\ConfigurationHelper;
use Pimcore\Config\LocationAwareConfigRepository;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(GreenriversMagentoIntegrationExtension::CONTAINER_KEY);

        $rootNode = $treeBuilder->getRootNode();
        $rootNode->addDefaultsIfNotSet();

        $rootNode->children()
            ->arrayNode('general')
                ->children()
                    ->scalarNode('magentoUrl')->end()
                    ?->scalarNode('magentoToken')->end()
                ?->end()
            ?->end()
            ?->arrayNode('pimcore')
                ->children()
                    ->booleanNode('sendProductOnSave')->end()
                    ?->booleanNode('sendCategoryOnSave')->end()
                ?->end()
            ?->end();

        ConfigurationHelper::addConfigLocationWithWriteTargetNodes(
            $rootNode,
            [GreenriversMagentoIntegrationExtension::CONFIG_KEY => '/var/config/greenrivers'],
            [LocationAwareConfigRepository::READ_TARGET]
        );

        return $treeBuilder;
    }
}
