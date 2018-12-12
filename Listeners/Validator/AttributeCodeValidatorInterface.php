<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners\Validator;


/**
 * Interface AttributeCodeValidatorInterface
 */
interface AttributeCodeValidatorInterface
{
    /**
     * @return int
     */
    public function getMaxLength(): int;

    /**
     * @return int
     */
    public function getMinLength(): int;

    /**
     * @param $value
     *
     * @return mixed
     */
    public function isValid($value);
}