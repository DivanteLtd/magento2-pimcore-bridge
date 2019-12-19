<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Processor;

use Divante\PimcoreIntegration\Api\Queue\CategoryQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificatorInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionFactory;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Event\ManagerInterface;
use Divante\PimcoreIntegration\Queue\Action\ActionResultFactory;
use Magento\Store\Model\App\Emulation;

/**
 * Class CategoryQueueProcessor
 */
class CategoryQueueProcessor extends AbstractQueueProcessor
{
    /**
     * @var CategoryQueueRepositoryInterface
     */
    private $categoryQueueRepository;

    /**
     * CategoryQueueProcessor constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ConfigInterface $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param ManagerInterface $eventManager
     * @param PimcoreNotificatorInterface $notificator
     * @param CategoryQueueRepositoryInterface $categoryQueueRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ActionResultFactory $actionResultFactory
     * @param Emulation $emulation
     * @param bool $isSendNotification
     */
    public function __construct(
        ActionFactory $actionFactory,
        ConfigInterface $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BridgeLoggerFactory $bridgeLoggerFactory,
        ManagerInterface $eventManager,
        PimcoreNotificatorInterface $notificator,
        CategoryQueueRepositoryInterface $categoryQueueRepository,
        SortOrderBuilder $sortOrderBuilder,
        ActionResultFactory $actionResultFactory,
        Emulation $emulation,
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
            $emulation,
            $isSendNotification
        );
        $this->categoryQueueRepository = $categoryQueueRepository;
    }

    /**
     *
     * @return int
     */
    protected function getPageSize(): int
    {
        return $this->config->getCategoryQueueProcess();
    }

    /**
     * @param QueueInterface $queue
     *
     * @return string
     */
    protected function getActionType(QueueInterface $queue): string
    {
        return ('category/' . $queue->getAction());
    }

    /**
     * @param QueueInterface|CategoryQueueInterface $queue
     *
     * @return string
     */
    protected function getSuccessNotificationMessage(QueueInterface $queue): string
    {
        return sprintf(
            'Category with ID "%s" has been successfully %s',
            $queue->getCategoryId(),
            $queue->getAction()
        );
    }

    /**
     * @param QueueInterface|CategoryQueueInterface $queue
     * @param \Exception $ex
     *
     * @return string
     */
    protected function getErrorNotificationMessage(QueueInterface $queue, \Exception $ex): string
    {
        return sprintf(
            'An error occurred while %s category "%s": %s',
            $queue->getAction(),
            $queue->getCategoryId(),
            $ex->getMessage()
        );
    }

    /**
     *
     * @return mixed
     */
    protected function getRepository()
    {
        return $this->categoryQueueRepository;
    }

    /**
     * @param QueueInterface|CategoryQueueInterface $queue
     *
     * @return string
     */
    protected function getPimObjectId(QueueInterface $queue): string
    {
        return $queue->getCategoryId();
    }

    /**
     *
     * @return string
     */
    protected function getNotificationUriPath(): string
    {
        return 'category/update-status';
    }
}
