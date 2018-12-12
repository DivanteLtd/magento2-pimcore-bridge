<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\AssetQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\ProductQueueRepositoryInterface;
use Divante\PimcoreIntegration\Exception\InvalidTypeException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class QueueRepositoryFactory
 */
class QueueRepositoryFactory
{
    /**
     * Product queue repository type
     */
    const TYPE_PRODUCT = 'product_queue_repository';

    /**
     * Category queue repository type
     */
    const TYPE_CATEGORY = 'category_queue_repository';

    /**
     * Asset queue repository type
     */
    const TYPE_ASSET = 'asset_queue_repository';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * QueueRepositoryFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $type
     *
     * @throws InvalidTypeException
     *
     * @return mixed
     */
    public function create(string $type)
    {
        $repository = null;

        switch ($type) {
            case self::TYPE_PRODUCT:
                $repository = $this->objectManager->create(ProductQueueRepositoryInterface::class);
                break;
            case self::TYPE_CATEGORY:
                $repository = $this->objectManager->create(CategoryRepositoryInterface::class);
                break;
            case self::TYPE_ASSET:
                $repository = $this->objectManager->create(AssetQueueRepositoryInterface::class);
                break;
            default:
                throw new InvalidTypeException(__('Invalid repository type "%1".', $type));
        }

        return $repository;
    }
}
