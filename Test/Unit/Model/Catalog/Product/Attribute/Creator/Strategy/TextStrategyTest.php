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
class TextStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var MockObject|EavSetupFactory\
     */
    private $eavSetupFactoryMock;

    /**
     * @var MockObject|EavSetup
     */
    private $eavSetupMock;

    /**
     * @var MockObject|AttributeRepository
     */
    private $attributeRepositoryMock;

    /**
     * @var string[]
     */
    private $baseAttrConf = [
        'type' => 'varchar',
        'label' => 'test_label',
        'input' => 'text',
    ];

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
    public function attrDataProvider()
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
        $strategy = $this->createStrategyObject($code, $attrData);

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
     * @dataProvider attrDataProvider
     */
    public function testGetBaseAttrConf(array $attrData, $code)
    {
        $strategy = $this->createStrategyObject($code, $attrData);
        $this->assertEquals($this->baseAttrConf, $strategy->getBaseAttrConfig());
    }

    /**
     * @param string $code
     * @param array $attrData
     *
     * @return TextStrategy|object
     */
    private function createStrategyObject(string $code, array $attrData = [])
    {
        return $this->om->getObject(TextStrategy::class, [
            'eavSetupFactory' => $this->eavSetupFactoryMock,
            'attributeRepository' => $this->attributeRepositoryMock,
            'attrData' => $attrData,
            'code' => $code
        ]);
    }
}
