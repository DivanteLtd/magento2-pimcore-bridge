<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class ProductTypeResolver
 */
class ProductTypeResolver
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function resolveType(array $data): string
    {
        $type = Type::TYPE_SIMPLE;
        if ($this->isConfigurable($data)) {
            return Configurable::TYPE_CODE;
        }

        list($data, $type) = $this->beforeReturn($data, $type);

        return $type;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isConfigurable(array $data): bool
    {
        if (empty($data['type'])) {
            return false;
        }

        return $data['type'] === 'configurable_attributes' || $data['type'] === 'configurable';
    }

    /**
     * Plugin this method for custom types resolving
     *
     * @param array $data
     * @param string $type
     *
     * @return array
     */
    protected function beforeReturn(array $data, string $type): array
    {
        return [$data, $type];
    }
}
