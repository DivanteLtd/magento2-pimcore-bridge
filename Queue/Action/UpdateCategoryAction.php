<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action;

use Divante\PimcoreIntegration\Api\CategoryRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\AssetQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\CategoryQueueRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\CategoryQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Api\RequestClientInterface;
use Divante\PimcoreIntegration\Http\Response\Transformator\ResponseTransformatorInterface;
use Divante\PimcoreIntegration\Http\UrlBuilderInterface;
use Divante\PimcoreIntegration\Model\Queue\Asset\AssetQueueFactory;
use Divante\PimcoreIntegration\Queue\Action\Category\AdditionalDataResource;
use Divante\PimcoreIntegration\Queue\ActionInterface;
use Divante\PimcoreIntegration\Queue\QueueStatusInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateCategoryAction
 */
class UpdateCategoryAction implements ActionInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $repository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var RequestClientInterface
     */
    private $request;

    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var ResponseTransformatorInterface
     */
    private $transformator;

    /**
     * @var AssetQueueFactory
     */
    private $assetQueueFactory;

    /**
     * @var AssetQueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ActionResultFactory
     */
    private $actionResultFactory;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var CategoryQueueRepositoryInterface
     */
    private $categoryQueueRepository;

    /**
     * @var AdditionalDataResource
     */
    private $additionalDataResource;

    /**
     * UpdateCategoryAction constructor.
     *
     * @param CategoryRepositoryInterface $repository
     * @param ManagerInterface $eventManager
     * @param CategoryFactory $categoryFactory
     * @param RequestClientInterface $requestClient
     * @param UrlBuilderInterface $urlBuilder
     * @param ResponseTransformatorInterface $transformator
     * @param AssetQueueFactory $assetQueueFactory
     * @param AssetQueueRepositoryInterface $queueRepository
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     * @param ActionResultFactory $actionResultFactory
     * @param CategoryQueueRepositoryInterface $categoryQueueRepository
     * @param AdditionalDataResource $additionalDataResource
     */
    public function __construct(
        CategoryRepositoryInterface $repository,
        ManagerInterface $eventManager,
        CategoryFactory $categoryFactory,
        RequestClientInterface $requestClient,
        UrlBuilderInterface $urlBuilder,
        ResponseTransformatorInterface $transformator,
        AssetQueueFactory $assetQueueFactory,
        AssetQueueRepositoryInterface $queueRepository,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory,
        ActionResultFactory $actionResultFactory,
        CategoryQueueRepositoryInterface $categoryQueueRepository,
        AdditionalDataResource $additionalDataResource
    ) {
        $this->repository = $repository;
        $this->eventManager = $eventManager;
        $this->categoryFactory = $categoryFactory;
        $this->request = $requestClient;
        $this->urlBuilder = $urlBuilder;
        $this->transformator = $transformator;
        $this->assetQueueFactory = $assetQueueFactory;
        $this->queueRepository = $queueRepository;
        $this->storeManager = $storeManager;
        $this->actionResultFactory = $actionResultFactory;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->categoryQueueRepository = $categoryQueueRepository;
        $this->additionalDataResource = $additionalDataResource;
    }

    /**
     * @param QueueInterface $queue
     * @param null $data
     *
     * @throws CouldNotSaveException
     * @throws LocalizedException
     *
     * @return ActionResultInterface
     */
    public function execute(QueueInterface $queue, $data = null): ActionResultInterface
    {
        $response = $this->prepareRequest($queue)->send();

        if (!$response->isSuccess()) {
            throw new LocalizedException(
                __(
                    'Invalid category data fetch ID "%1", error code: "%2"',
                    $response->getStatusCode(),
                    $queue->getPimcoreId()
                )
            );
        }

        $dto = $this->transformator->transform($response);

        try {
            /** @var Category $category */
            $category = $this->repository->getByPimId($queue->getCategoryId(), $queue->getStoreViewId());
        } catch (NoSuchEntityException $ex) {
            $category = $this->categoryFactory->create();
            $category->setStoreId($queue->getStoreViewId());
        }
        $this->storeManager->setCurrentStore($queue->getStoreViewId());
        $newCatData = $dto->getData($queue->getCategoryId());

        $imageId = $newCatData->getData('image');

        if (false !== $imageId) {
            $newCatData->unsetData('image');
        }

        $category->addData($newCatData->getData());
        $category->addData($this->additionalDataResource->getAdditionalData());
        $category->setHasDataChanges(true);

        if (!$category->getParentId()) {
            if (!$this->isParentQueued($queue, $newCatData)) {
                throw new LocalizedException(
                    __(
                        'Unable to import category. Parent category with ID "%1" is not published yet.',
                        $category->getData('pimcore_parent_id')
                    )
                );
            }

            return $this->actionResultFactory->create(['result' => ActionResultInterface::SKIPPED]);
        }

        $this->eventManager->dispatch('pimcore_category_update_before', ['category' => $category]);
        $this->repository->save($category);
        $this->eventManager->dispatch('pimcore_category_update_after', ['category' => $category]);

        //$category->move($category->getParentId(), null);

        if ($imageId) {
            $this->queueImportImageAsset($queue, $imageId);
        }

        return $this->actionResultFactory->create(['result' => ActionResultInterface::SUCCESS]);
    }

    /**
     * @param CategoryQueueInterface $queue
     *
     * @return RequestClientInterface
     */
    private function prepareRequest(CategoryQueueInterface $queue): RequestClientInterface
    {
        $this->request->setUri($this->urlBuilder->build('category'))
            ->setEventPrefix('category')
            ->setMethod('GET')
            ->setStoreViewId($queue->getStoreViewId())
            ->setQueryData(['id' => $queue->getCategoryId()]);

        return $this->request;
    }

    /**
     * @param QueueInterface $queue
     * @param DataObject $newCatData
     *
     * @return bool
     */
    protected function isParentQueued(QueueInterface $queue, DataObject $newCatData): bool
    {
        /** @var SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->criteriaBuilderFactory->create();
        $pimParentId = $newCatData->getData('pimcore_parent_id');
        $criteriaBuilder->addFilter(CategoryQueueInterface::CATEGORY_ID, $pimParentId);
        $criteriaBuilder->addFilter(CategoryQueueInterface::STATUS, QueueStatusInterface::PENDING);
        $criteriaBuilder->addFilter(CategoryQueueInterface::ACTION, $queue->getAction());
        $criteriaBuilder->addFilter(CategoryQueueInterface::STORE_VIEW_ID, $queue->getStoreViewId());
        $result = $this->categoryQueueRepository->getList($criteriaBuilder->create());

        return (bool)$result->getTotalCount();
    }

    /**
     * @param QueueInterface $queue
     * @param int $imageId
     *
     * @return void
     */
    private function queueImportImageAsset(QueueInterface $queue, int $imageId)
    {
        /** @var AssetQueueInterface $queue */
        $assetQueue = $this->assetQueueFactory->create();
        $assetQueue->setStatus(QueueStatusInterface::PENDING)
            ->setStoreViewId($queue->getStoreViewId())
            ->setAssetId($imageId)
            ->setTargetEntityId($queue->getCategoryId())
            ->setType('catalog_category/media_gallery')
            ->setAction('insert/update');

        $this->queueRepository->save($assetQueue);
    }
}
