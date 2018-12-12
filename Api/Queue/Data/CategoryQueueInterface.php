<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue\Data;

/**
 * Interface CategoryQueueInterface
 */
interface CategoryQueueInterface extends QueueInterface
{
    /**
     * Table name for stored published categories from Pimcore
     */
    const SCHEMA_NAME = 'divante_pimcore_category_queue';

    /**
     * ID of published pimcore category
     */
    const CATEGORY_ID = 'category_id';

    /**
     * @return string|null
     */
    public function getCategoryId();

    /**
     * @param string $categoryId
     *
     * @return $this
     */
    public function setCategoryId(string $categoryId);
}
