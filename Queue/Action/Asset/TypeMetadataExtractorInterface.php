<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

/**
 * Interface TypeMetadataInterface
 */
interface TypeMetadataExtractorInterface
{
    /**
     * @return string
     */
    public function getEntityType(): string;

    /**
     * @return array
     */
    public function getAssetTypes(): array;

    /**
     * @return bool
     */
    public function isValid(): bool;
}
