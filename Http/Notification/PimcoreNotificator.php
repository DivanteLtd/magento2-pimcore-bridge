<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Notification;

use Divante\PimcoreIntegration\Api\RequestClientInterface;
use Divante\PimcoreIntegration\Http\Request\RequestClientFactory;
use Divante\PimcoreIntegration\Http\UrlBuilderInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Magento\Framework\Webapi\Rest\Request;
use Monolog\Logger;

/**
 * Class PimcoreNotification
 */
class PimcoreNotificator implements PimcoreNotificatorInterface
{
    /**
     * @var string
     */
    private $uriPath;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var RequestClientFactory
     */
    private $requestFactory;

    /**
     * @var string
     */
    private $pimId;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $storeViewId;

    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var string
     */
    private $eventPrefix;

    /**
     * PimcoreNotification constructor.
     *
     * @param RequestClientFactory $requestFactory
     * @param UrlBuilderInterface $urlBuilder
     * @param BridgeLoggerFactory $loggerFactory
     * @param string $eventPrefix
     */
    public function __construct(
        RequestClientFactory $requestFactory,
        UrlBuilderInterface $urlBuilder,
        BridgeLoggerFactory $loggerFactory,
        $eventPrefix = 'notification'
    ) {
        $this->requestFactory = $requestFactory;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $loggerFactory->getLoggerInstance();
        $this->eventPrefix = $eventPrefix;
    }

    /**
     * @return bool
     */
    public function send(): bool
    {
        try {
            /** @var RequestClientInterface $client */
            $client = $this->requestFactory->create();
            $client->setEventPrefix($this->eventPrefix)
                ->setStoreViewId($this->storeViewId)
                ->setMethod(Request::HTTP_METHOD_POST)
                ->setUri($this->urlBuilder->build($this->uriPath))
                ->setPostData([
                    'message' => $this->message,
                    'status'  => $this->status,
                    'id'      => $this->pimId,
                ]);

            $response = $client->send();
            $this->logger->info($response->getBody());
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @param string $pimId
     *
     * @return PimcoreNotificatorInterface
     */
    public function setPimId(string $pimId): PimcoreNotificatorInterface
    {
        $this->pimId = $pimId;

        return $this;
    }

    /**
     * @param string $message
     *
     * @return PimcoreNotificatorInterface
     */
    public function setMessage(string $message): PimcoreNotificatorInterface
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param string $status
     *
     * @return PimcoreNotificatorInterface
     */
    public function setStatus(string $status): PimcoreNotificatorInterface
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param string $storeViewId
     *
     * @return PimcoreNotificatorInterface
     */
    public function setStoreViewId(string $storeViewId): PimcoreNotificatorInterface
    {
        $this->storeViewId = $storeViewId;

        return $this;
    }

    public function setUriPath(string $uriPath): PimcoreNotificatorInterface
    {
        $this->uriPath = $uriPath;

        return $this;
    }
}
