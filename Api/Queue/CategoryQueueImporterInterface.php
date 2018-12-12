<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue;

use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;

/**
 * Interface CategoryQueueImporterInterface
 *
 * @api
 */
interface CategoryQueueImporterInterface
{
    /**
     * Add published category in Pimcore to Magento import queue as a insert/update request
     *
     * @param CategoryQueueInterface $data
     *
     * @return array
     */
    public function insertOrUpdate(CategoryQueueInterface $data): array;

    /**
     * Add published category in Pimcore to Magento import queue as a delete request
     *
     * @param int $categoryId
     *
     * @return array
     */
    public function delete(int $categoryId): array;
}
