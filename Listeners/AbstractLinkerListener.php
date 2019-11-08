<?php


namespace Divante\PimcoreIntegration\Listeners;


use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;


abstract class AbstractLinkerListener implements ObserverInterface
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
     * @param ProductRepositoryInterface $productRepository
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
     */
    abstract public function execute(Observer $observer);

    /**
     * @param $linkedPimcoreProductsIds
     *
     * @return Collection
     */
    protected function getCollectionOfRelatedProducts($linkedPimcoreProductsIds): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('pimcore_id');
        $collection->addFieldToFilter('pimcore_id', ['in' => $linkedPimcoreProductsIds]);

        return $collection;
    }


    /**
     * @param $pimcoreProduct
     * @param \Magento\Catalog\Model\Product $product
     * @param string $LinkType
     * @param string $pimcoreField
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function setNewProductLinks($pimcoreProduct, $product,  string $LinkType, string $pimcoreField){

        $relatedProductsIds = $pimcoreProduct->getData($pimcoreField);

        if (null === $relatedProductsIds) {
            return;
        }

        $collection = $this->getCollectionOfRelatedProducts($relatedProductsIds);
        $collection->setFlag('has_stock_status_filter', true);

        // there are no links for the specific link type
        // clear all old links
        if (!$collection->getSize()) {
            $links =  $product->getProductLinks();
            $newLinkList = [];
            foreach ($links as $link){
                if($link->getLinkType() !== $LinkType){
                    $newLinkList[] = $link;
                }
            }
            $product->setProductLinks($newLinkList);
            $this->productRepository->save($product);
            return;
        }


        // keep onyl old links form different type
        $oldLinkList =  $product->getProductLinks();
        $links = [];
        foreach ($oldLinkList as $link){
            if($link->getLinkType() !== $LinkType){
                $links[] = $link;
            }
        }

        foreach ($collection->getItems() as $item) {
            $productLinks = $this->linkInterfaceFactory->create();
            $links[] = $productLinks
                ->setSku($pimcoreProduct->getData('sku'))
                ->setLinkedProductSku($item->getData('sku'))
                ->setLinkType($LinkType);
        }

        $product->setProductLinks($links);
        $this->productRepository->save($product);
    }
}