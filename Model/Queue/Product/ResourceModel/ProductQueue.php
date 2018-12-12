<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Product\ResourceModel;

use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ProductQueue
 */
class ProductQueue extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProductQueueInterface::SCHEMA_NAME, ProductQueueInterface::TRANSACTION_ID);
    }
}
