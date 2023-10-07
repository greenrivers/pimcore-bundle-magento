<?php

declare(strict_types=1);

namespace Greenrivers\Bundle\MagentoIntegrationBundle\Model;

use Pimcore\Model\DataObject\Concrete;

abstract class AbstractCategory extends Concrete
{
    public const CLASS_ID = 'MagentoIntegrationCategory';
}
