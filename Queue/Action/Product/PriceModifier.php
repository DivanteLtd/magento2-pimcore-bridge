<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Magento\Catalog\Model\Product;

/**
 * Class PriceModifier
 */
class PriceModifier implements DataModifierInterface
{
    /**
     * @var int
     */
    public static $defaultPriceValue = 0;

    /**
     * @param Product $product
     * @param PimcoreProductInterface $pimcoreProduct
     *
     * @return array
     */
    public function handle(Product $product, PimcoreProductInterface $pimcoreProduct): array
    {
        if (null === $pimcoreProduct->getData('price') && null === $product->getPrice()) {
            $pimcoreProduct->setData('price', self::$defaultPriceValue);
            $pimcoreProduct->setData('base_price', self::$defaultPriceValue);
        } elseif (null !== $product->getPrice()) {
            $pimcoreProduct->setData('price', $product->getPrice());
        }

        $pimcoreProduct->setData('base_price', $pimcoreProduct->getData('price'));

        return [$product, $pimcoreProduct];
    }
}
