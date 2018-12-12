<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface ProductRepositoryInterface
 */
interface ProductRepositoryInterface extends \Magento\Catalog\Api\ProductRepositoryInterface
{
    /**
     * @param int $pimcoreId
     * @param bool $joinOutOfStock
     *
     * @throws NoSuchEntityException
     *
     * @return ProductInterface
     */
    public function getByPimId($pimcoreId, bool $joinOutOfStock = true): ProductInterface;
}
