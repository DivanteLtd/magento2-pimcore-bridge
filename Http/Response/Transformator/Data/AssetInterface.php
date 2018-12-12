<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator\Data;

/**
 * Interface AssetInterface
 *
 */
interface AssetInterface
{
    /**
     * Data fields keys
     */
    const IS_SUCCESS    = 'is_success';

    const ENCODED_IMAGE = 'encoded_image';

    const DECODED_IMAGE = 'decoded_image';

    const CHECKSUM      = 'checksum';

    const MIMETYPE      = 'mimetype';

    const PIM_ID        = 'pim_id';

    const NAME          = 'name';

    const FILENAME      = 'filename';

    const EXT           = 'ext';

    /**
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * @return string
     */
    public function getEncodedImage(): string;

    /**
     * @return string
     */
    public function getDecodedImage(): string;

    /**
     * @return ChecksumInterface
     */
    public function getChecksum(): ChecksumInterface;

    /**
     * @return string
     */
    public function getMimetype(): string;

    /**
     * @return string
     */
    public function getPimId(): string;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getNameWithExt(): string;

    /**
     * @return string
     */
    public function getExt(): string;

    /**
     * @param $key
     * @param mixed $value
     *
     * @return AssetInterface
     */
    public function setData($key, $value = null);
}
