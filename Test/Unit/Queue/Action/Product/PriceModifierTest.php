<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Queue\Action\Product;

use Divante\PimcoreIntegration\Model\Pimcore\PimcoreProduct;
use Divante\PimcoreIntegration\Queue\Action\Product\DataModifierInterface;
use Divante\PimcoreIntegration\Queue\Action\Product\PriceModifier;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * PriceModifierTest
 */
class PriceModifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataModifierInterface
     */
    private $priceModifier;

    /**
     * @var PimcoreProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pimcoreProduct;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->product = $this->mockProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();

        $this->pimcoreProduct = $this->mockPimcoreProduct = $this->getMockBuilder(PimcoreProduct::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData'])
            ->getMock();

        $this->priceModifier = $this->objectManager->getObject(PriceModifier::class);
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
    public function testHandle($price, $pimPrice, $final)
    {
        $this->pimcoreProduct->expects($this->exactly(2))
            ->method('getData')
            ->willReturn($pimPrice);

        $this->product->expects($this->atLeast(1))
            ->method('getPrice')
            ->willReturn($price);

        $this->priceModifier->handle($this->product, $this->pimcoreProduct);
    }
}
