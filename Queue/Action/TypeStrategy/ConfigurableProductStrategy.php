<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\TypeStrategy;

use Divante\PimcoreIntegration\Exception\InvalidDataStructureException;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\PropertyInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;

/**
 * Class ConfigurableProductStrategy
 */
class ConfigurableProductStrategy implements ProductTypeCreationStrategyInterface
{
    /**
     * @param ProductInterface $product
     *
     * @throws InvalidDataStructureException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return ProductInterface
     */
    public function execute(ProductInterface $product): ProductInterface
    {
        $product->setTypeId(Configurable::TYPE_CODE);
        return $product;
    }
}
