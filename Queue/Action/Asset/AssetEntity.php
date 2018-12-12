<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

/**
 * Class AssetEntity
 */
class AssetEntity
{
    /**
     * Available entity types to import
     */
    const PRODUCT  = Product::ENTITY;
    const CATEGORY = Category::ENTITY;

    /**
     *
     * @return array
     */
    public static function getEntityTypes(): array
    {
        return [
            self::PRODUCT,
            self::CATEGORY,
        ];
    }
}
