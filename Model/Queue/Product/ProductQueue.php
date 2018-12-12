<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Product;

use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Divante\PimcoreIntegration\Model\AbstractQueue;
use Divante\PimcoreIntegration\Model\Queue\Product\ResourceModel\ProductQueue as ResourceProductQueue;

/**
 * Class ProductQueue
 */
class ProductQueue extends AbstractQueue implements ProductQueueInterface
{
    /**
     * Queue type value
     */
    const TYPE = 'product';

    /**
     * @param int $productId
     *
     * @return void
     */
    public function setProductId(int $productId)
    {
        $this->setData(static::PRODUCT_ID, $productId);
    }

    /**
     * @return int|null
     */
    public function getPimcoreId()
    {
        return $this->getProductId();
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(static::PRODUCT_ID);
    }

    /**
     * @return string
     */
    public function getQueueType(): string
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceProductQueue::class);
    }
}
