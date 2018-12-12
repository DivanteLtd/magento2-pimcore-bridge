<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Category;

use Divante\PimcoreIntegration\Api\Queue\CategoryQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueSearchResultsInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Model\Queue\Category\ResourceModel\CategoryQueue as CategoryQueueResource;
use Divante\PimcoreIntegration\Model\Queue\Category\ResourceModel\CategoryQueue\CollectionFactory;
use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

/**
 * Class CategoryQueueRepository
 */
class CategoryQueueRepository implements CategoryQueueRepositoryInterface
{
    /**
     * @var CategoryQueueFactory
     */
    private $categoryQueueFactory;

    /**
     * @var CategoryQueueResource
     */
    private $categoryQueueResource;

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
     * CategoryQueueRepository constructor.
     *
     * @param CategoryQueueFactory $categoryQueueFactory
     * @param CategoryQueueResource $categoryQueueResource
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        CategoryQueueFactory $categoryQueueFactory,
        CategoryQueueResource $categoryQueueResource,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultFactory $searchResultFactory
    ) {
        $this->categoryQueueFactory = $categoryQueueFactory;
        $this->categoryQueueResource = $categoryQueueResource;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @param int $transactionId
     *
     * @throws NoSuchEntityException
     *
     * @return CategoryQueueInterface
     */
    public function getById(int $transactionId): CategoryQueueInterface
    {
        /** @var CategoryQueueInterface|AbstractModel $categoryQueue */
        $categoryQueue = $this->categoryQueueFactory->create();

        $this->categoryQueueResource->load($categoryQueue, $transactionId);

        if (null === $categoryQueue->getId()) {
            throw new NoSuchEntityException(
                __('CategoryQueue with transaction_id "%1" does not exist.', $transactionId)
            );
        }

        return $categoryQueue;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     *
     * @return CategoryQueueSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var CategoryQueueResource\Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var CategoryQueueSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param CategoryQueueInterface $categoryQueue
     *
     * @throws CouldNotSaveException
     * @return QueueInterface
     */
    public function save(CategoryQueueInterface $categoryQueue): QueueInterface
    {
        try {
            $this->categoryQueueResource->save($categoryQueue);
        } catch (AlreadyExistsException $e) {
            // Fail gracefully
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('An error occurred while saving entity in queue.'));
        }

        return $categoryQueue;
    }

    /**
     * @param CategoryQueueInterface $categoryQueue
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function delete(CategoryQueueInterface $categoryQueue): bool
    {
        try {
            $this->categoryQueueResource->delete($categoryQueue);
        } catch (\Exception $ex) {
            throw new CouldNotDeleteException(
                __('We could not delete queue entity with id "%1"', $categoryQueue->getId())
            );
        }

        return true;
    }
}
