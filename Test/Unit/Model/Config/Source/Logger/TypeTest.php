<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Model\Config\Source\Logger;

use Divante\PimcoreIntegration\Model\Config\Source\Logger\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class TypeTest
 */
class TypeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testToOptionArrayStructure()
    {
        $expectedSource = [
            [
                'value' => Type::LOGGER_TYPE_STREAM,
                'label' => __('Stream'),
            ],
            [
                'value' => Type::LOGGER_TYPE_GRAYLOG,
                'label' => __('Graylog'),
            ],
        ];
        /** @var Type $source */
        $source = $this->objectManager->getObject(Type::class);
        $this->assertEquals($expectedSource, $source->toOptionArray());
    }
}
