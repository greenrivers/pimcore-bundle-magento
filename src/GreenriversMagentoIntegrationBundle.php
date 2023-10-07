<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;
use Pimcore\Extension\Bundle\Traits\BundleAdminClassicTrait;
use function dirname;

class GreenriversMagentoIntegrationBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    use BundleAdminClassicTrait;

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    /**
     * @inheritDoc
     */
    public function getCssPaths(): array
    {
        return [
            '/bundles/greenriversmagentointegration/css/icons.css'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getJsPaths(): array
    {
        return [
            '/bundles/greenriversmagentointegration/js/startup.js',
            '/bundles/greenriversmagentointegration/js/settings.js'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }
}
