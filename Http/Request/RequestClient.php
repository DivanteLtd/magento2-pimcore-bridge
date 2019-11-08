<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Request;

use Divante\PimcoreIntegration\Api\RequestClientInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\ClientFactory;
use Zend\Http\Exception\InvalidArgumentException;
use Zend\Http\Response;

/**
 * Class RequestClient
 */
class RequestClient implements RequestClientInterface
{
    /**
     * Required request query param name
     */
    const QUERY_PARAM_INSTANCE_URL = 'instanceUrl';

    const QUERY_PARAM_STORE_VIEW   = 'storeViewId';

    const QUERY_PARAM_API_KEY      = 'apikey';

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $storeViewId = 0;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $eventPrefix = 'default';

    /**
     * RequestClient constructor.
     *
     * @param Client $httpClient
     * @param Curl $curlAdapter
     * @param ManagerInterface $eventManager
     * @param ConfigInterface $config
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     */
    public function __construct(
        ClientFactory $httpClientFactory,
        Curl $curlAdapter,
        ManagerInterface $eventManager,
        ConfigInterface $config,
        BridgeLoggerFactory $bridgeLoggerFactory
    ) {
        $this->curlAdapter = $curlAdapter;
        $this->curlAdapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->curlAdapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->eventManager = $eventManager;
        $this->config = $config;
        $this->logger = $bridgeLoggerFactory->getLoggerInstance();
        $this->httpClient = $httpClientFactory->create();
        $this->httpClient->setAdapter($curlAdapter);
    }

    /**
     * @return Response
     */
    public function send(): Response
    {
        try {
            $this->prepareRequest();

            $this->eventManager->dispatch(
                $this->getBeforeSendEventName(),
                ['request' => $this->httpClient->getRequest()]
            );

            $this->logRequest();

            $this->httpClient->send();

            $this->eventManager->dispatch(
                $this->getAfterSendEventName(),
                ['response' => $this->httpClient->getResponse()]
            );
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }

        return $this->httpClient->getResponse();
    }

    /**
     * Prepares request
     *
     * @return $this
     */
    private function prepareRequest()
    {
        $this->setHeaders()->setUriAndMethod()->setRequestData();

        return $this;
    }

    /**
     * @return $this
     */
    private function setRequestData()
    {
        $request = $this->httpClient->getRequest();
        $query = $request->getQuery();
        $query->set(self::QUERY_PARAM_INSTANCE_URL, $this->config->getInstanceUrl());
        $query->set(self::QUERY_PARAM_API_KEY, $this->config->getPimcoreApiKey());
        $query->set(self::QUERY_PARAM_STORE_VIEW, $this->getStoreViewId());

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreViewId(): int
    {
        return $this->storeViewId;
    }

    /**
     * @param int $storeViewId
     *
     * @return RequestClientInterface
     */
    public function setStoreViewId(int $storeViewId): RequestClientInterface
    {
        $this->storeViewId = $storeViewId;

        return $this;
    }

    /**
     * @return $this
     */
    private function setUriAndMethod()
    {
        $this->httpClient->setUri($this->getUri());
        $this->httpClient->setMethod($this->getMethod());

        return $this;
    }

    /**
     * Get request url
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     *
     * @return RequestClientInterface
     */
    public function setUri(string $uri): RequestClientInterface
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     *
     * @return RequestClientInterface
     */
    public function setMethod(string $method = Request::HTTP_METHOD_GET): RequestClientInterface
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setHeaders()
    {
        $this->httpClient->setHeaders([
            'Authorization' => 'Bearer ' . $this->config->getPimcoreApiKey(),
            'Content-Type'  => 'application/x-www-form-urlencoded',
            'Accept'        => 'application/x-www-form-urlencoded',
        ]);

        return $this;
    }

    /**
     * Get before send event name
     *
     * @return string
     */
    private function getBeforeSendEventName(): string
    {
        return sprintf('%s_request_before', $this->eventPrefix);
    }

    /**
     * Log request data
     *
     * @return void
     */
    private function logRequest()
    {
        $params = sprintf(
            "QUERY: %s\nPOST: %s",
            urldecode($this->httpClient->getRequest()->getQuery()->toString()),
            urldecode($this->httpClient->getRequest()->getPost()->toString())
        );
        $this->logger->info(
            sprintf(
                "\n====REQUEST====\n%s%s",
                $this->httpClient->getRequest()->toString(),
                $params
            )
        );
    }

    /**
     * Get after send event name
     *
     * @return string
     */
    private function getAfterSendEventName(): string
    {
        return sprintf('%s_request_after', $this->eventPrefix);
    }

    /**
     * @param array $data
     *
     * @return RequestClientInterface
     */
    public function setPostData(array $data): RequestClientInterface
    {
        $this->httpClient->setParameterPost($data);

        return $this;
    }

    /**
     * @param array $data
     *
     * @return RequestClientInterface
     */
    public function setQueryData(array $data): RequestClientInterface
    {
        $this->httpClient->setParameterGet($data);

        return $this;
    }

    /**
     * @param string $prefix
     *
     * @return RequestClientInterface
     */
    public function setEventPrefix(string $prefix): RequestClientInterface
    {
        $this->eventPrefix = $prefix;

        return $this;
    }
}
