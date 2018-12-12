<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ProductRepository
 */
class ProductRepository extends \Magento\Catalog\Model\ProductRepository implements ProductRepositoryInterface
{
    /**
     * @param int $pimcoreId
     *
     * @param bool $joinOutOfStock
     *
     * @throws NoSuchEntityException
     * @return \Magento\Framework\DataObject
     */
    public function getByPimId($pimcoreId, bool $joinOutOfStock = true): ProductInterface
    {
        $productId = $this->getProductIdByPimId($pimcoreId);

        if (!$productId) {
            throw NoSuchEntityException::singleField('pimcore_id', $pimcoreId);
        }

        $product = $this->productFactory->create();
        $this->resourceModel->load($product, $productId);

        return $product;
    }

    /**
     * @param $pimcoreId
     *
     * @return string
     */
    private function getProductIdByPimId($pimcoreId): string
    {
        $connection = $this->resourceModel->getConnection();
        $table = $connection->getTableName('catalog_product_entity_int');
        $sql = "SELECT entity_id FROM $table where value=?";

        return $connection->fetchOne($sql, $pimcoreId);
    }
}
