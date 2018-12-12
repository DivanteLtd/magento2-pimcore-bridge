<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset\Strategy;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\AssetInterface;
use Divante\PimcoreIntegration\Queue\Action\ActionResultInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractorInterface;
use Magento\Framework\DataObject;

/**
 * Interface AssetHandlerStrategyInterface
 */
interface AssetHandlerStrategyInterface
{
    /**
     * @param DataObject|AssetInterface $dto
     * @param TypeMetadataExtractorInterface $metadataExtractor
     * @param AssetQueueInterface|null $queue
     *
     * @return ActionResultInterface
     */
    public function execute(
        DataObject $dto,
        TypeMetadataExtractorInterface $metadataExtractor,
        AssetQueueInterface $queue = null
    ): ActionResultInterface;
}
