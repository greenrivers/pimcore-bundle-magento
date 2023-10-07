<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle;

use Doctrine\Dbal\Exception as DoctrineException;
use Exception;
use Pimcore\Bundle\DataHubBundle\Configuration;
use Pimcore\Bundle\DataHubBundle\Service\ImportService;
use Pimcore\Bundle\DataHubBundle\WorkspaceHelper;
use Pimcore\Db;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Service;
use Pimcore\Model\DataObject\Folder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Installer extends SettingsStoreAwareInstaller
{
    protected const USER_PERMISSIONS = [
        [
            'key' => 'greenrivers',
            'category' => 'Greenrivers'
        ],
        [
            'key' => 'greenrivers_magento_integration',
            'category' => 'Greenrivers MagentoIntegration Bundle'
        ]
    ];

    protected const DATA_HUB_DEFINITION = [
        'name' => 'greenrivers',
        'file' => 'datahub_graphql_greenrivers_export.json'
    ];

    protected const CLASS_DEFINITIONS = [
        [
            'name' => 'MagentoIntegrationCategory',
            'file' => 'class_MagentoIntegrationCategory_export.json'
        ],
        [
            'name' => 'MagentoIntegrationProduct',
            'file' => 'class_MagentoIntegrationProduct_export.json'
        ]
    ];

    /**
     * Installer constructor.
     * @param ImportService $importService
     * @param BundleInterface $bundle
     */
    public function __construct(
        private readonly ImportService $importService,
        BundleInterface                $bundle
    )
    {
        parent::__construct($bundle);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function install(): void
    {
        $this->addUserPermission();
        $this->addDataHubConfig();
        $this->createClasses();
        $this->createFolders();
        $this->markInstalled();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function uninstall(): void
    {
        $this->removeUserPermission();
        $this->removeDataHubConfig();
        $this->markUninstalled();
    }

    /**
     * @return void
     * @throws DoctrineException
     */
    private function addUserPermission(): void
    {
        $db = Db::get();

        foreach (self::USER_PERMISSIONS as $permission) {
            ['key' => $key, 'category' => $category] = $permission;

            $permissionExists = $db->executeStatement(
                'SELECT `key` FROM users_permission_definitions WHERE `key` = :key',
                ['key' => $key]
            );
            if (!$permissionExists) {
                $db->insert('users_permission_definitions', [
                    $db->quoteIdentifier('key') => $key,
                    $db->quoteIdentifier('category') => $category
                ]);
            }
        }
    }

    /**
     * @return void
     * @throws DoctrineException
     */
    private function removeUserPermission(): void
    {
        $db = Db::get();

        foreach (self::USER_PERMISSIONS as $permission) {
            $db->delete('users_permission_definitions', [
                $db->quoteIdentifier('key') => $permission['key']
            ]);
        }
    }

    /**
     * @return void
     */
    public function addDataHubConfig(): void
    {
        ['file' => $file] = self::DATA_HUB_DEFINITION;
        $json = file_get_contents(__DIR__ . '/../data-hub/' . $file);

        $this->importService->importConfigurationJson($json);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function removeDataHubConfig(): void
    {
        ['name' => $name] = self::DATA_HUB_DEFINITION;
        $config = Configuration::getByName($name);

        if ($config) {
            WorkspaceHelper::deleteConfiguration($config);
            $config->delete();
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function createClasses(): void
    {
        foreach (self::CLASS_DEFINITIONS as $classDefinition) {
            ['name' => $name, 'file' => $file] = $classDefinition;

            $json = file_get_contents(__DIR__ . '/../classes/' . $file);
            $class = ClassDefinition::getByName($name);

            if ($class) {
                throw new Exception("Class definition with name $name already exists.");
            }

            $class = ClassDefinition::create();
            $class->setName($name);
            $class->setGroup('Greenrivers');

            Service::importClassDefinitionFromJson($class, $json, true);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function createFolders(): void
    {
        $greenriversFolder = Folder::getByPath('/Greenrivers');

        if ($greenriversFolder) {
            throw new Exception('Greenrivers folder already exists.');
        }

        $greenriversFolder = Folder::create([
            'parentId' => 1,
            'creationDate' => time(),
            'modificationDate' => time(),
            'userOwner' => 1,
            'userModification' => 1,
            'key' => 'Greenrivers',
            'published' => true
        ]);
        $magentoIntegration = Folder::create([
            'parentId' => $greenriversFolder->getId(),
            'creationDate' => time(),
            'modificationDate' => time(),
            'userOwner' => 1,
            'userModification' => 1,
            'key' => 'MagentoIntegration',
            'published' => true
        ]);
        Folder::create([
            'parentId' => $magentoIntegration->getId(),
            'creationDate' => time(),
            'modificationDate' => time(),
            'userOwner' => 1,
            'userModification' => 1,
            'key' => 'Products',
            'published' => true
        ]);
        Folder::create([
            'parentId' => $magentoIntegration->getId(),
            'creationDate' => time(),
            'modificationDate' => time(),
            'userOwner' => 1,
            'userModification' => 1,
            'key' => 'Categories',
            'published' => true
        ]);
    }
}
