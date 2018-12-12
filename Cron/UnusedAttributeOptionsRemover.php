<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Cron;

use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;

/**
 * Class UnusedAttributeOptionsRemover
 */
class UnusedAttributeOptionsRemover implements CronJobInterface
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Config
     */
    private $config;

    /**
     * UnusedAttributeSetRemover constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param Config $config
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Config $config,
        BridgeLoggerFactory $loggerFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->config = $config;
        $this->logger = $loggerFactory->getLoggerInstance();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute()
    {
        $connection = $this->resourceConnection->getConnection();
        $entityTypeId = $this->config
            ->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getEntityTypeId();

        $optsInUseQuery = $connection->select()
            ->from(['o' => $connection->getTableName('eav_attribute_option')], ['option_id', 'attribute_id'])
            ->joinLeft(['e' => $connection->getTableName('eav_attribute')], 'o.attribute_id=e.attribute_id', '')
            ->where('e.backend_type != ?', 'static')
            ->where('e.entity_type_id = ?', $entityTypeId)
            ->where('e.is_user_defined = ?', 1)
            ->where('e.frontend_input IN (?)', ['select', 'multiselect']);

        $optsInUse = $connection->fetchAll($optsInUseQuery);

        $attrIds = [];
        $optIds = [];

        foreach ($optsInUse as $optInUse) {
            $attrIds[] = (int) $optInUse['attribute_id'];
            $optIds[] = (int) $optInUse['option_id'];
        }

        $usedSelectOptsQuery = $connection->select()
            ->from($connection->getTableName('catalog_product_entity_int'), ['value'])
            ->where('value IN(?)', $optIds)
            ->where('attribute_id IN(?)', $attrIds)
            ->group('attribute_id');

        $usedMultiSelectOptsQuery = $connection->select()
            ->from($connection->getTableName('catalog_product_entity_varchar'), ['value'])
            ->where('value IN(?)', $optIds)
            ->where('attribute_id IN(?)', $attrIds);

        $usedSelectOpts = $connection->fetchCol($usedSelectOptsQuery);
        $usedMultiSelectOpts = $connection->fetchCol($usedMultiSelectOptsQuery);
        $mergedUsedOpts = [];
        $mergedUsedOpts = array_merge($mergedUsedOpts, $usedSelectOpts);

        foreach ($usedMultiSelectOpts as $usedMultiSelectOpt) {
            $mergedUsedOpts = array_merge($mergedUsedOpts, explode(',', $usedMultiSelectOpt));
        }

        $mergedUsedOpts = array_unique($mergedUsedOpts);

        $unusedOptsQuery = $connection->select()
            ->from($connection->getTableName('eav_attribute_option'), ['option_id'])
            ->where('option_id NOT IN(?)', $mergedUsedOpts)
            ->where('attribute_id IN(?)', $attrIds);

        $unusedOpts = $connection->fetchCol($unusedOptsQuery);

        if (!empty($unusedOpts)) {
            $connection->delete(
                $connection->getTableName('eav_attribute_option'),
                sprintf('option_id IN (%s)', implode(',', $unusedOpts))
            );

            $this->logger->info(__("%1 option(s) has been removed.", count($unusedOpts)));
        }
    }
}
