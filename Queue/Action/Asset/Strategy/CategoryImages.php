<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset\Strategy;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\CategoryQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\AssetInterface;
use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueue;
use Divante\PimcoreIntegration\Model\Queue\Category\CategoryQueueFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultFactory;
use Divante\PimcoreIntegration\Queue\Action\ActionResultInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\PathResolver;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractorInterface;
use Divante\PimcoreIntegration\Queue\Importer\AbstractImporter;
use Divante\PimcoreIntegration\Queue\QueueStatusInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CategoryImages
 */
class CategoryImages implements AssetHandlerStrategyInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryQueueRepositoryInterface
     */
    private $categoryQueueRepository;

    /**
     * @var ActionResultFactory
     */
    private $actionResultFactory;

    /**
     * @var CategoryQueueFactory
     */
    private $categoryQueueFactory;

    /**
     * @var AbstractImporter
     */
    private $queueImporter;

    /**
     * CategoryImages constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     * @param PathResolver $pathResolver
     * @param StoreManagerInterface $storeManager
     * @param CategoryQueueRepositoryInterface $categoryQueueRepository
     * @param ActionResultFactory $actionResultFactory
     * @param CategoryQueueFactory $categoryQueueFactory
     * @param AbstractImporter $queueImporter
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        PathResolver $pathResolver,
        StoreManagerInterface $storeManager,
        CategoryQueueRepositoryInterface $categoryQueueRepository,
        ActionResultFactory $actionResultFactory,
        CategoryQueueFactory $categoryQueueFactory,
        AbstractImporter $queueImporter
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->pathResolver = $pathResolver;
        $this->storeManager = $storeManager;
        $this->categoryQueueRepository = $categoryQueueRepository;
        $this->actionResultFactory = $actionResultFactory;
        $this->categoryQueueFactory = $categoryQueueFactory;
        $this->queueImporter = $queueImporter;
    }

    /**
     * @param AssetQueueInterface $queue
     * @param DataObject|AssetInterface $dto
     * @param TypeMetadataExtractorInterface $metadataExtractor
     *
     * @throws LocalizedException
     *
     * @return ActionResultInterface
     */
    public function execute(
        DataObject $dto,
        TypeMetadataExtractorInterface $metadataExtractor,
        AssetQueueInterface $queue = null
    ): ActionResultInterface {
        /** @var Category $category */
        if (null === $queue) {
            throw new LocalizedException(__('Queue object is required for this strategy.'));
        }

        try {
            $category = $this->categoryRepository->getByPimId($queue->getTargetEntityId(), $queue->getStoreViewId());
        } catch (NoSuchEntityException $e) {
            if ($this->queueImporter->isAlreadyQueued($this->createCategoryQueue($queue))) {
                return $this->actionResultFactory->create(['result' => ActionResultInterface::SKIPPED]);
            }

            throw new LocalizedException(
                __(
                    'Unable to import asset. Related category with ID "%1" is not published yet.',
                    $queue->getTargetEntityId()
                )
            );
        }

        $this->storeManager->setCurrentStore($queue->getStoreViewId());

        $category->setImage(
            $dto->getNameWithExt(),
            $metadataExtractor->getAssetTypes(),
            true,
            false
        );

        $rootDir = $this->pathResolver->getCategoryMediaRootDir();

        if (!file_exists($rootDir)) {
            mkdir($rootDir, 0777, true);
        }

        file_put_contents($this->pathResolver->getCategoryAssetPath($dto->getNameWithExt()), $dto->getDecodedImage());

        $this->categoryRepository->save($category);

        return $this->actionResultFactory->create(['result' => ActionResultInterface::SUCCESS]);
    }

    /**
     * @param AssetQueueInterface $queue
     *
     * @return CategoryQueue
     */
    protected function createCategoryQueue(AssetQueueInterface $queue): CategoryQueue
    {
        $categoryQueue = $this->categoryQueueFactory->create();
        $categoryQueue->setStatus(QueueStatusInterface::PENDING);
        $categoryQueue->setAction($queue->getAction());
        $categoryQueue->setStoreViewId($queue->getStoreViewId());
        $categoryQueue->setCategoryId($queue->getTargetEntityId());

        return $categoryQueue;
    }
}
