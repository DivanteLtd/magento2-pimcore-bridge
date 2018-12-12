<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\System\Config;

/**
 * Class UnusedAttributeSetFrequency
 */
class UnusedAttributeSetFrequency extends AbstractFrequencyConfig
{
    /**
     * @return string
     */
    protected function getCronStringPath(): string
    {
        return 'crontab/divante_pimcore_integration/jobs/remove_unused_attribute_sets/schedule/cron_expr';
    }

    /**
     * @return string
     */
    protected function getCronModelPath(): string
    {
        return 'crontab/divante_pimcore_integration/jobs/remove_unused_attribute_sets/run/model';
    }

    /**
     * @return string
     */
    protected function getTimeConfigValuePath(): string
    {
        return 'groups/attribute_sets/fields/time/value';
    }

    /**
     * @return string
     */
    protected function getFrequencyConfigValuePath(): string
    {
        return 'groups/attribute_sets/fields/frequency/value';
    }
}
