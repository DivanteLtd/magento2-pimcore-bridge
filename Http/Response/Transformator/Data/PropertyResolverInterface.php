<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator\Data;

/**
 * Interface PropertyResolverInterface
 */
interface PropertyResolverInterface
{
    /**
     * @param string $code
     * @param array $properties
     *
     * @return PropertyInterface|null
     */
    public function getProperty(string $code, array $properties);
}
