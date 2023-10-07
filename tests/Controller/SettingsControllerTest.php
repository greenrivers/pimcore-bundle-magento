<?php

namespace Greenrivers\Bundle\MagentoIntegrationBundle\Tests\Controller;

use Greenrivers\Bundle\MagentoIntegrationBundle\Controller\SettingsController;
use Pimcore\Bundle\AdminBundle\Security\CsrfProtectionHandler;
use Pimcore\Model\Tool\SettingsStore;
use Pimcore\Model\User;
use Pimcore\Security\User\User as SecurityUser;
use Pimcore\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class SettingsControllerTest extends WebTestCase
{
    /**
     * @covers SettingsController::getSettings
     */
    public function testGetSettings(): void
    {
        $client = static::createClient();

        $data = [
            'general' => [
                'magentoUrl' => 'https://app.magento.test/',
                'magentoToken' => 'token123'
            ],
            'pimcore' => [
                'sendProductOnSave' => true,
                'sendCategoryOnSave' => true
            ]
        ];
        SettingsStore::set(
            'magento_integration',
            json_encode($data, JSON_THROW_ON_ERROR),
            SettingsStore::TYPE_STRING,
            'greenrivers'
        );

        $user = User::getByName('pimcore');

        $client->loginUser(new SecurityUser($user), 'pimcore_admin');
        $client->request(Request::METHOD_GET, '/admin/greenrivers/magento_integration/settings');

        $response = $client->getResponse();
        $result = $response->getContent();

        self::assertResponseIsSuccessful();
        self::assertJson($result);
        self::assertJsonStringEqualsJsonString(
            <<<EOD
                {
                    "values": {
                        "general": {
                            "magentoUrl": "https:\/\/app.magento.test\/",
                            "magentoToken": "token123"
                        },
                        "pimcore": {
                            "sendProductOnSave": true,
                            "sendCategoryOnSave": true
                        }
                    }
                }
            EOD,
            $result
        );
    }

    /**
     * @covers SettingsController::setSettings
     */
    public function testSetSettings(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $data = [
            'magentoUrl' => 'https://app.magento.test/',
            'magentoToken' => 'token123',
            'sendProductOnSave' => false,
            'sendCategoryOnSave' => false
        ];

        $twig = new Environment(new ArrayLoader([]));
        $csrfProtectionHandler = new CsrfProtectionHandler(
            ['pimcore_bundle_greenrivers_magento_integration_settings_set'],
            $twig
        );
        $container->set(CsrfProtectionHandler::class, $csrfProtectionHandler);

        $user = User::getByName('pimcore');
        $client->loginUser(new SecurityUser($user), 'pimcore_admin');
        $client->request(
            Request::METHOD_PUT,
            '/admin/greenrivers/magento_integration/settings',
            ['data' => json_encode($data, JSON_THROW_ON_ERROR)]
        );

        $response = $client->getResponse();
        $result = $response->getContent();

        self::assertResponseIsSuccessful();
        self::assertJson($result);
        self::assertJsonStringEqualsJsonString('{"success": true}', $result);
        self::assertJsonStringEqualsJsonString(
            <<<EOD
                {
                    "general": {
                        "magentoUrl": "https:\/\/app.magento.test\/",
                        "magentoToken": "token123"
                    },
                    "pimcore": {
                        "sendProductOnSave": false,
                        "sendCategoryOnSave": false
                    }
                }
            EOD,
            SettingsStore::get('magento_integration', 'greenrivers')
                ->getData()
        );
    }
}
