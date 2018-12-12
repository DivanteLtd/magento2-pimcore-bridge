<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CategoryQueueSearchResultsInterface
 */
interface ProductQueueSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get category queue list
     *
     * @return ProductQueueInterface[]
     */
    public function getItems(): array;

    /**
     * Set category queue list
     *
     * @param CategoryQueueInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items): ProductQueueSearchResultsInterface;
}
