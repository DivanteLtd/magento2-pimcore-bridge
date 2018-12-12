<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners;

use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class StockUpdateListener
 */
class StockUpdateListener implements ObserverInterface
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * StockUpdateListener constructor.
     *
     * @param StockRegistryInterface $stockRegistry
     * @param BridgeLoggerFactory $bridgeLoggerFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        BridgeLoggerFactory $bridgeLoggerFactory,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->logger = $bridgeLoggerFactory->getLoggerInstance();
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $pimcoreProduct = $observer->getData('pimcore');
        /** @var Product $product */
        $product = $observer->getData('product');

        try {
            $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
            // We should not set qty if is already set, ERP should be responsible for updating stocks
            if ($stockItem->getQty()) {
                return;
            }

            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $stockItem->setIsInStock(true);
            } else {
                $stockItem->setQty($pimcoreProduct->getData('qty'));
                $stockItem->setIsInStock((bool) $pimcoreProduct->getData('qty'));
            }

            $this->stockItemRepository->save($stockItem);
        } catch (\Exception $ex) {
            $this->logger->critical($ex->getMessage());
        }
    }
}
