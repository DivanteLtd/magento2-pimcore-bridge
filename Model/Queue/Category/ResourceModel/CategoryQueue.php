<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Category\ResourceModel;

use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class CategoryQueue
 */
class CategoryQueue extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CategoryQueueInterface::SCHEMA_NAME, CategoryQueueInterface::TRANSACTION_ID);
    }
}
