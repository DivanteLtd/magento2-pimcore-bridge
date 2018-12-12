<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Cron;

use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Model\Config\Source\QueueOutdated;
use Divante\PimcoreIntegration\System\ConfigInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Class OldQueueEntriesRemover
 */
class OldQueueEntriesRemover implements CronJobInterface
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var array
     */
    private $tablesToClean = [
        'product'  => 'divante_pimcore_product_queue',
        'asset'    => 'divante_pimcore_asset_queue',
        'category' => 'divante_pimcore_category_queue',
    ];

    /**
     * OldQueueEntriesRemover constructor.
     *
     * @param ResourceConnection $resource
     * @param ConfigInterface $config
     * @param BridgeLoggerFactory $loggerFactory
     */
    public function __construct(
        ResourceConnection $resource,
        ConfigInterface $config,
        BridgeLoggerFactory $loggerFactory
    ) {
        $this->resource = $resource;
        $this->config = $config;
        $this->logger = $loggerFactory->getLoggerInstance();
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function execute()
    {
        $outdatedValue = $this->config->getQueueOutdatedValue();

        if ($outdatedValue === QueueOutdated::NEVER) {
            return;
        }

        $datetime = new \DateTime();
        $interval = sprintf('P%sD', $outdatedValue);
        $datetime->sub(new \DateInterval($interval));

        $limitDateTime = $datetime->format('Y-m-d H:i:s');

        $connection = $this->resource->getConnection();

        foreach ($this->tablesToClean as $type => $table) {
            $deleted = $connection->delete(
                $connection->getTableName($table),
                sprintf('finished_at < \'%s\'', $limitDateTime)
            );

            if ($deleted) {
                $this->logger->info(sprintf('%s outdated %s queue entries have been removed', $deleted, $type));
            }
        }
    }
}
