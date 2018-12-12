<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action;

/**
 * Interface ActionResultInterface
 */
interface ActionResultInterface
{
    /**
     * Action results
     */
    const SUCCESS = 'success';
    const SKIPPED = 'skipped';
    const ERROR = 'error';

    /**
     * @return string
     */
    public function getResult(): string;
}
