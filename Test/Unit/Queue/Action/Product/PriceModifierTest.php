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
use Divante\PimcoreIntegration\System\Config;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @var PimcoreProduct|MockObject
     */
    private $pimcoreProduct;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var Price|MockObject
     */
    private $mockPriceModel;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->mockPriceModel = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();

        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceModel'])
            ->getMock();

        $this->pimcoreProduct = $this->objectManager->getObject(PimcoreProduct::class);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIsPriceOverride'])
            ->getMock();

        $this->priceModifier = $this->objectManager->getObject(PriceModifier::class, [
            'config' => $this->configMock
        ]);
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
            [15, 20, 20, true],
            [15, 20, 15, false],
        ];
    }

    /**
     * @dataProvider priceDataProvider
     */
    public function testHandle($price, $pimPrice, $final, $override = false)
    {
        $this->product->setPrice($price);
        $this->product->expects($this->any())
            ->method('getPriceModel')
            ->willReturn($this->mockPriceModel);

        $this->mockPriceModel->expects($this->any())
            ->method('getPrice')
            ->willReturn($price);

        $this->configMock->expects($this->any())
            ->method('getIsPriceOverride')
            ->willReturn($override);

        $this->pimcoreProduct->setData('price', $pimPrice);

        $result = $this->priceModifier->handle($this->product, $this->pimcoreProduct);
        $this->assertEquals($final, $result[1]->getPrice());
    }
}
