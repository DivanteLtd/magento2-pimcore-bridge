<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue;

use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Queue\Action\ActionResultInterface;

/**
 * Interface ActionInterface
 */
interface ActionInterface
{
    /**
     * Actions names
     */
    const UPDATE_CATEGORY_ACTION = 'category/insert/update';
    const DELETE_CATEGORY_ACTION = 'category/delete';
    const UPDATE_ASSET_ACTION = 'asset/insert/update';
    const DELETE_ASSET_ACTION = 'asset/delete';
    const UPDATE_PRODUCT_ACTION = 'product/insert/update';
    const DELETE_PRODUCT_ACTION = 'product/delete';

    /**
     * @param QueueInterface $queue
     * @param mixed $data
     *
     * @return ActionResultInterface
     */
    public function execute(QueueInterface $queue, $data = null): ActionResultInterface;
}
