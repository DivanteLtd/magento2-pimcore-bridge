<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Importer;

use Divante\PimcoreIntegration\Api\Queue\AssetQueueImporterInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Exception\InvalidQueueTypeException;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Model\Queue\Asset\AssetQueueRepository;
use Divante\PimcoreIntegration\Model\Queue\Asset\AssetQueueRepositoryFactory;
use Divante\PimcoreIntegration\Queue\Builder\QueueBuilderInterface;
use Divante\PimcoreIntegration\Queue\QueueFactory;
use Divante\PimcoreIntegration\Queue\QueueStatusInterface;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class AssetQueueImporter
 */
class AssetQueueImporter extends AbstractImporter implements AssetQueueImporterInterface
{
    /**
     * @var AssetQueueRepositoryFactory
     */
    private $repositoryFactory;

    /**
     * AssetQueueImporter constructor.
     *
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param ConfigInterface $config
     * @param ManagerInterface $eventManager
     * @param AbstractValidator $validator
     * @param QueueBuilderInterface $queueBuilder
     * @param QueueFactory $queueFactory
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param AssetQueueRepositoryFactory $repositoryFactory
     */
    public function __construct(
        BridgeLoggerFactory $bridgeLoggerFactory,
        ConfigInterface $config,
        ManagerInterface $eventManager,
        AbstractValidator $validator,
        QueueBuilderInterface $queueBuilder,
        QueueFactory $queueFactory,
        SearchCriteriaBuilder $criteriaBuilder,
        AssetQueueRepositoryFactory $repositoryFactory
    ) {
        $this->repositoryFactory = $repositoryFactory;

        parent::__construct(
            $bridgeLoggerFactory,
            $config,
            $eventManager,
            $validator,
            $queueBuilder,
            $queueFactory,
            $criteriaBuilder
        );
    }

    /**
     * Add published asset in Pimcore to Magento import queue as a insert/update request
     *
     * @param AssetQueueInterface $data
     *
     * @return array
     */
    public function insertOrUpdate(AssetQueueInterface $data): array
    {
        return $this->prepareRequest($data, self::ACTION_INSERT_UPDATE);
    }

    /**
     * Add published asset in Pimcore to Magento import queue as a delete request
     *
     * @param int $queueId
     *
     * @throws InvalidQueueTypeException
     * @return array
     */
    public function delete(int $queueId): array
    {
        /** @var AbstractModel|AssetQueueInterface $queue */
        $queue = $this->queueFactory->create(AssetQueueInterface::class);
        $queue->setData([
            AssetQueueInterface::ASSET_ID      => $queueId,
            AssetQueueInterface::STORE_VIEW_ID => 0,
        ]);

        return $this->prepareRequest($queue, self::ACTION_DELETE);
    }

    /**
     * Add published asset in Pimcore to Magento import queue
     *
     * @param DataObject $dto
     *
     * @throws \Zend_Validate_Exception
     * @return array
     */
    protected function addToQueue(DataObject $dto): array
    {
        if (!$this->validator->isValid($dto)) {
            return $this->critical($this->validator->getMessages());
        }

        $dto->setData('status', QueueStatusInterface::PENDING);

        if (!$this->isAlreadyQueued($dto)) {
            $this->queueBuilder->addToQueue($dto, AssetQueueInterface::class);
        }

        return $this->success(
            sprintf('Asset %d has been added to queue', $dto[AssetQueueInterface::ASSET_ID])
        );
    }

    /**
     * @param DataObject $dto
     *
     * @return bool
     */
    public function isAlreadyQueued(DataObject $dto): bool
    {
        /** @var AssetQueueRepository $repository */
        $repository = $this->repositoryFactory->create();

        $this->criteriaBuilder->addFilter('asset_id', $dto->getData('asset_id'));
        $this->criteriaBuilder->addFilter('store_view_id', $dto->getData('store_view_id'));
        $this->criteriaBuilder->addFilter('status', QueueStatusInterface::PENDING);
        $this->criteriaBuilder->addFilter('action', $dto->getData('action'));
        $this->criteriaBuilder->addFilter('asset_type', $dto->getData('asset_type'));
        $this->criteriaBuilder->addFilter('entity_id', $dto->getData('entity_id'));

        return (bool) $repository->getList($this->criteriaBuilder->create())->getTotalCount();
    }
}
