<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\TypeStrategy;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\ProductQueueRepositoryInterface;
use Divante\PimcoreIntegration\Model\Queue\Product\ProductQueue;
use Divante\PimcoreIntegration\Queue\QueueStatusInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class SimpleProductStrategy
 */
class SimpleProductStrategy implements ProductTypeCreationStrategyInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductQueueRepositoryInterface
     */
    private $productQueueRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * SimpleProductStrategy constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductQueueRepositoryInterface $productQueueRepository
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductQueueRepositoryInterface $productQueueRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productQueueRepository = $productQueueRepository;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
    }

    /**
     * @param ProductInterface $product
     *
     * @throws LocalizedException
     * @return ProductInterface
     */
    public function execute(ProductInterface $product): ProductInterface
    {
        $product->setTypeId(Type::TYPE_SIMPLE);

        if ($product->getProductType() !== 'variant') {
            return $product;
        }

        $parentId = $product->getData('parent_id');

        if (!$parentId) {
            throw new LocalizedException(
                __('Variant product with ID "%1", do not have assigned parent or parentId is not set.')
            );
        }

        try {
            $this->productRepository->getByPimId($parentId);
        } catch (NoSuchEntityException $ex) {
            /** @var SearchCriteriaBuilder $criteriaBuilder */
            $criteriaBuilder = $this->criteriaBuilderFactory->create();
            $criteriaBuilder->addFilter(ProductQueue::PRODUCT_ID, $parentId);
            $criteriaBuilder->addFilter(ProductQueue::STATUS, QueueStatusInterface::PENDING);
            $result = $this->productQueueRepository->getList($criteriaBuilder->create());

            if (!$result->getTotalCount()) {
                throw new LocalizedException(
                    __('Unable to import product. Parent product with ID "%1" is not published.', $parentId)
                );
            }

            $product->setIsSkip(true);
        }

        $product->setStatus(Status::STATUS_ENABLED);

        return $product;
    }
}
