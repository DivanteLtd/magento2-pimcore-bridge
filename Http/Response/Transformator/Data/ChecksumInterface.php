<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator\Data;

/**
 * Interface ChecksumInterface
 */
interface ChecksumInterface
{
    /**
     * Checksum data keys
     */
    const DATA_VALUE = 'value';
    const DATA_ALGO = 'algo';

    /**
     * @param string $value
     *
     * @return ChecksumInterface
     */
    public function setValue(string $value): ChecksumInterface;

    /**
     * @return string
     */
    public function getValue(): string;

    /**
     * @param string $algo
     *
     * @return ChecksumInterface
     */
    public function setAlgorithm(string $algo): ChecksumInterface;

    /**
     * @return string
     */
    public function getAlgorithm(): string;
}
