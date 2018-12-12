<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Cron;

use Divante\PimcoreIntegration\Cron\OldQueueEntriesRemover;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Model\Config\Source\QueueOutdated;
use Divante\PimcoreIntegration\System\Config;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Monolog\Logger;

/**
 * Class OldQueueEntriesRemoverTest
 */
class OldQueueEntriesRemoverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var OldQueueEntriesRemover
     */
    private $remover;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfig;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var BridgeLoggerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockBridgeLoggerFactory;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockResourceConnection;

    /**
     * @var Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConnection;

    public function setUp()
    {
        $this->mockResourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConnection'])
            ->getMock();

        $this->mockConnection = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['delete', 'getTableName'])
            ->getMock();

        $this->mockResourceConnection->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->mockConnection);

        $this->mockBridgeLoggerFactory = $this->getMockBuilder(BridgeLoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLoggerInstance'])
            ->getMock();

        $this->mockLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['info'])
            ->getMock();

        $this->mockBridgeLoggerFactory->expects($this->any())
            ->method('getLoggerInstance')
            ->willReturn($this->mockLogger);

        $this->mockConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQueueOutdatedValue'])
            ->getMock();

        $om = new ObjectManager($this);
        $this->remover = $om->getObject(OldQueueEntriesRemover::class, [
            'resource'      => $this->mockResourceConnection,
            'config'        => $this->mockConfig,
            'loggerFactory' => $this->mockBridgeLoggerFactory,
        ]);
    }

    /**
     * When setting are set to NEVER remove
     *
     * @throws \Exception
     */
    public function testNeverRemoveAction()
    {
        $this->mockConfig->expects($this->once())
            ->method('getQueueOutdatedValue')
            ->willReturn(QueueOutdated::NEVER);

        $this->assertNull($this->remover->execute());
    }

    /**
     * @throws \Exception
     */
    public function testRemoveExecutionWithSuccessfulDeletion()
    {
        $deletedEntitiesCount = 2;
        $this->mockConfig->expects($this->once())
            ->method('getQueueOutdatedValue')
            ->willReturn(QueueOutdated::AFTER_60D);

        $this->mockConnection->expects($this->any())
            ->method('getTableName')
            ->willReturn('divante_pimcore_category_queue');

        $this->mockConnection->expects($this->exactly(3))
            ->method('delete')
            ->willReturn($deletedEntitiesCount);

        $this->mockLogger->expects($this->any())->method('info');

        $this->remover->execute();
    }
}
