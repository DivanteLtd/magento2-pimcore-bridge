<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class QueueOutdated
 */
class QueueOutdated implements ArrayInterface
{
    /**
     * Never remove value
     */
    const NEVER = '0';

    /**
     * Remove AFTER 30 days
     */
    const AFTER_30D = '30';

    /**
     * Remove AFTER 60 days
     */
    const AFTER_60D = '60';

    /**
     * Remove AFTER 90 days
     */
    const AFTER_90D = '90';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => static::NEVER,
                'label' => __('Never'),
            ],
            [
                'value' => static::AFTER_30D,
                'label' => __('After 30 days'),
            ],
            [
                'value' => static::AFTER_60D,
                'label' => __('After 60 days'),
            ],
            [
                'value' => static::AFTER_90D,
                'label' => __('After 90 days'),
            ],
        ];
    }
}
