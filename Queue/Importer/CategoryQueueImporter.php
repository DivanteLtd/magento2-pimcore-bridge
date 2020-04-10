<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Importer;

use Divante\PimcoreIntegration\Api\Queue\CategoryQueueImporterInterface;
use Divante\PimcoreIntegration\Api\Queue\CategoryQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueueRepositoryFactory;
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
 * Class CategoryQueueImporter
 */
class CategoryQueueImporter extends AbstractImporter implements CategoryQueueImporterInterface
{
    /**
     * @var CategoryQueueRepositoryFactory
     */
    private $repositoryFactory;

    /**
     * CategoryQueueImporter constructor.
     *
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param ConfigInterface $config
     * @param ManagerInterface $eventManager
     * @param AbstractValidator $validator
     * @param QueueBuilderInterface $queueBuilder
     * @param QueueFactory $queueFactory
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param CategoryQueueRepositoryFactory $repositoryFactory
     */
    public function __construct(
        BridgeLoggerFactory $bridgeLoggerFactory,
        ConfigInterface $config,
        ManagerInterface $eventManager,
        AbstractValidator $validator,
        QueueBuilderInterface $queueBuilder,
        QueueFactory $queueFactory,
        SearchCriteriaBuilder $criteriaBuilder,
        CategoryQueueRepositoryFactory $repositoryFactory
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
     * Add published category in Pimcore to Magento import queue as a insert/update request
     *
     * @param CategoryQueueInterface $data
     *
     * @return string[]
     */
    public function insertOrUpdate(CategoryQueueInterface $data): array
    {
        return $this->prepareRequest($data, self::ACTION_INSERT_UPDATE);
    }

    /**
     * Add published category in Pimcore to Magento import queue as a delete request
     *
     * @param int $categoryId
     *
     * @throws \Divante\PimcoreIntegration\Exception\InvalidQueueTypeException
     * @return string[]
     */
    public function delete(int $categoryId): array
    {
        /** @var AbstractModel|ProductQueueInterface $queue */
        $queue = $this->queueFactory->create(CategoryQueueInterface::class);
        $queue->setData([
            CategoryQueueInterface::CATEGORY_ID   => $categoryId,
            CategoryQueueInterface::STORE_VIEW_ID => 0,
        ]);

        return $this->prepareRequest($queue, self::ACTION_DELETE);
    }

    /**
     * Add published product in Pimcore to Magento import queue
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
            $this->queueBuilder->addToQueue($dto, CategoryQueueInterface::class);
        }

        return $this->success(
            sprintf('Category %d has been added to queue', $dto[CategoryQueueInterface::CATEGORY_ID])
        );
    }

    /**
     * @param DataObject $dto
     *
     * @return bool
     */
    public function isAlreadyQueued(DataObject $dto): bool
    {
        /** @var CategoryQueueRepositoryInterface $repository */
        $repository = $this->repositoryFactory->create();

        $this->criteriaBuilder->addFilter('category_id', $dto->getData('category_id'));
        $this->criteriaBuilder->addFilter('store_view_id', $dto->getData('store_view_id'));
        $this->criteriaBuilder->addFilter('status', QueueStatusInterface::PENDING);
        $this->criteriaBuilder->addFilter('action', $dto->getData('action'));

        return (bool) $repository->getList($this->criteriaBuilder->create())->getTotalCount();
    }
}
