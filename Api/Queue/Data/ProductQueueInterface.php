<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue\Data;

/**
 * Interface ProductQueueInterface
 */
interface ProductQueueInterface extends QueueInterface
{
    /**
     * Table name for stored published products from Pimcore
     */
    const SCHEMA_NAME = 'divante_pimcore_product_queue';

    /**
     * ID of published pimcore product
     */
    const PRODUCT_ID = 'product_id';

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     *
     * @return void
     */
    public function setProductId(int $productId);
}
