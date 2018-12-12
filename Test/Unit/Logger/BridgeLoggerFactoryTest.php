<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Logger;

use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Logger\Greylog\LoggerFactory as GraylogLoggerFactory;
use Divante\PimcoreIntegration\Logger\Stream\LoggerFactory as StreamLoggerFactory;
use Divante\PimcoreIntegration\Model\Config\Source\Logger\Type;
use Divante\PimcoreIntegration\System\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * Class BridgeLoggerFactoryTest
 */
class BridgeLoggerFactoryTest extends TestCase
{
    /**
     * @var BridgeLoggerFactory
     */
    private $bridgeLoggerFactory;

    /**
     * @var GraylogLoggerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockGraylogLoggerFactory;

    /**
     * @var StreamLoggerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockStreamLoggerFactory;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfig;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->mockStreamLoggerFactory = $this->getMockBuilder(StreamLoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mockGraylogLoggerFactory = $this->getMockBuilder(GraylogLoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    /**
     */
    public function testGetLoggerInstance()
    {
        foreach ($this->loggerTypesDataProvider() as $data) {
            $this->mockConfig = $this->getMockBuilder(Config::class)
                ->disableOriginalConstructor()
                ->setMethods(['getLoggerType'])
                ->getMock();

            $this->mockConfig->expects($this->any())
                ->method('getLoggerType')
                ->willReturn($data['type']);

            $this->bridgeLoggerFactory = $this->objectManager->getObject(BridgeLoggerFactory::class, [
                'config'               => $this->mockConfig,
                'streamLoggerFactory'  => $this->mockStreamLoggerFactory,
                'graylogLoggerFactory' => $this->mockGraylogLoggerFactory,
            ]);

            $data['factory']->expects($this->once())
                ->method('create')
                ->willReturn(
                    $this->getMockBuilder(Logger::class)
                        ->disableOriginalConstructor()
                        ->getMock()
                );

            $this->bridgeLoggerFactory->getLoggerInstance();
        }
    }

    /**
     * Provider for Types of available loggers
     *
     * @return array
     */
    public function loggerTypesDataProvider()
    {
        return [
            ['type' => Type::LOGGER_TYPE_STREAM, 'factory' => $this->mockStreamLoggerFactory],
            ['type' => Type::LOGGER_TYPE_GRAYLOG, 'factory' => $this->mockGraylogLoggerFactory],
        ];
    }
}
