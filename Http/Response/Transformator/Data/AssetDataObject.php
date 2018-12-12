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
 * Class AssetDataObject
 */
class AssetDataObject extends DataObject implements AssetInterface
{
    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return (bool) $this->getData(self::IS_SUCCESS);
    }

    /**
     * @return string
     */
    public function getEncodedImage(): string
    {
        return $this->getData(self::ENCODED_IMAGE);
    }

    /**
     * @return string
     */
    public function getDecodedImage(): string
    {
        return $this->getData(self::DECODED_IMAGE);
    }

    /**
     * @return ChecksumInterface
     */
    public function getChecksum(): ChecksumInterface
    {
        return $this->getData(self::CHECKSUM);
    }

    /**
     * @return string
     */
    public function getMimetype(): string
    {
        return $this->getData(self::MIMETYPE);
    }

    /**
     * @return string
     */
    public function getPimId(): string
    {
        return $this->getData(self::PIM_ID);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @return string
     */
    public function getNameWithExt(): string
    {
        return sprintf('%s.%s', $this->getData(self::NAME), $this->getData(self::EXT));
    }

    /**
     * @return string
     */
    public function getExt(): string
    {
        return $this->getData(self::EXT);
    }
}
