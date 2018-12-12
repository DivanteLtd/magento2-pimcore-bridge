<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CategoryRepository
 */
class CategoryRepository extends \Magento\Catalog\Model\CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * CategoryRepository constructor.
     *
     * @param CategoryFactory $categoryFactory
     * @param Category $categoryResource
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        Category $categoryResource,
        StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($categoryFactory, $categoryResource, $storeManager);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param int $pimId
     * @param int $storeId
     *
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return CategoryInterface
     */
    public function getByPimId(int $pimId, int $storeId = 0): CategoryInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('pimcore_id', ['eq' => $pimId]);
        $collection->addAttributeToSelect('*');
        $collection->setStore($storeId);

        if (!$collection->getSize()) {
            throw NoSuchEntityException::singleField('pimcore_id', $pimId);
        }

        return $collection->getFirstItem();
    }
}
