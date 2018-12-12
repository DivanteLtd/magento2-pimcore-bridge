<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

use Divante\PimcoreIntegration\Http\Response\Transformator\Data\ChecksumInterface;

/**
 * Interface ChecksumValidatorInterface
 */
interface ChecksumValidatorInterface
{
    /**
     * Validates checksum against image data
     *
     * @param ChecksumInterface $checksum
     * @param string $imageData
     *
     * @return bool
     */
    public function isValid(ChecksumInterface $checksum, string $imageData): bool;
}
