<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Setup;

use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueue;
use Divante\PimcoreIntegration\Model\Queue\Product\ProductQueue;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createPimcoreProductQueueTable($setup);
        $this->createPimcoreCategoryQueueTable($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createPimcoreProductQueueTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(ProductQueue::SCHEMA_NAME)) {
            $setup->getConnection()->createTable(
                $setup->getConnection()
                    ->newTable($setup->getTable(ProductQueue::SCHEMA_NAME))
                    ->addColumn(
                        ProductQueue::TRANSACTION_ID,
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Transaction ID'
                    )
                    ->addColumn(
                        ProductQueue::PRODUCT_ID,
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Pimcore Product ID'
                    )
                    ->addColumn(
                        ProductQueue::STORE_VIEW_ID,
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Magento store_view id for published product'
                    )
                    ->addColumn(
                        ProductQueue::STATUS,
                        Table::TYPE_INTEGER,
                        1,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Status of import processing'
                    )
                    ->addColumn(
                        ProductQueue::ACTION,
                        Table::TYPE_TEXT,
                        30,
                        [
                            'nullable' => false,
                        ],
                        'Status of import processing'
                    )
                    ->addColumn(
                        ProductQueue::CREATED_AT,
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => false,
                            'default'  => Table::TIMESTAMP_INIT,
                        ],
                        'Date of published product'
                    )
                    ->addColumn(
                        ProductQueue::UPDATED_AT,
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => false,
                            'default'  => Table::TIMESTAMP_INIT_UPDATE,
                        ],
                        'Date of last queue update'
                    )
                    ->addColumn(
                        ProductQueue::FINISHED_AT,
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => true,
                        ],
                        'Date when import was finished'
                    )
                    ->setComment('Pim product queue table')
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createPimcoreCategoryQueueTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists(CategoryQueue::SCHEMA_NAME)) {
            $setup->getConnection()->createTable(
                $setup->getConnection()
                    ->newTable($setup->getTable(CategoryQueue::SCHEMA_NAME))
                    ->addColumn(
                        CategoryQueue::TRANSACTION_ID,
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'identity' => true,
                            'nullable' => false,
                            'primary'  => true,
                            'unsigned' => true,
                        ],
                        'Transaction ID'
                    )
                    ->addColumn(
                        CategoryQueue::CATEGORY_ID,
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Pimcore Category ID'
                    )
                    ->addColumn(
                        CategoryQueue::STORE_VIEW_ID,
                        Table::TYPE_INTEGER,
                        11,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Magento store_view id for published category'
                    )
                    ->addColumn(
                        CategoryQueue::STATUS,
                        Table::TYPE_INTEGER,
                        1,
                        [
                            'nullable' => false,
                            'unsigned' => true,
                        ],
                        'Status of import processing'
                    )
                    ->addColumn(
                        CategoryQueue::ACTION,
                        Table::TYPE_TEXT,
                        30,
                        [
                            'nullable' => false,
                        ],
                        'Status of import processing'
                    )
                    ->addColumn(
                        CategoryQueue::CREATED_AT,
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => false,
                            'default'  => Table::TIMESTAMP_INIT,
                        ],
                        'Date of published product'
                    )
                    ->addColumn(
                        CategoryQueue::UPDATED_AT,
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => false,
                            'default'  => Table::TIMESTAMP_INIT_UPDATE,
                        ],
                        'Date of last queue update'
                    )
                    ->addColumn(
                        CategoryQueue::FINISHED_AT,
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                            'nullable' => true,
                        ],
                        'Date when import was finished'
                    )
                    ->setComment('Pim category queue table')
            );
        }
    }
}
