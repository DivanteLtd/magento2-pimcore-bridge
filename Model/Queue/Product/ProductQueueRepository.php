<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Product;

use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Api\Queue\ProductQueueRepositoryInterface;
use Divante\PimcoreIntegration\Model\Queue\Product\ResourceModel\ProductQueue as ProductQueueResource;
use Divante\PimcoreIntegration\Model\Queue\Product\ResourceModel\ProductQueue\CollectionFactory;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class ProductQueueRepository
 */
class ProductQueueRepository implements ProductQueueRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ProductQueueFactory
     */
    private $productQueueFactory;

    /**
     * @var ProductQueueResource
     */
    private $productQueueResource;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * ProductQueueRepository constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ProductQueueFactory $productQueueFactory
     * @param ProductQueueResource $productQueueResource
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ProductQueueFactory $productQueueFactory,
        ProductQueueResource $productQueueResource,
        SearchResultFactory $searchResultFactory
    ) {

        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->productQueueFactory = $productQueueFactory;
        $this->productQueueResource = $productQueueResource;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * @param int $productId
     *
     * @return ProductQueueInterface
     */
    public function getById(int $productId): ProductQueueInterface
    {
        /** @var ProductQueue $productQueue */
        $productQueue = $this->productQueueFactory->create();

        $this->productQueueResource->load($productQueue, $productId);

        return $productQueue;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param ProductQueueInterface $productQueue
     *
     * @throws CouldNotSaveException
     * @return QueueInterface
     */
    public function save(ProductQueueInterface $productQueue): QueueInterface
    {
        try {
            /** @var ProductQueue $productQueue */
            $this->productQueueResource->save($productQueue);
        } catch (AlreadyExistsException $e) {
            // Fail gracefully
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('An error occurred while saving entity in queue.'));
        }

        return $productQueue;
    }

    /**
     * @param ProductQueueInterface $productQueue
     *
     * @throws CouldNotDeleteException
     *
     * @return bool
     */
    public function delete(ProductQueueInterface $productQueue): bool
    {
        try {
            $this->productQueueResource->delete($productQueue);
        } catch (\Exception $ex) {
            throw new CouldNotDeleteException(
                __('We could not delete queue entity with id "%1"', $productQueue->getId())
            );
        }

        return true;
    }
}
