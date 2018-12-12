<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Category;

/**
 * Class AdditionalDataResource
 */
class AdditionalDataResource
{
    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return [
            'is_anchor' => 1,
        ];
    }
}