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
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class TestAbstract
 */
abstract class BaseTestAbstract extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var MockObject|EavSetupFactory
     */
    protected $eavSetupFactoryMock;

    /**
     * @var MockObject|EavSetup
     */
    protected $eavSetupMock;

    /**
     * @var MockObject|AttributeRepository
     */
    protected $attributeRepositoryMock;

    /**
     * Test Setup
     */
    public function setUp()
    {
        $this->om = new ObjectManager($this);

        $this->eavSetupFactoryMock = $this->getMockBuilder(EavSetupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->eavSetupMock = $this->getMockBuilder(EavSetup::class)
            ->disableOriginalConstructor()
            ->setMethods(['addAttribute', 'getAttributeId'])
            ->getMock();

        $this->eavSetupMock = $this->getMockBuilder(EavSetup::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->attributeRepositoryMock = $this->getMockBuilder(AttributeRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    /**
     * @return array[]
     */
    abstract public function attrDataProvider(): array;

    /**
     * @return string
     */
    abstract public function getStrategyClass(): string;

    /**
     * @return array
     */
    abstract public function getBaseAttrConfig(): array;

    /**
     * @dataProvider attrDataProvider
     */
    public function testExecution(array $attrData, $code)
    {
        $this->markTestIncomplete('You must implement execution test.');
    }

    /**
     * @dataProvider attrDataProvider
     */
    public function testGetBaseAttrConf(array $attrData, $code)
    {
        $strategy = $this->createStrategyObject($this->getStrategyClass(), $code, $attrData);
        $this->assertEquals($this->getBaseAttrConfig(), $strategy->getBaseAttrConfig());
    }

    /**
     * @param string $code
     * @param array $attrData
     *
     * @return object
     */
    protected function createStrategyObject(string $strategyClass, string $code, array $attrData = [])
    {
        return $this->om->getObject($strategyClass, [
            'eavSetupFactory' => $this->eavSetupFactoryMock,
            'attributeRepository' => $this->attributeRepositoryMock,
            'attrData' => $attrData,
            'code' => $code
        ]);
    }
}
