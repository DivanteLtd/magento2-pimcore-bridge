<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Product;

use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Magento\Framework\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * Class ProductQueueValidator
 */
class ProductQueueValidator extends AbstractValidator
{
    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     *
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();

        if (!($value instanceof ProductQueueInterface)) {
            $this->_addMessages([
                sprintf("Invalid object type, expected '%s'", ProductQueueInterface::class),
            ]);

            return false;
        }

        if (null === $value->getProductId()) {
            $this->_addMessages(["Field 'productId' is required."]);
        }

        if (null === $value->getStoreViewId()) {
            $this->_addMessages(["Field 'store' is required."]);
        }

        return $this->hasMessages();
    }
}
