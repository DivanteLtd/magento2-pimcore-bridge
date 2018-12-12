<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy;

/**
 * Interface AttributeCreationStrategyInterface
 */
interface AttributeCreationStrategyInterface
{
    /**
     * @return int
     */
    public function execute(): int;
}
