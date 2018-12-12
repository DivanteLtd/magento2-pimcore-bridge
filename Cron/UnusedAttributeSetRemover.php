<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Cron;

use Divante\PimcoreIntegration\Api\AttributeSetRepositoryInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class UnusedAttributeSetRemover
 */
class UnusedAttributeSetRemover implements CronJobInterface
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
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * UnusedAttributeSetRemover constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param BridgeLoggerFactory $loggerFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AttributeSetRepositoryInterface $attributeSetRepository,
        BridgeLoggerFactory $loggerFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->logger = $loggerFactory->getLoggerInstance();
    }

    /**
     * @throws InputException
     * @throws NoSuchEntityException
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->resourceConnection->getConnection();

        $subQuery = $connection->select()->from(
            $connection->getTableName('catalog_product_entity'),
            ['attribute_set_id']
        )->group('attribute_set_id');

        $query = $connection->select()->from(
            $connection->getTableName('eav_attribute_set'),
            ['attribute_set_id']
        )->where("attribute_set_id NOT IN ($subQuery)")
            ->where('attribute_set_name != ?', 'Default')
            ->where('entity_type_id = ?', 4);

        $results = $connection->fetchCol($query);

        foreach ($results as $attrSetId) {
            $this->attributeSetRepository->deleteById($attrSetId);
            $this->logger->info(__("Attribute set with ID %1 has been removed.", $attrSetId));
        }
    }
}
