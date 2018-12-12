<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

/**
 * Class MultiobjectStrategy
 */
class MultiobjectStrategy extends AbstractObjectTypeStrategy
{
    /**
     * @return string
     */
    protected function getEventName(): string
    {
        return 'pimcore_attribute_creation_type_multiobject';
    }
}
