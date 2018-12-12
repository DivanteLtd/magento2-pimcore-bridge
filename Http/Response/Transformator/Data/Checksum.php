<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator\Data;

use Magento\Framework\DataObject;

/**
 * Class Checksum
 */
class Checksum extends DataObject implements ChecksumInterface
{
    /**
     * @param string $value
     *
     * @return ChecksumInterface
     */
    public function setValue(string $value): ChecksumInterface
    {
        return $this->setData(self::DATA_VALUE, $value);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->getData(self::DATA_VALUE);
    }

    /**
     * @param string $algo
     *
     * @return ChecksumInterface
     */
    public function setAlgorithm(string $algo): ChecksumInterface
    {
        return $this->setData(self::DATA_ALGO, $algo);
    }

    /**
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->getData(self::DATA_ALGO);
    }
}
