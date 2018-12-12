<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Cron;

use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductRepositoryFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductPublisher
 */
class ProductPublisher implements CronJobInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var ProductRepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * ProductPublisher constructor.
     *
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param ProductRepositoryFactory $repositoryFactory
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     */
    public function __construct(
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        ProductRepositoryFactory $repositoryFactory,
        StoreManagerInterface $storeManager,
        ConfigInterface $config
    ) {
        $this->storeManager = $storeManager;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->config = $config;
    }

    /**
     * Publish products
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->config->getIsProductPublishActive()) {
            return;
        }

        foreach ($this->storeManager->getStores(true) as $store) {
            $this->storeManager->setCurrentStore($store->getId());

            $this->enableCommonProducts();
            $this->enableConfigurableProducts();
        }
    }

    /**
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     *
     * @return void
     */
    private function enableCommonProducts()
    {
        $criteriaBuilder = $this->getCommonProductsCriteria();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->repositoryFactory->create();
        $products = $productRepository->getList($criteriaBuilder->create())->getItems();

        $this->enableProducts($products, $productRepository);
    }

    /**
     * @return SearchCriteriaBuilder
     */
    private function getCommonProductsCriteria(): SearchCriteriaBuilder
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();

        $criteriaBuilder->addFilter('quantity_and_stock_status', '1');
        $criteriaBuilder->addFilter('price', 0, 'gt');
        $criteriaBuilder->addFilter('status', Status::STATUS_DISABLED);
        $criteriaBuilder->addFilter('is_active_in_pim', 1);

        return $criteriaBuilder;
    }

    /**
     * @param array $products
     * @param ProductRepositoryInterface $productRepository
     *
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     * @return void
     */
    private function enableProducts(array $products, ProductRepositoryInterface $productRepository)
    {
        foreach ($products as $product) {
            if (empty($product->getCategoryIds()) || \in_array($product->getImage(), [null, 'no_selection', ''])) {
                continue;
            }

            $product->setStatus(Status::STATUS_ENABLED);
            $productRepository->save($product);
        }
    }

    /**
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     *
     * @return void
     */
    private function enableConfigurableProducts()
    {
        $criteriaBuilder = $this->getConfigurableProductsCriteria();

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->repositoryFactory->create();
        $products = $productRepository->getList($criteriaBuilder->create())->getItems();

        $this->enableProducts($products, $productRepository);
    }

    /**
     *
     * @return SearchCriteriaBuilder
     */
    private function getConfigurableProductsCriteria(): SearchCriteriaBuilder
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();

        $criteriaBuilder->addFilter('type_id', Configurable::TYPE_CODE);
        $criteriaBuilder->addFilter('quantity_and_stock_status', '1');
        $criteriaBuilder->addFilter('is_active_in_pim', 1);
        $criteriaBuilder->addFilter('status', Status::STATUS_DISABLED);

        return $criteriaBuilder;
    }
}
