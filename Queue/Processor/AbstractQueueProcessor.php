<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Processor;

use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Http\Notification\NotificationStatusInterface;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificatorInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultInterface;
use Divante\PimcoreIntegration\Queue\QueueProcessorInterface;
use Divante\PimcoreIntegration\Queue\QueueStatusInterface;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class AbstractProcessor
 */
abstract class AbstractQueueProcessor implements QueueProcessorInterface
{
    /**
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var PimcoreNotificatorInterface
     */
    protected $notificator;

    /**
     * @var bool
     */
    protected $isSendNotification;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var ActionResultFactory
     */
    protected $actionResultFactory;

    /**
     * AbstractProcessor constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ConfigInterface $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param ManagerInterface $eventManager
     * @param PimcoreNotificatorInterface $notificator
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ActionResultFactory $actionResultFactory
     * @param bool $isSendNotification
     */
    public function __construct(
        ActionFactory $actionFactory,
        ConfigInterface $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BridgeLoggerFactory $bridgeLoggerFactory,
        ManagerInterface $eventManager,
        PimcoreNotificatorInterface $notificator,
        SortOrderBuilder $sortOrderBuilder,
        ActionResultFactory $actionResultFactory,
        bool $isSendNotification = true
    ) {
        $this->actionFactory = $actionFactory;
        $this->config = $config;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->eventManager = $eventManager;
        $this->notificator = $notificator;
        $this->logger = $bridgeLoggerFactory->getLoggerInstance();
        $this->isSendNotification = $isSendNotification;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->actionResultFactory = $actionResultFactory;
    }

    /**
     * @return int
     */
    public function predictQueueLength()
    {
        $searchResults = $this->getQueueItems();
        return $searchResults->getTotalCount();
    }

    /**
     * @return void
     */
    public function process()
    {
        $searchResults = $this->getQueueItems();

        if (!$searchResults->getTotalCount()) {
            return;
        }

        /** @var QueueInterface $queue */
        foreach ($searchResults->getItems() as $queue) {
            try {
                $action = $this->actionFactory->createByType($this->getActionType($queue));
                $result = $action->execute($queue);

                $this->notificator->setMessage($this->getSuccessNotificationMessage($queue));
                $this->notificator->setStatus($this->resolveNotificationStatus($queue));

                $queue->setStatus(QueueStatusInterface::COMPLETED);
            } catch (\Exception $ex) {
                $this->logger->critical($ex->getMessage());

                $this->notificator
                    ->setMessage($this->getErrorNotificationMessage($queue, $ex))
                    ->setStatus(NotificationStatusInterface::ERROR);

                $queue->setStatus(QueueStatusInterface::ERROR);
                $result = $this->actionResultFactory->create(['result' => ActionResultInterface::ERROR]);
            }

            try {
                if ($result->getResult() === ActionResultInterface::SKIPPED) {
                    $this->skipQueueAndPushToEndOfStack($queue);
                } else {
                    $this->closeQueueAndSendNotification($queue);
                }

                $this->getRepository()->save($queue);
            } catch (\Exception $ex) {
                $this->logger->critical($ex->getMessage());
            }

            $this->eventManager->dispatch('queue_processed_after', [
                'result' => $result,
                'queue'  => $queue,
            ]);
        }
    }

    /**
     *
     * @return SearchResultInterface
     */
    protected function getQueueItems(): SearchResultInterface
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilder->setPageSize($this->getPageSize());
        $searchCriteriaBuilder->addFilter('status', QueueStatusInterface::PENDING);
        $searchCriteria = $searchCriteriaBuilder->create();
        $sortOrder = $this->sortOrderBuilder->setAscendingDirection()->setField('updated_at')->create();
        $searchCriteria->setSortOrders([$sortOrder]);

        return $this->getRepository()->getList($searchCriteria);
    }

    /**
     *
     * @return int
     */
    abstract protected function getPageSize(): int;

    /**
     *
     * @return mixed
     */
    abstract protected function getRepository();

    /**
     * @param QueueInterface $queue
     *
     * @return string
     */
    abstract protected function getActionType(QueueInterface $queue): string;

    /**
     * @param $queue
     *
     * @return string
     */
    abstract protected function getSuccessNotificationMessage(QueueInterface $queue): string;

    /**
     * @param QueueInterface $queue
     *
     * @return string
     */
    protected function resolveNotificationStatus(QueueInterface $queue): string
    {
        return ($queue->getAction() === 'delete')
            ? NotificationStatusInterface::DELETED
            : NotificationStatusInterface::SUCCESS;
    }

    /**
     * @param QueueInterface $queue
     * @param \Exception $ex
     *
     * @return string
     */
    abstract protected function getErrorNotificationMessage(QueueInterface $queue, \Exception $ex): string;

    /**
     * @param QueueInterface $queue
     *
     * @return void
     */
    protected function skipQueueAndPushToEndOfStack(QueueInterface $queue)
    {
        $queue->setStatus(QueueStatusInterface::PENDING);
        $queue->setUpdatedAt(date('Y-m-d H:i:s'));
        $this->logger->info(
            __(
                'Action "%1 for a pimcore object with ID %2 was skipped.',
                $queue->getAction(),
                $queue->getPimcoreId()
            )
        );
    }

    /**
     * @param QueueInterface $queue
     *
     * @return void
     */
    protected function closeQueueAndSendNotification(QueueInterface $queue)
    {
        $queue->setFinishedAt(date('Y-m-d H:i:s'));

        $this->notificator
            ->setPimId($this->getPimObjectId($queue))
            ->setUriPath($this->getNotificationUriPath())
            ->setStoreViewId($queue->getStoreViewId());

        if (true === $this->isSendNotification) {
            $this->notificator->send();
        }
    }

    /**
     * @param QueueInterface $queue
     *
     * @return string
     */
    abstract protected function getPimObjectId(QueueInterface $queue): string;

    /**
     *
     * @return string
     */
    abstract protected function getNotificationUriPath(): string;
}
