<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\Controller;

use Exception;
use Greenrivers\Bundle\MagentoIntegrationBundle\Service\ConfigService;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\Exception\ConfigWriteException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/greenrivers/magento_integration')]
class SettingsController extends UserAwareController
{
    use JsonHelperTrait;

    public const SETTINGS_GET_ROUTE = 'pimcore_bundle_greenrivers_magento_integration_settings_get';
    public const SETTINGS_SET_ROUTE = 'pimcore_bundle_greenrivers_magento_integration_settings_set';

    /**
     * SettingsController constructor.
     * @param LoggerInterface $magentoIntegrationLogger
     */
    public function __construct(private readonly LoggerInterface $magentoIntegrationLogger)
    {
    }

    #[Route('/settings', name: self::SETTINGS_GET_ROUTE, methods: [Request::METHOD_GET])]
    public function getSettings(Request $request): JsonResponse
    {
        $this->checkPermission('greenrivers_magento_integration');
        $response = ['values' => []];

        try {
            $valueArray = ConfigService::getMagentoIntegrationConfig();
            $response = ['values' => $valueArray];
        } catch (Exception $e) {
            $this->magentoIntegrationLogger->error($e->getMessage());
        }

        return $this->jsonResponse($response);
    }

    #[Route('/settings', name: self::SETTINGS_SET_ROUTE, methods: [Request::METHOD_PUT])]
    public function setSettings(Request $request): JsonResponse
    {
        $this->checkPermission('greenrivers_magento_integration');

        $values = $this->decodeJson($request->get('data'));

        try {
            ConfigService::save($values);
        } catch (ConfigWriteException $e) {
            $this->magentoIntegrationLogger->error($e->getMessage());
        }

        return $this->jsonResponse(['success' => true]);
    }
}
