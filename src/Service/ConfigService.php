<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\Service;

use Exception;
use Greenrivers\Bundle\MagentoIntegrationBundle\DependencyInjection\GreenriversMagentoIntegrationExtension;
use Pimcore;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Config\LocationAwareConfigRepository;
use Pimcore\Model\Exception\ConfigWriteException;

class ConfigService
{
    private const MAGENTO_INTEGRATION_CONFIG_KEY = 'pimcore_bundle_magento_integration_config';
    private const SETTINGS_STORE_SCOPE = 'greenrivers';

    private static ?LocationAwareConfigRepository $locationAwareConfigRepository = null;

    /**
     * @return LocationAwareConfigRepository
     */
    private static function getRepository(): LocationAwareConfigRepository
    {
        if (!self::$locationAwareConfigRepository) {
            $config = [];
            $containerConfig = Pimcore::getContainer()
                ?->getParameter(GreenriversMagentoIntegrationExtension::CONTAINER_KEY);

            $storageConfig = $containerConfig[LocationAwareConfigRepository::CONFIG_LOCATION]
            [GreenriversMagentoIntegrationExtension::CONFIG_KEY];

            self::$locationAwareConfigRepository = new LocationAwareConfigRepository(
                $config,
                self::SETTINGS_STORE_SCOPE,
                $storageConfig
            );
        }

        return self::$locationAwareConfigRepository;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function isWriteable(): bool
    {
        return self::getRepository()->isWriteable();
    }

    /**
     * @return array
     */
    public static function get(): array
    {
        $repository = self::getRepository();

        $config = $repository->loadConfigByKey(GreenriversMagentoIntegrationExtension::CONFIG_KEY);

        return $config[0] ?? [];
    }

    /**
     * @param array $data
     * @return void
     * @throws ConfigWriteException
     */
    public static function save(array $data): void
    {
        $repository = self::getRepository();

        if (!$repository->isWriteable()) {
            throw new ConfigWriteException();
        }

        $data = [
            'general' => [
                'magentoUrl' => $data['magentoUrl'],
                'magentoToken' => $data['magentoToken']
            ],
            'pimcore' => [
                'sendProductOnSave' => $data['sendProductOnSave'],
                'sendCategoryOnSave' => $data['sendCategoryOnSave']
            ]
        ];

        $repository->saveConfig(GreenriversMagentoIntegrationExtension::CONFIG_KEY, $data, function ($key, $data) {
            return [
                GreenriversMagentoIntegrationExtension::CONTAINER_KEY => $data
            ];
        });
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function getMagentoIntegrationConfig(): array
    {
        if (RuntimeCache::isRegistered(self::MAGENTO_INTEGRATION_CONFIG_KEY)) {
            $config = RuntimeCache::get(self::MAGENTO_INTEGRATION_CONFIG_KEY);
        } else {
            $config = self::get();
            self::setMagentoIntegrationConfig($config);
        }

        return $config;
    }

    /**
     * @param array $config
     * @return void
     */
    public static function setMagentoIntegrationConfig(array $config): void
    {
        RuntimeCache::set(self::MAGENTO_INTEGRATION_CONFIG_KEY, $config);
    }
}
