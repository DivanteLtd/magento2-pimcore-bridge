<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Model\Catalog\Product\Attribute\Creator\Strategy;

use Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\QuantityValueStrategy;

/**
 * Class QuantityValueStrategyTest
 */
class QuantityValueStrategyTest extends BaseTestAbstract
{
    /**
     * @dataProvider attrDataProvider
     */
    public function testExecution(array $attrData, $code)
    {
        $strategy = $this->createStrategyObject($this->getStrategyClass(), $code, $attrData);

        $this->eavSetupFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->eavSetupMock);

        $this->eavSetupMock->expects($this->once())
            ->method('addAttribute');

        $this->eavSetupMock->expects($this->once())
            ->method('getAttributeId')
            ->willReturn(1);

        $strategy->execute();
    }

    /**
     * @return array|array[]
     */
    public function attrDataProvider(): array
    {
        return [
            [['label' => 'quantity', 'unit' => 'km'], 'test'],
        ];
    }

    /**
     * @return string
     */
    public function getStrategyClass(): string
    {
        return QuantityValueStrategy::class;
    }

    /**
     * @return array|string[]
     */
    public function getBaseAttrConfig(): array
    {
        return [
            'type' => 'decimal',
            'label' => 'quantity (km)',
            'input' => 'text',
        ];
    }
}
