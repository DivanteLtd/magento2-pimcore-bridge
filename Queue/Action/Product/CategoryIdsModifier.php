<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Divante\PimcoreIntegration\Api\Queue\CategoryQueueRepositoryInterface;
use Divante\PimcoreIntegration\Queue\Importer\AbstractImporter;
use Divante\PimcoreIntegration\Queue\QueueStatusInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CategoryIdsModifier
 */
class CategoryIdsModifier implements DataModifierInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryQueueRepositoryInterface
     */
    private $categoryQueueRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CategoriesModifier constructor.
     *
     * @param CategoryCollectionFactory $collectionFactory
     * @param CategoryQueueRepositoryInterface $categoryQueueRepository
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryCollectionFactory $collectionFactory,
        CategoryQueueRepositoryInterface $categoryQueueRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->categoryQueueRepository = $categoryQueueRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Product $product
     * @param PimcoreProductInterface $pimcoreProduct
     *
     * @throws LocalizedException
     *
     * @return array
     */
    public function handle(Product $product, PimcoreProductInterface $pimcoreProduct): array
    {
        $pimCatIds = $pimcoreProduct->getData('category_ids') ?? [];

        $catCollection = $this->getMageCatCollection($pimcoreProduct);
        $mageCatIds = $catCollection->getAllIds();
        $pimcoreProduct->setData('category_ids', $mageCatIds);

        if (count($pimCatIds) !== count($mageCatIds)) {
            $existingPimCatIds = [];
            /** @var Category $category */
            foreach ($catCollection as $category) {
                $existingPimCatIds[] = $category->getData('pimcore_id');
            }

            $missingCatPimIds = array_diff($pimCatIds, $existingPimCatIds);

            $queuedCats = $this->categoryQueueRepository->getList(
                $this->getQueuedCatsSearchCriteria($missingCatPimIds, $product)
            );

            if ($queuedCats->getTotalCount() !== count($missingCatPimIds)) {
                throw new LocalizedException(
                    __(
                        'Unable to import product with ID "%1". Related categories are not published yet: %2.',
                        $pimcoreProduct->getData('pimcore_id'),
                        implode(',', $missingCatPimIds)
                    )
                );
            }

            $pimcoreProduct->setData('is_skip', true);
        }

        return [$product, $pimcoreProduct];
    }

    /**
     * @param PimcoreProductInterface $pimcoreProduct
     *
     * @throws LocalizedException
     *
     * @return CategoryCollection
     */
    private function getMageCatCollection(PimcoreProductInterface $pimcoreProduct): CategoryCollection
    {
        $collection = $this->collectionFactory->create();

        $collection->addAttributeToFilter(
            'pimcore_id',
            ['in' => $pimcoreProduct->getData('category_ids')]
        );

        return $collection;
    }

    /**
     * @param array $missingCatIds
     * @param Product $product
     *
     * @return SearchCriteria
     */
    protected function getQueuedCatsSearchCriteria(array $missingCatIds, Product $product): SearchCriteria
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $criteriaBuilder->addFilter('category_id', $missingCatIds, 'in');
        $criteriaBuilder->addFilter('status', QueueStatusInterface::PENDING);
        $criteriaBuilder->addFilter('action', AbstractImporter::ACTION_INSERT_UPDATE);
        $criteriaBuilder->addFilter('store_view_id', $product->getStoreId());

        return $criteriaBuilder->create();
    }
}
