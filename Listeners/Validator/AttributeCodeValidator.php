<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Listeners\Validator;


use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Validator\AbstractValidator;
use Zend_Validate_Exception;

/**
 * Clas AttributeCodeValidator
 */
class AttributeCodeValidator extends AbstractValidator implements AttributeCodeValidatorInterface
{
    /**
     * @var int
     */
    private $minLength = Attribute::ATTRIBUTE_CODE_MIN_LENGTH;

    /**
     * @var int
     */
    private $maxLength = Attribute::ATTRIBUTE_CODE_MAX_LENGTH;

    /**
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    /**
     * @return int
     */
    public function getMinLength(): int
    {
        return $this->minLength;
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value)
    {
        return \Zend_Validate::is(
            trim($value),
            'StringLength',
            ['min' => $this->minLength, 'max' => $this->maxLength]
        );
    }
}