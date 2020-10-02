<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Model\Catalog\Product\Attribute\Creator\Strategy;

use Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\AbstractStrategy;
use Magento\Catalog\Model\Category\AttributeRepository;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class TextStrategyTest
 */
class AbstractStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|EavSetupFactory
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
     * @var array
     */
    private $defaultAttrConfig = [
        'backend' => '',
        'frontend' => '',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => ScopedAttributeInterface::SCOPE_STORE,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'searchable' => true,
        'filterable' => true,
        'comparable' => true,
        'visible_on_front' => true,
        'used_in_product_listing' => true,
        'unique' => false,
    ];

    public function setUp()
    {
        $this->eavSetupFactoryMock = $this->getMockBuilder(EavSetupFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->eavSetupMock = $this->getMockBuilder(EavSetup::class)
            ->disableOriginalConstructor()
            ->setMethods([])
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
            [['attr_conf' => ['test' => 1]], []],
            [['attr_conf' => ['test' => 1]], ['input' => 'select']],
            [['attr_conf' => ['test' => 1]], ['input' => 'select']],
            [['attr_conf' => ['test' => 1]], ['input' => 'select']],
            [['something_else' => [], 'attr_conf' => ['test' => 1]], ['input' => 'select']],
            [['missed_key' => ['test' => 1, 'required' => true]], []],
        ];
    }

    public function testGetDefaultAttrConfig()
    {
        $strategy = $this->getAbstractStrategyMockImplementation('test');
        $this->assertEquals($this->defaultAttrConfig, $strategy->getDefaultAttributeConfig());
    }

    /**
     * @dataProvider attrDataProvider
     */
    public function testGetMergedConfig(array $attrData, $base)
    {
        $strategy = $this->getAbstractStrategyMockImplementation('test', $attrData);
        $result = array_merge($this->defaultAttrConfig, $base, $attrData['attr_conf'] ?? []);
        $this->assertEquals($result, $strategy->getMergedConfig($base));
    }

    /**
     * @param string $code
     * @param array $attrData
     *
     * @return AbstractStrategy
     */
    private function getAbstractStrategyMockImplementation(string $code, array $attrData = [])
    {
        return new class(
            $this->eavSetupFactoryMock,
            $this->attributeRepositoryMock,
            $attrData,
            $code
        ) extends AbstractStrategy {
            public function getBaseAttrConfig(): array
            {
                return [];
            }

            public function execute(): int
            {
                return 1;
            }
        };
    }
}
