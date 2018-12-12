<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Pimcore\Mapper;

/**
 * Interface ComplexMapperInterface
 */
interface ComplexMapperInterface
{
    /**
     * @param array $attributeData
     *
     * @return mixed
     */
    public function map(array $attributeData);
}
