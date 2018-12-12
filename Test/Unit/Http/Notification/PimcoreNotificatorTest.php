<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Http\Notification;

use Divante\PimcoreIntegration\Http\EndpointUrlBuilder;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificator;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificatorInterface;
use Divante\PimcoreIntegration\Http\Request\RequestClient;
use Divante\PimcoreIntegration\Http\UrlBuilderInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Rest\Request;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

/**
 * Class PimcoreNotificationTest
 */
class PimcoreNotificatorTest extends TestCase
{
    /**
     * @var RequestClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestClientFactory;

    /**
     * @var PimcoreNotificatorInterface
     */
    private $notificator;

    /**
     * @var UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlBuilder;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var BridgeLoggerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLoggerFactory;

    /**
     * @var RequestClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequestClient;

    /**
     * @var string
     */
    private $eventPrefix = 'prefix';

    public function setUp()
    {
        $om = new ObjectManager($this);

        $this->mockRequestClientFactory = $this->getMockBuilder('Divante\PimcoreIntegration\Http\Request\RequestClientFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mockRequestClient = $this->getMockBuilder(RequestClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['setEventPrefix', 'setStoreViewId', 'setMethod', 'setUri', 'setPostData', 'send'])
            ->getMock();

        $this->mockUrlBuilder = $this->getMockBuilder(EndpointUrlBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['build'])
            ->getMock();

        $this->mockLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['critical', 'info'])
            ->getMock();

        $this->mockLoggerFactory = $this->getMockBuilder(BridgeLoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLoggerInstance'])
            ->getMock();

        $this->mockLoggerFactory->expects($this->once())
            ->method('getLoggerInstance')
            ->willReturn($this->mockLogger);

        $this->notificator = $om->getObject(PimcoreNotificator::class, [
            'requestFactory' => $this->mockRequestClientFactory,
            'urlBuilder'     => $this->mockUrlBuilder,
            'loggerFactory'  => $this->mockLoggerFactory,
            'eventPrefix'    => $this->eventPrefix,
        ]);
    }

    /**
     * @return array
     */
    public function notificationDataProvider(): array
    {
        return [
            [
                [
                    'storeViewId' => '0',
                    'message'     => 'message',
                    'pimId'       => '100',
                    'status'      => 'error',
                ],
            ],
        ];
    }

    /**
     * @param array $data
     *
     * @dataProvider notificationDataProvider
     */
    public function testSendNotification(array $data)
    {
        $this->assertTrue($this->sendNotification($data));
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function sendNotification(array $data): bool
    {
        $this->mockRequestClientFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->mockRequestClient);

        $this->mockRequestClient->expects($this->once())
            ->method('setEventPrefix')
            ->with($this->eventPrefix)
            ->willReturn($this->mockRequestClient);

        $this->mockRequestClient->expects($this->once())
            ->method('setStoreViewId')
            ->with($data['storeViewId'])
            ->willReturn($this->mockRequestClient);

        $this->mockRequestClient->expects($this->once())
            ->method('setMethod')
            ->with(Request::HTTP_METHOD_POST)
            ->willReturn($this->mockRequestClient);

        $this->mockRequestClient->expects($this->once())
            ->method('setUri')
            ->willReturn($this->mockRequestClient);

        $this->mockRequestClient->expects($this->once())
            ->method('setPostData')
            ->with([
                'message' => $data['message'],
                'status'  => $data['status'],
                'id'      => $data['pimId'],
            ])
            ->willReturn($this->mockRequestClient);

        $result = $this->notificator->setStoreViewId($data['storeViewId'])
            ->setStatus($data['status'])
            ->setMessage($data['message'])
            ->setUriPath('product/update-status')
            ->setPimId($data['pimId'])
            ->send();

        return $result;
    }

    /**
     * @param array $data
     *
     * @dataProvider notificationDataProvider
     */
    public function testSendExceptionHandling(array $data)
    {
        $exMsg = 'exception message';

        $this->mockLogger->expects($this->once())
            ->method('critical')
            ->with($exMsg);

        $this->mockRequestClient->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception($exMsg));

        $this->assertFalse($this->sendNotification($data));
    }
}
