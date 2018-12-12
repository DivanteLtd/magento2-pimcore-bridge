<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Queue\Builder;

use Divante\PimcoreIntegration\Http\Notification\NotificationStatusInterface;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificator;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificatorInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Logger\Stream\Logger;
use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueue;
use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueueRepository;
use Divante\PimcoreIntegration\Model\Queue\Product\ProductQueue;
use Divante\PimcoreIntegration\Model\Queue\Product\ProductQueueRepository;
use Divante\PimcoreIntegration\Queue\Builder\DbQueueBuilder;
use Divante\PimcoreIntegration\Queue\Builder\QueueBuilderInterface;
use Magento\Framework\DataObject;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * DbQueueBuilderTest
 */
class DbQueueBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BridgeLoggerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockLoggerFactory;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockLogger;

    /**
     * @var PimcoreNotificatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockNotificator;

    /**
     * @var CategoryQueueRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockCategoryQueueRepository;

    /**
     * @var ProductQueueRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mockProductQueueRepository;

    /**
     * @var QueueBuilderInterface
     */
    protected $dbQueueBuilder;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $notificatorUriPath = 'category/update-status';

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->mockProductQueueRepository = $this->getMockBuilder(ProductQueueRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->mockCategoryQueueRepository = $this->getMockBuilder(CategoryQueueRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $this->mockNotificator = $this->getMockBuilder(PimcoreNotificator::class)
            ->disableOriginalConstructor()
            ->setMethods(['send', 'setStatus', 'setMessage', 'setUriPath', 'setStoreViewId'])
            ->getMock();

        $this->mockLoggerFactory = $this->getMockBuilder(BridgeLoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLoggerInstance'])
            ->getMock();

        $this->mockLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['critical'])
            ->getMock();

        $this->mockLoggerFactory->expects($this->once())
            ->method('getLoggerInstance')
            ->willReturn($this->mockLogger);

        $this->dbQueueBuilder = $this->objectManager->getObject(DbQueueBuilder::class, [
            'productQueueRepository'  => $this->mockProductQueueRepository,
            'categoryQueueRepository' => $this->mockCategoryQueueRepository,
            'notificator'             => $this->mockNotificator,
            'loggerFactory'           => $this->mockLoggerFactory,
            'isSendNotification'      => true,
        ]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testInvalidQueueTypeException()
    {
        $storeViewId = '10';
        $type = 'invalid-type';

        $this->setNotificationExpectation(
            NotificationStatusInterface::ERROR,
            "We were not able to push \"{$type}\" object to queue",
            $this->notificatorUriPath,
            $storeViewId
        );

        $dto = $this->getDataObject();
        $dto->setData('store_view_id', $storeViewId);

        $this->mockLogger->expects($this->once())
            ->method('critical')
            ->with('Invalid queue type.');

        $this->dbQueueBuilder->addToQueue($dto, $type);
    }

    public function validDataProvider()
    {
        return [
            ['storeViewId' => '10', 'type' => ProductQueue::class],
            ['storeViewId' => '10', 'type' => CategoryQueue::class],
        ];
    }

    /**
     * @param string $status
     * @param string $message
     * @param string $uriPath
     * @param string $storeViewId
     *
     * @return void
     */
    private function setNotificationExpectation(string $status, string $message, string $uriPath, string $storeViewId)
    {
        $this->mockNotificator->expects($this->once())
            ->method('setStatus')
            ->with($status)
            ->willReturn($this->mockNotificator);

        $this->mockNotificator->expects($this->once())
            ->method('setMessage')
            ->with($message)
            ->willReturn($this->mockNotificator);

        $this->mockNotificator->expects($this->once())
            ->method('setUriPath')
            ->with($uriPath)
            ->willReturn($this->mockNotificator);

        $this->mockNotificator->expects($this->once())
            ->method('setStoreViewId')
            ->with($storeViewId)
            ->willReturn($this->mockNotificator);

        $this->mockNotificator->expects($this->once())
            ->method('send');
    }

    /**
     * @return mixed
     */
    private function getDataObject()
    {
        return $this->objectManager->getObject(DataObject::class);
    }
}
