<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Pimcore\Mapper;

/**
 * Class BooleanMapper
 */
class BooleanMapper implements ComplexMapperInterface
{
    /**
     * @param array $attributeData
     *
     * @return int
     */
    public function map(array $attributeData): int
    {
        if (empty($attributeData['value']['value'])) {
            $value = true;
        } else {
            $value = $attributeData['value']['value'] === 'true' ? true : false;
        }

        return $value;
    }
}
