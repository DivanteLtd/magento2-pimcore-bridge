<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Model\Catalog\Product\Attribute\Creator\Strategy;

use Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\TextStrategy;
use Magento\Catalog\Model\Category\AttributeRepository;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class TextStrategyTest
 */
class TextStrategyTest extends BaseTestAbstract
{
    /**
     * @return array[]
     */
    public function attrDataProvider(): array
    {
        return [
            [['label' => 'test_label'], 'test'],
        ];
    }

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
     * @return string
     */
    public function getStrategyClass(): string
    {
        return TextStrategy::class;
    }

    /**
     * @return array|string[]
     */
    public function getBaseAttrConfig(): array
    {
        return [
            'type' => 'varchar',
            'label' => 'test_label',
            'input' => 'text',
        ];
    }
}
