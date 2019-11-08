<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset\Strategy;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\AssetInterface;
use Divante\PimcoreIntegration\Logger\BridgeLoggerFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\File;
use Divante\PimcoreIntegration\Queue\Action\Asset\PathResolver;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;

/**
 * Class ReplaceImages
 */
class ReplaceImages implements AssetHandlerStrategyInterface
{

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var File
     */
    private $file;

    /**
     * @var ActionResultFactory
     */
    private $actionResultFactory;

    /**
     * ReplaceImages constructor.
     *
     * @param PathResolver $pathResolver
     * @param ResourceConnection $resource
     * @param File $file
     * @param ActionResultFactory $actionResultFactory
     * @param BridgeLoggerFactory $loggerFactory
     */
    public function __construct(
        PathResolver $pathResolver,
        ResourceConnection $resource,
        File $file,
        ActionResultFactory $actionResultFactory,
        BridgeLoggerFactory $loggerFactory
    ) {
        $this->pathResolver = $pathResolver;
        $this->resource = $resource;
        $this->file = $file;
        $this->actionResultFactory = $actionResultFactory;
        $this->logger = $loggerFactory->getLoggerInstance();
    }

    /**
     * @param DataObject|AssetInterface $dto
     * @param TypeMetadataExtractorInterface $metadataExtractor
     * @param AssetQueueInterface|null $queue
     *
     * @return ActionResultInterface
     */
    public function execute(
        DataObject $dto,
        TypeMetadataExtractorInterface $metadataExtractor,
        AssetQueueInterface $queue = null
    ): ActionResultInterface {

        $this->logger->addInfo('ProductImage', [$dto->getNameWithExt(), $dto->getChecksum()->getValue()]);
        $actionResult = ActionResultInterface::SUCCESS;
        try{
            $this->replaceProductsImage($dto);
            $this->replaceCategoryImage($dto);
            $this->updateEntries($dto);
        }catch (\Exception $ex){
            $this->logger->addCritical($ex->getMessage(), [$ex->getCode(), $ex->getFile(), $ex->getLine(), $ex->getTraceAsString() ]);
            $actionResult = ActionResultInterface::ERROR;
        }
        return $this->actionResultFactory->create(['result' => $actionResult]);
    }

    /**
     * @param DataObject|AssetInterface $dto
     *
     * @return void
     */
    private function replaceProductsImage(DataObject $dto)
    {
        $root = str_replace(
            '/' . $dto->getNameWithExt(),
            '',
            $this->pathResolver->getBaseProductAssetPath($dto->getNameWithExt())
        );

        if (!is_dir($root)) {
            if (!mkdir($root, 0777, true) && !is_dir($root)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $root));
            }
        }

        array_map('unlink', glob($this->pathResolver->getBaseProductAssetPath($dto->getName()) . '.*'));
        file_put_contents(
            $this->pathResolver->getBaseProductAssetPath($dto->getNameWithExt()),
            $dto->getDecodedImage()
        );
    }

    /**
     * @param DataObject|AssetInterface $dto
     *
     * @return void
     */
    private function replaceCategoryImage(DataObject $dto)
    {
        $root = $this->pathResolver->getCategoryMediaRootDir();

        if (!is_dir($root)) {
            if (!mkdir($root, 0777, true) && !is_dir($root)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $root));
            }
        }

        array_map('unlink', glob($this->pathResolver->getCategoryAssetPath($dto->getName()) . '.*'));
        file_put_contents(
            $this->pathResolver->getCategoryAssetPath($dto->getNameWithExt()),
            $dto->getDecodedImage()
        );
    }

    /**
     * @param DataObject|AssetInterface $dto
     *
     * @return void
     */
    private function updateEntries(DataObject $dto)
    {
        $this->updateCategoryEntities($dto);
        $this->updateProductEntities($dto);
    }

    /**
     * @param DataObject|AssetInterface $dto
     *
     * @return void
     */
    private function updateCategoryEntities(DataObject $dto)
    {
        $connection = $this->resource->getConnection();

        $tablesToUpdate = [
            $connection->getTableName('catalog_category_entity_varchar'),
        ];

        foreach ($tablesToUpdate as $table) {
            $connection->query(
                "UPDATE {$table} SET value=? WHERE value LIKE ?",
                [$dto->getNameWithExt(), "%{$dto->getName()}%"]
            );
        }
    }

    /**
     * @param DataObject|AssetInterface $dto
     *
     * @return void
     */
    private function updateProductEntities(DataObject $dto)
    {
        $connection = $this->resource->getConnection();

        $tablesToUpdate = [
            $connection->getTableName('catalog_product_entity_media_gallery'),
            $connection->getTableName('catalog_product_entity_varchar'),
        ];

        foreach ($tablesToUpdate as $table) {
            $filename = sprintf('%s.%s', $this->file->getFilenameWithDispretionPath($dto->getPimId()), $dto->getExt());
            $connection->query(
                "UPDATE {$table} SET value=? WHERE value LIKE ?",
                [$filename, "%{$dto->getName()}%"]
            );
        }
    }
}
