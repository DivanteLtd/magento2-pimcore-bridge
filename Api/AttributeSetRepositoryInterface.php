<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api;

use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface AttributeSetRepositoryInterface
 */
interface AttributeSetRepositoryInterface extends \Magento\Catalog\Api\AttributeSetRepositoryInterface
{
    /**
     * @param string $checksum
     *
     * @throws NoSuchEntityException
     *
     * @return AttributeSetInterface
     */
    public function getByChecksum(string $checksum): AttributeSetInterface;
}
