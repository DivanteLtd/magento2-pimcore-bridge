<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\PathResolver;
use Divante\PimcoreIntegration\Queue\ActionInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Class DeleteAssetAction
 */
class DeleteAssetAction implements ActionInterface
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $suffix = '_pim';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var ActionResultFactory
     */
    private $actionResultFactory;

    /**
     * DeleteAssetAction constructor.
     *
     * @param ResourceConnection $resource
     * @param PathResolver $pathResolver
     * @param ActionResultFactory $actionResultFactory
     */
    public function __construct(
        ResourceConnection $resource,
        PathResolver $pathResolver,
        ActionResultFactory $actionResultFactory
    ) {
        $this->resource = $resource;
        $this->pathResolver = $pathResolver;
        $this->actionResultFactory = $actionResultFactory;
    }

    /**
     * @param QueueInterface|AssetQueueInterface $queue
     * @param null $data
     *
     * @return ActionResultInterface
     */
    public function execute(QueueInterface $queue, $data = null): ActionResultInterface
    {
        $this->filename = $this->createFilename($queue->getAssetId());

        $this->removeEntriesFromDb();
        $this->removeFiles();

        return $this->actionResultFactory->create(['result' => ActionResultInterface::SUCCESS]);
    }

    /**
     * @param string $base
     *
     * @return string
     */
    private function createFilename(string $base = ''): string
    {
        return sprintf('%s%s', $base, $this->suffix);
    }

    /**
     * @return void
     */
    private function removeEntriesFromDb()
    {
        $connection = $this->resource->getConnection();

        $tablesToClean = [
            $connection->getTableName('catalog_product_entity_media_gallery'),
            $connection->getTableName('catalog_product_entity_varchar'),
            $connection->getTableName('catalog_category_entity_varchar'),
        ];

        foreach ($tablesToClean as $table) {
            $connection->query("DELETE FROM {$table} WHERE value LIKE ?", ["%{$this->filename}%"]);
        }
    }

    /**
     * @return void
     */
    private function removeFiles()
    {
        $productMediaPath = $this->pathResolver->getBaseProductAssetPath($this->filename);
        $categoryMediaPath = $this->pathResolver->getCategoryAssetPath($this->filename);

        array_map('unlink', glob($productMediaPath . '.*'));
        array_map('unlink', glob($categoryMediaPath . '.*'));
    }
}
