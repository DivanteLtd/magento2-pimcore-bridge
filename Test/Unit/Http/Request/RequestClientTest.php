<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Test\Unit\Http\Request;

use Divante\PimcoreIntegration\Http\Request\AbstractRequest;
use Divante\PimcoreIntegration\Http\Request\RequestClient;
use Divante\PimcoreIntegration\Http\UrlBuilderInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\System\Config;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\ClientFactory;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\ParametersInterface;

/**
 * Class RequestClientTest
 */
class RequestClientTest extends TestCase
{
    /**
     * @var ClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockHttpClientFactory;

    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockHttpClient;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEventManager;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockConfigInterface;

    /**
     * @var BridgeLoggerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockBridgeLoggerFactory;

    /**
     * @var UrlBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlBuilder;

    /**
     * @var ParametersInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockQuery;

    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockResponse;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockLogger;

    /**
     * @var string
     */
    private $pimcoreApiKey = 'apiKey';

    /**
     * @var string
     */
    private $uri = 'http://some.uri';

    /**
     * @var string
     */
    private $method = 'GET';

    /**
     * @var int
     */
    private $defaultStoreView = 0;

    /**
     * @var string
     */
    private $beforeSendEvent = 'default_request_before';

    /**
     * @var string
     */
    private $afterSendEvent = 'default_request_after';

    /**
     * @var string
     */
    private $exMsg = 'something very bad happen!';

    /**
     * @var Request
     */
    private $mockRequest;

    /**
     * @var RequestClient
     */
    private $requestClient;

    public function setUp()
    {
        $this->mockHttpClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getRequest',
                'setHeaders',
                'setUri',
                'setMethod',
                'send',
                'getResponse',
                'setParameterPost',
                'setParameterGet',
            ])
            ->getMock();

        $this->mockHttpClientFactory = $this->getMockBuilder(ClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->mockHttpClientFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->mockHttpClient);

        $this->mockRequest = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuery'])
            ->getMock();

        $this->mockQuery = $this->getMockForAbstractClass(ParametersInterface::class);

        $this->mockRequest->expects($this->any())->method('getQuery')->willReturn($this->mockQuery);

        $this->mockHttpClient->expects($this->any())->method('getRequest')->willReturn($this->mockRequest);

        $this->mockResponse = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockHttpClient->expects($this->any())->method('getResponse')->willReturn($this->mockResponse);

        $mockCurlAdapter = $this->getMockBuilder(Curl::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->mockEventManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();

        $this->mockConfigInterface = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPimcoreApiKey', 'setUri', 'setMethod', 'getInstanceUrl'])
            ->getMock();

        $this->mockConfigInterface->expects($this->any())->method('getPimcoreApiKey')->willReturn($this->pimcoreApiKey);
        $this->mockConfigInterface->expects($this->any())->method('getInstanceUrl')->willReturn('http://some-url.com');

        $this->mockBridgeLoggerFactory = $this->getMockBuilder(BridgeLoggerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLoggerInstance'])
            ->getMock();

        $this->mockLogger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->setMethods(['critical', 'info'])
            ->getMock();

        $this->mockBridgeLoggerFactory->expects($this->once())
            ->method('getLoggerInstance')
            ->willReturn($this->mockLogger);

        $this->mockUrlBuilder = $this->getMockBuilder(UrlBuilderInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $om = new ObjectManager($this);

        $this->requestClient = $om->getObject(RequestClient::class, [
            'httpClientFactory'   => $this->mockHttpClientFactory,
            'curlAdapter'         => $mockCurlAdapter,
            'eventManager'        => $this->mockEventManager,
            'config'              => $this->mockConfigInterface,
            'bridgeLoggerFactory' => $this->mockBridgeLoggerFactory,
        ]);
    }

    /**
     * Test setting post data
     */
    public function testSetPostData()
    {
        $data = ['someData' => 1];
        $this->mockHttpClient->expects($this->once())->method('setParameterPost')->with($data);

        $this->assertEquals($this->requestClient, $this->requestClient->setPostData($data));
    }

    /**
     * Test setting query data params
     */
    public function testSetQueryData()
    {
        $data = ['someData' => 1];
        $this->mockHttpClient->expects($this->once())->method('setParameterGet')->with($data);

        $this->assertEquals($this->requestClient, $this->requestClient->setQueryData($data));
    }

    /**
     * Test successful request execution
     */
    public function testSuccessfulSendRequest()
    {
        $this->prepareRequest();

        $this->mockEventManager->expects($this->at(0))
            ->method('dispatch')
            ->with($this->beforeSendEvent, ['request' => $this->mockRequest]);

        $this->mockHttpClient->expects($this->once())->method('send');

        $this->mockEventManager->expects($this->at(1))
            ->method('dispatch')
            ->with($this->afterSendEvent, ['response' => $this->mockResponse]);

        $this->assertEquals($this->mockResponse, $this->requestClient->send());
    }

    /**
     * Prepares request
     */
    private function prepareRequest()
    {
        $this->mockHttpClient->expects($this->once())
            ->method('setHeaders')
            ->with([
                'Authorization' => 'Bearer ' . $this->pimcoreApiKey,
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/x-www-form-urlencoded',
            ]);

        $this->requestClient->setUri($this->uri)
            ->setMethod($this->method)
            ->setStoreViewId('0');

        $this->mockHttpClient->expects($this->once())
            ->method('setUri')
            ->with($this->uri);

        $this->mockHttpClient->expects($this->atLeastOnce())
            ->method('setMethod')
            ->with($this->method);

        $this->mockQuery->expects($this->at(1))
            ->method('set')
            ->with($this->requestClient::QUERY_PARAM_API_KEY, $this->pimcoreApiKey);

        $this->mockQuery->expects($this->at(2))
            ->method('set')
            ->with($this->requestClient::QUERY_PARAM_STORE_VIEW, $this->defaultStoreView);
    }

    /**
     * Test request execution with exception thrown
     */
    public function testInvalidSendRequest()
    {
        $this->prepareRequest();

        $this->mockHttpClient->expects($this->once())->method('send')
            ->willThrowException(new \Exception($this->exMsg));

        $this->mockLogger->expects($this->once())->method('critical')->with($this->exMsg);

        $this->assertEquals($this->mockResponse, $this->requestClient->send());
    }
}
