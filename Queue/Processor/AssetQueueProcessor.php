<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Processor;

use Divante\PimcoreIntegration\Api\Queue\AssetQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificatorInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultFactory;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class AssetQueueProcessor
 */
class AssetQueueProcessor extends AbstractQueueProcessor
{
    /**
     * @var AssetQueueRepositoryInterface
     */
    private $assetQueueRepository;

    /**
     * AssetQueueProcessor constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ConfigInterface $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param ManagerInterface $eventManager
     * @param PimcoreNotificatorInterface $notificator
     * @param AssetQueueRepositoryInterface $assetQueueRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param bool $isSendNotification
     */
    public function __construct(
        ActionFactory $actionFactory,
        ConfigInterface $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BridgeLoggerFactory $bridgeLoggerFactory,
        ManagerInterface $eventManager,
        PimcoreNotificatorInterface $notificator,
        AssetQueueRepositoryInterface $assetQueueRepository,
        SortOrderBuilder $sortOrderBuilder,
        ActionResultFactory $actionResultFactory,
        bool $isSendNotification = true
    ) {
        parent::__construct(
            $actionFactory,
            $config,
            $searchCriteriaBuilder,
            $bridgeLoggerFactory,
            $eventManager,
            $notificator,
            $sortOrderBuilder,
            $actionResultFactory,
            $isSendNotification
        );

        $this->assetQueueRepository = $assetQueueRepository;
    }

    /**
     *
     * @return int
     */
    protected function getPageSize(): int
    {
        return $this->config->getAssetQueueProcess();
    }

    /**
     * @param QueueInterface $queue
     *
     * @return string
     */
    protected function getActionType(QueueInterface $queue): string
    {
        return ('asset/' . $queue->getAction());
    }

    /**
     * @param QueueInterface|AssetQueueInterface $queue
     *
     * @return string
     */
    protected function getSuccessNotificationMessage(QueueInterface $queue): string
    {
        return sprintf(
            'Asset with ID "%s" has been successfully %s',
            $queue->getAssetId(),
            $queue->getAction()
        );
    }

    /**
     * @param QueueInterface|AssetQueueInterface $queue
     * @param \Exception $ex
     *
     * @return string
     */
    protected function getErrorNotificationMessage(QueueInterface $queue, \Exception $ex): string
    {
        return sprintf(
            'An error occurred while %s asset "%s": %s',
            $queue->getAction(),
            $queue->getAssetId(),
            $ex->getMessage()
        );
    }

    /**
     *
     * @return mixed
     */
    protected function getRepository()
    {
        return $this->assetQueueRepository;
    }

    /**
     * @param QueueInterface|AssetQueueInterface $queue
     *
     * @return string
     */
    protected function getPimObjectId(QueueInterface $queue): string
    {
        return $queue->getAssetId();
    }

    /**
     *
     * @return string
     */
    protected function getNotificationUriPath(): string
    {
        return 'asset/update-status';
    }
}
