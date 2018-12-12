<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Pimcore;

/**
 * Interface PimcoreProductInterface
 */
interface PimcoreProductInterface
{
    /**
     * Pimcore product attribute set ID code
     */
    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    /**
     * @param array $elements
     *
     * @return PimcoreProductInterface
     */
    public function setElements(array $elements): PimcoreProductInterface;

    /**
     * @param string $key
     * @param string|int|null $index
     *
     * @return mixed
     */
    public function getData($key = '', $index = null);

    /**
     * @param string$key
     * @param mixed $value
     *
     * @return mixed
     */
    public function setData($key, $value = null);

    /**
     * Unset data from the object.
     *
     * @param null|string|array $key
     * @return $this
     */
    public function unsetData($key = null);
}
