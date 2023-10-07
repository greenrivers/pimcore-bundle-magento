<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\EventListener;

use Greenrivers\Bundle\MagentoIntegrationBundle\Model\AbstractCategory;
use Greenrivers\Bundle\MagentoIntegrationBundle\Model\AbstractProduct;
use Greenrivers\Bundle\MagentoIntegrationBundle\Service\ConfigService;
use Greenrivers\Bundle\MagentoIntegrationBundle\Service\GraphqlService;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Pimcore\Event\DataObjectEvents;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\ClassDefinition;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataObjectListener implements EventSubscriberInterface
{
    /**
     * DataObjectListener constructor.
     * @param GraphqlService $graphqlService
     */
    public function __construct(private readonly GraphqlService $graphqlService)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DataObjectEvents::POST_UPDATE => 'onObjectUpdate'
        ];
    }

    /**
     * @param DataObjectEvent $event
     * @return void
     * @throws GuzzleException
     * @throws JsonException
     * @throws RuntimeException
     */
    public function onObjectUpdate(DataObjectEvent $event): void
    {
        $config = ConfigService::getMagentoIntegrationConfig();
        ['magentoUrl' => $magentoUrl, 'magentoToken' => $magentoToken] = $config['general'];
        ['sendProductOnSave' => $sendProductOnSave, 'sendCategoryOnSave' => $sendCategoryOnSave] = $config['pimcore'];

        $isAutoSave = $event->hasArgument('isAutoSave');
        $object = $event->getObject();
        $data = $object->getObjectVars();

        /** @var ClassDefinition $class */
        $class = $object->getClass();
        $classId = $class->getId();

        if (!$isAutoSave) {
            if ($classId === AbstractProduct::CLASS_ID && $sendProductOnSave) {
                $response = $this->graphqlService->sendProduct($magentoUrl, $magentoToken, $data);
                $this->graphqlService->processResponse($response);
            } elseif ($classId === AbstractCategory::CLASS_ID && $sendCategoryOnSave) {
                $response = $this->graphqlService->sendCategory($magentoUrl, $magentoToken, $data);
                $this->graphqlService->processResponse($response);
            }
        }
    }
}
