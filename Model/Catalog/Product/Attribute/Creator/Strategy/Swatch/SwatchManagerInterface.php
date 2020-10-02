<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Catalog\Product\Attribute\Creator\Strategy\Swatch;

/**
 * Interface SwatchManagerInterface
 */
interface SwatchManagerInterface
{
    /**
     * Convert base64 to a swatch and returns file name.
     *
     * @param string $base64
     *
     * @return string
     */
    public function createVisualSwatchFromBase64(string $file, string $base64): string;
}
