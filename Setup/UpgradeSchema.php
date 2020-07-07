<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Setup;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->createPimcoreAssetQueueTable($setup);
            $this->addEntityIdColumnToPimcoreAssetTable($setup);
            $this->addPimcoreIdColumnToMediaGallery($setup);
            $this->addChecksumColToEavAttributeSetTable($setup);
            $this->addPimIdToEavOption($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable(AssetQueueInterface::SCHEMA_NAME),
                'asset_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                ]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     *
     */
    private function createPimcoreAssetQueueTable(SchemaSetupInterface $setup)
    {
        if ($setup->tableExists(AssetQueueInterface::SCHEMA_NAME)) {
            return;
        }

        $setup->getConnection()->createTable(
            $setup->getConnection()
                ->newTable($setup->getTable(AssetQueueInterface::SCHEMA_NAME))
                ->addColumn(
                    AssetQueueInterface::TRANSACTION_ID,
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
                    AssetQueueInterface::ASSET_ID,
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Pimcore Asset ID'
                )
                ->addColumn(
                    AssetQueueInterface::STORE_VIEW_ID,
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Magento store_view id for published asset'
                )
                ->addColumn(
                    AssetQueueInterface::STATUS,
                    Table::TYPE_INTEGER,
                    1,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Status of import processing'
                )
                ->addColumn(
                    AssetQueueInterface::ACTION,
                    Table::TYPE_TEXT,
                    30,
                    [
                        'nullable' => false,
                    ],
                    'Status of import processing'
                )
                ->addColumn(
                    AssetQueueInterface::ASSET_TYPE,
                    Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => true,
                    ],
                    'Type of asset to import, eg. product, category, etc.'
                )
                ->addColumn(
                    AssetQueueInterface::CREATED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default'  => Table::TIMESTAMP_INIT,
                    ],
                    'Date of published asset'
                )
                ->addColumn(
                    AssetQueueInterface::UPDATED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default'  => Table::TIMESTAMP_INIT_UPDATE,
                    ],
                    'Date of last queue update'
                )
                ->addColumn(
                    AssetQueueInterface::FINISHED_AT,
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => true,
                    ],
                    'Date when import was finished'
                )->setComment('Pim asset queue table')
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addEntityIdColumnToPimcoreAssetTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(AssetQueueInterface::SCHEMA_NAME),
            AssetQueueInterface::ASSET_TARGET_ENTITY_ID,
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'Target entity id',
                'after'    => AssetQueueInterface::ASSET_TYPE,
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addPimcoreIdColumnToMediaGallery(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(Gallery::GALLERY_TABLE),
            'pimcore_id',
            [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment'  => 'Pimcore object id',
                'after'    => 'attribute_id',
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addChecksumColToEavAttributeSetTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('eav_attribute_set'),
            'checksum',
            [
                'type'     => Table::TYPE_TEXT,
                'comment'  => 'Checksum value of all attributes in set',
                'nullable' => false,
                'default'  => '',
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return void
     */
    private function addPimIdToEavOption(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('eav_attribute_option');
        $setup->getConnection()->addColumn(
            $tableName,
            'pimcore_id',
            [
                'type'     => Table::TYPE_TEXT,
                'comment'  => 'Pimcore option id',
                'nullable' => true,
                'length'   => '255',
            ]
        );

        $setup->getConnection()->addIndex(
            $tableName,
            $setup->getIdxName($tableName, ['pimcore_id']),
            'pimcore_id'
        );

        $setup->getConnection()->addIndex(
            $setup->getTable('eav_attribute_option_value'),
            $setup->getIdxName($tableName, ['option_id', 'store_id']),
            ['option_id', 'store_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
