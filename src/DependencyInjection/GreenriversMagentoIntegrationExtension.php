<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\DependencyInjection;

use Exception;
use Pimcore\Config\LocationAwareConfigRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class GreenriversMagentoIntegrationExtension extends ConfigurableExtension implements PrependExtensionInterface
{
    public const CONTAINER_KEY = 'greenrivers_magento_integration';
    public const CONFIG_KEY = 'magento_integration';

    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @return void
     * @throws Exception
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $container->setParameter(self::CONTAINER_KEY, $mergedConfig);
    }

    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function prepend(ContainerBuilder $container): void
    {
        LocationAwareConfigRepository::loadSymfonyConfigFiles(
            $container,
            self::CONTAINER_KEY,
            self::CONFIG_KEY
        );
    }
}
