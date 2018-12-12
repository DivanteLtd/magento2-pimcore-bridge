<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;

/**
 * Interface AssetQueueImporterInterface
 *
 * @api
 */
interface AssetQueueImporterInterface
{
    /**
     * Add published asset in Pimcore to Magento import queue as a insert/update request
     *
     * @param AssetQueueInterface $data
     *
     * @return array
     */
    public function insertOrUpdate(AssetQueueInterface $data): array;

    /**
     * Add published asset in Pimcore to Magento import queue as a delete request
     *
     * @param int $assetId
     *
     * @return array
     */
    public function delete(int $assetId): array;
}
