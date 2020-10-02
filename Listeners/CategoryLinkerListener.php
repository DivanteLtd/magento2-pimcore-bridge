<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\CategoryLinkRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;

/**
 * Class CategoryLinkerListener
 */
class CategoryLinkerListener implements ObserverInterface
{
    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;

    /**
     * @var CategoryLinkRepositoryInterface
     */
    private $categoryLinkRepository;

    /**
     * CategoryModifier constructor.
     *
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param CategoryLinkRepositoryInterface $categoryLinkRepository
     */
    public function __construct(
        CategoryLinkManagementInterface $categoryLinkManagement,
        CategoryLinkRepositoryInterface $categoryLinkRepository
    ) {
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->categoryLinkRepository = $categoryLinkRepository;
    }

    /**
     * @param Observer $observer
     *
     * @throws CouldNotSaveException
     * @throws StateException
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $pimcoreProduct = $observer->getData('pimcore');
        $product = $observer->getData('product');

        $categoryIds = $pimcoreProduct->getData('category_ids') ?? [];

        $catsToUnlink = array_diff($product->getCategoryIds(), $categoryIds);
        $catsToLink = array_diff($categoryIds, $product->getCategoryIds());

        if ($catsToLink) {
            $this->categoryLinkManagement->assignProductToCategories($product->getSku(), $catsToLink);
        }

        if ($catsToUnlink) {
            foreach ($catsToUnlink as $catId) {
                $this->categoryLinkRepository->deleteByIds($catId, $product->getSku());
            }
        }

        $product->setData('category_ids', $categoryIds);
    }
}
