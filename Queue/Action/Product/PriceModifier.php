<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Divante\PimcoreIntegration\System\ConfigInterface;
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
     * @var ConfigInterface
     */
    private $config;

    /**
     * PriceModifier constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

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
        } elseif ($this->isPriceAlreadySet($product)) {
            if ($this->config->getIsPriceOverride()) {
                $pimcoreProduct->setData('price', $pimcoreProduct->getData('price') ?? self::$defaultPriceValue);
            } else {
                $pimcoreProduct->setData('price', $product->getPrice());
            }
        }

        $pimcoreProduct->setData('base_price', $pimcoreProduct->getData('price'));

        return [$product, $pimcoreProduct];
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    private function isPriceAlreadySet(Product $product): bool
    {
        return (null !== $product->getPrice());
    }
}
