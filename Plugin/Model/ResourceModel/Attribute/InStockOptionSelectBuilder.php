<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Plugin\Model\ResourceModel\Attribute;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;

/**
 * Class InStockOptionSelectBuilder
 */
class InStockOptionSelectBuilder
{
    /**
     * CatalogInventory Stock Status Resource Model.
     *
     * @var Status
     */
    private $stockStatusResource;

    /**
     * @param Status $stockStatusResource
     */
    public function __construct(Status $stockStatusResource)
    {
        $this->stockStatusResource = $stockStatusResource;
    }

    /**
     * Add stock status filter to select.
     *
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     *
     * @return Select
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelect(OptionSelectBuilderInterface $subject, Select $select)
    {
        $select->joinInner(
            ['stock' => $this->stockStatusResource->getMainTable()],
            'stock.product_id = entity.entity_id',
            []
        )->where(
            'stock.stock_status IN (?)',
            [StockStatus::STATUS_IN_STOCK, StockStatus::STATUS_OUT_OF_STOCK]
        );

        return $select;
    }
}
