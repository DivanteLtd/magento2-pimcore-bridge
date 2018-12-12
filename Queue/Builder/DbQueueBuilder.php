<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Builder;

use Divante\PimcoreIntegration\Api\Queue\AssetQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\CategoryQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Api\Queue\ProductQueueRepositoryInterface;
use Divante\PimcoreIntegration\Exception\InvalidQueueTypeException;
use Divante\PimcoreIntegration\Http\Notification\NotificationStatusInterface;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificatorInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class DbQueueBuilder
 */
class DbQueueBuilder implements QueueBuilderInterface
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var ProductQueueRepositoryInterface
     */
    private $productQueueRepository;

    /**
     * @var CategoryQueueRepositoryInterface
     */
    private $categoryQueueRepository;

    /**
     * @var bool
     */
    private $isSendNotification;

    /**
     * @var PimcoreNotificatorInterface
     */
    private $notificator;

    /**
     * @var AssetQueueRepositoryInterface
     */
    private $assetQueueRepository;

    /**
     * DbQueueBuilder constructor.
     *
     * @param ProductQueueRepositoryInterface $productQueueRepository
     * @param CategoryQueueRepositoryInterface $categoryQueueRepository
     * @param AssetQueueRepositoryInterface $assetQueueRepository
     * @param PimcoreNotificatorInterface $notificator
     * @param BridgeLoggerFactory $loggerFactory
     * @param bool $isSendNotification
     */
    public function __construct(
        ProductQueueRepositoryInterface $productQueueRepository,
        CategoryQueueRepositoryInterface $categoryQueueRepository,
        AssetQueueRepositoryInterface $assetQueueRepository,
        PimcoreNotificatorInterface $notificator,
        BridgeLoggerFactory $loggerFactory,
        $isSendNotification = true
    ) {
        $this->productQueueRepository = $productQueueRepository;
        $this->categoryQueueRepository = $categoryQueueRepository;
        $this->notificator = $notificator;
        $this->logger = $loggerFactory->getLoggerInstance();
        $this->isSendNotification = $isSendNotification;
        $this->assetQueueRepository = $assetQueueRepository;
    }

    /**
     * @param DataObject $dto
     * @param string $type
     *
     * @throws LocalizedException
     * @return void
     */
    public function addToQueue(DataObject $dto, string $type)
    {
        try {
            switch ($type) {
                case ProductQueueInterface::class:
                    /** @var ProductQueueInterface $dto */
                    $this->productQueueRepository->save($dto);
                    break;
                case CategoryQueueInterface::class:
                    /** @var CategoryQueueInterface $dto */
                    $this->categoryQueueRepository->save($dto);
                    break;
                case AssetQueueInterface::class:
                    /** @var AssetQueueInterface $dto */
                    $this->assetQueueRepository->save($dto);
                    break;
                default:
                    throw new InvalidQueueTypeException(__('Invalid queue type.'));
            }

            $this->notificator
                ->setStatus(NotificationStatusInterface::SUCCESS)
                ->setMessage(sprintf(
                    '%s with ID %s successfully added to queue',
                    $dto->getQueueType(),
                    $dto->getPimcoreId()
                ));
        } catch (\Exception $ex) {
            $errMsg = sprintf('We were not able to push "%s" object to queue', $type);
            $this->notificator
                ->setStatus(NotificationStatusInterface::ERROR)
                ->setMessage($errMsg);

            $this->logger->critical($ex->getMessage());

            $this->tryToSendNotification($dto);

            throw new LocalizedException(__($errMsg));
        }

        $this->tryToSendNotification($dto);
    }

    /**
     * @param DataObject|QueueInterface $dto
     *
     * @return void
     */
    private function tryToSendNotification(DataObject $dto)
    {
        if (true === $this->isSendNotification) {
            $this->notificator->setUriPath(sprintf('%s/update-status', $dto->getQueueType()))
                ->setStoreViewId($dto->getData('store_view_id'))
                ->setPimId($dto->getPimcoreId())
                ->send();
        }
    }
}
