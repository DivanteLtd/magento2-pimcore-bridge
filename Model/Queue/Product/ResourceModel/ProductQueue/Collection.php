<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Product\ResourceModel\ProductQueue;

use Divante\PimcoreIntegration\Model\Queue\Product\ProductQueue;
use Divante\PimcoreIntegration\Model\Queue\Product\ResourceModel\ProductQueue as ResourceProductQueue;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProductQueue::class, ResourceProductQueue::class);
    }
}
