<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

/**
 * Interface FileInterface
 */
interface FileInterface
{
    /**
     * @param string $base
     *
     * @return string
     */
    public function getFilename(string $base): string;
}
