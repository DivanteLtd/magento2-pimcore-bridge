<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\ResourceModel\Product;

/**
 * Class Gallery
 */
class Gallery extends \Magento\Catalog\Model\ResourceModel\Product\Gallery
{
    /**
     * @param int $pimId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return array
     */
    public function getByPimId(int $pimId)
    {
        $select = $this->getConnection()->select()
            ->from([$this->getMainTableAlias() => $this->getMainTable()])
            ->where(
                'pimcore_id = ?',
                $pimId
            );
        return $this->getConnection()->fetchAll($select);
    }
}
