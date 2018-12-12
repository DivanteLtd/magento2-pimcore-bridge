<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Integration\Queue\Action\Product;

use Divante\PimcoreIntegration\Model\Pimcore\PimcoreProduct;
use Divante\PimcoreIntegration\Queue\Action\Product\DataModifierInterface;
use Divante\PimcoreIntegration\Queue\Action\Product\PriceModifier;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\ObjectManager;

class PriceModifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataModifierInterface
     */
    private $priceModifier;

    /**
     * @var PimcoreProduct
     */
    private $pimcoreProduct;

    /**
     * @var Product
     */
    private $product;

    public function setUp()
    {
        $this->product = ObjectManager::getInstance()->create(Product::class);
        $this->pimcoreProduct = ObjectManager::getInstance()->create(PimcoreProduct::class);

        $this->priceModifier = ObjectManager::getInstance()->create(PriceModifier::class);
    }

    /**
     * @return array
     */
    public function priceDataProvider(): array
    {
        return [
            [null, null, PriceModifier::$defaultPriceValue],
            [10, null, 10],
            [null, 10, 10],
            [12, 10, 12],
        ];
    }

    /**
     * @dataProvider priceDataProvider
     */
    public function testHandle($price, $pimPrice, $expected)
    {
        $this->product->setPrice($price);
        $this->pimcoreProduct->setData('price', $pimPrice);
        $this->priceModifier->handle($this->product, $this->pimcoreProduct);

        $this->assertSame($expected, $this->pimcoreProduct->getData('price'));
    }
}
