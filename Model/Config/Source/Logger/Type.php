<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Config\Source\Logger;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 */
class Type implements ArrayInterface
{

    /**
     * Stream logger log to files
     */
    const LOGGER_TYPE_STREAM = 0;

    /**
     * Handle logs to Graylog
     */
    const LOGGER_TYPE_GRAYLOG = 1;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => static::LOGGER_TYPE_STREAM,
                'label' => __('Stream'),
            ],
            [
                'value' => static::LOGGER_TYPE_GRAYLOG,
                'label' => __('Graylog'),
            ],
        ];
    }
}
