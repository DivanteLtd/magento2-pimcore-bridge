<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Builder;

use Magento\Framework\DataObject;

/**
 * Interface QueueBuilderInterface
 */
interface QueueBuilderInterface
{
    /**
     * @param DataObject $dto
     * @param string $type
     *
     * @return void
     */
    public function addToQueue(DataObject $dto, string $type);
}
