<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class RelatedProductsLinkerListener
 */
class RelatedProductsLinkerListener implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductLinkInterfaceFactory
     */
    private $linkInterfaceFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * RelatedProductsModifier constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param ProductLinkInterfaceFactory $linkInterfaceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductLinkInterfaceFactory $linkInterfaceFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $pimcoreProduct = $observer->getData('pimcore');
        $product = $observer->getData('product');

        $relatedProductsIds = $pimcoreProduct->getData('related_products');

        if (null === $relatedProductsIds) {
            return;
        }

        $collection = $this->getCollectionOfRelatedProducts($relatedProductsIds);
        $collection->setFlag('has_stock_status_filter', true);

        if (!$collection->getSize()) {
            $product->setProductLinks();

            return;
        }

        $productLinks = $this->linkInterfaceFactory->create();

        $links = [];

        foreach ($collection->getItems() as $item) {
            $links[] = $productLinks
                ->setSku($pimcoreProduct->getData('sku'))
                ->setLinkedProductSku($item->getData('sku'))
                ->setLinkType("related");
        }

        $product->setProductLinks($links);
        $this->productRepository->save($product);
    }

    /**
     * @param $relatedProductsIds
     *
     * @return Collection
     */
    private function getCollectionOfRelatedProducts($relatedProductsIds): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('pimcore_id');
        $collection->addFieldToFilter('pimcore_id', ['in' => $relatedProductsIds]);

        return $collection;
    }
}
