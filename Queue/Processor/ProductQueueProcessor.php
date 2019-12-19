<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Processor;

use Divante\PimcoreIntegration\Api\Queue\AssetQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Api\Queue\ProductQueueRepositoryInterface;
use Divante\PimcoreIntegration\Http\Notification\PimcoreNotificatorInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultFactory;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\App\Emulation;

/**
 * Class ProductQueueProcessor
 */
class ProductQueueProcessor extends AbstractQueueProcessor
{
    /**
     * @var ProductQueueRepositoryInterface
     */
    private $productQueueRepository;

    /**
     * ProductQueueProcessor constructor.
     *
     * @param ActionFactory $actionFactory
     * @param ConfigInterface $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param ManagerInterface $eventManager
     * @param PimcoreNotificatorInterface $notificator
     * @param ProductQueueRepositoryInterface $productQueueRepository
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
        ProductQueueRepositoryInterface $productQueueRepository,
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

        $this->productQueueRepository = $productQueueRepository;
    }

    /**
     *
     * @return int
     */
    protected function getPageSize(): int
    {
        return $this->config->getProductQueueProcess();
    }

    /**
     * @param QueueInterface $queue
     *
     * @return string
     */
    protected function getActionType(QueueInterface $queue): string
    {
        return ('product/' . $queue->getAction());
    }

    /**
     * @param $queue
     *
     * @return string
     */
    protected function getSuccessNotificationMessage(QueueInterface $queue): string
    {
        return sprintf(
            'Product with ID "%s" has been successfully %s',
            $queue->getProductId(),
            $queue->getAction()
        );
    }

    /**
     * @param QueueInterface $queue
     * @param \Exception $ex
     *
     * @return string
     */
    protected function getErrorNotificationMessage(QueueInterface $queue, \Exception $ex): string
    {
        return sprintf(
            'An error occurred while %s product "%s": %s',
            $queue->getAction(),
            $queue->getProductId(),
            $ex->getMessage()
        );
    }

    /**
     *
     * @return mixed
     */
    protected function getRepository()
    {
        return $this->productQueueRepository;
    }

    /**
     * @param QueueInterface $queue
     *
     * @return string
     */
    protected function getPimObjectId(QueueInterface $queue): string
    {
        return $queue->getProductId();
    }

    /**
     *
     * @return string
     */
    protected function getNotificationUriPath(): string
    {
        return 'product/update-status';
    }
}
