<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueSearchResultsInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Interface AssetQueueRepositoryInterface
 */
interface AssetQueueRepositoryInterface
{
    /**
     * @param int $transactionId
     *
     * @return AssetQueueInterface
     */
    public function getById(int $transactionId): AssetQueueInterface;

    /**
     * @param AssetQueueInterface $assetQueue
     *
     * @return QueueInterface
     */
    public function save(AssetQueueInterface $assetQueue): QueueInterface;

    /**
     * @param SearchCriteriaInterface $criteria
     *
     * @return AssetQueueSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param AssetQueueInterface $assetQueue
     *
     * @return bool
     */
    public function delete(AssetQueueInterface $assetQueue): bool;
}
