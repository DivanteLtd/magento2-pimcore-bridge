<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Asset;

use Divante\PimcoreIntegration\Api\Queue\AssetQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueSearchResultsInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Model\Queue\Asset\ResourceModel\AssetQueue as AssetQueueResource;
use Divante\PimcoreIntegration\Model\Queue\Asset\ResourceModel\AssetQueue\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

/**
 * Class AssetQueueRepository
 */
class AssetQueueRepository implements AssetQueueRepositoryInterface
{
    /**
     * @var AssetQueueFactory
     */
    private $assetQueueFactory;

    /**
     * @var AssetQueueResource
     */
    private $assetQueueResource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * AssetQueueRepository constructor.
     *
     * @param AssetQueueFactory $assetQueueFactory
     * @param AssetQueueResource $assetQueueResource
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        AssetQueueFactory $assetQueueFactory,
        AssetQueueResource $assetQueueResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultFactory $searchResultFactory
    ) {
        $this->assetQueueFactory = $assetQueueFactory;
        $this->assetQueueResource = $assetQueueResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @param int $transactionId
     *
     * @throws NoSuchEntityException
     *
     * @return AssetQueueInterface
     */
    public function getById(int $transactionId): AssetQueueInterface
    {
        /** @var AssetQueueInterface|AbstractModel $assetQueue */
        $assetQueue = $this->assetQueueFactory->create();

        $this->assetQueueResource->load($assetQueue, $transactionId);

        if (null === $assetQueue->getId()) {
            throw new NoSuchEntityException(
                __('AssetQueue with transaction_id "%1" does not exist.', $transactionId)
            );
        }

        return $assetQueue;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     *
     * @return AssetQueueSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var AssetQueueResource\Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var AssetQueueSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param AssetQueueInterface|AbstractModel $assetQueue
     *
     * @throws CouldNotSaveException
     * @return QueueInterface
     */
    public function save(AssetQueueInterface $assetQueue): QueueInterface
    {
        try {
            $this->assetQueueResource->save($assetQueue);
        } catch (AlreadyExistsException $e) {
            // Fail gracefully
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('An error occurred while saving entity in queue.'));
        }

        return $assetQueue;
    }

    /**
     * @param AssetQueueInterface $assetQueue
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function delete(AssetQueueInterface $assetQueue): bool
    {
        try {
            $this->assetQueueResource->delete($assetQueue);
        } catch (\Exception $ex) {
            throw new CouldNotDeleteException(
                __('We could not delete queue entity with id "%1"', $assetQueue->getId())
            );
        }

        return true;
    }
}
