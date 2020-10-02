<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action;

use Divante\PimcoreIntegration\Api\ProductRepositoryInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\ProductQueueInterface;
use Divante\PimcoreIntegration\Api\Queue\Data\QueueInterface;
use Divante\PimcoreIntegration\Api\RequestClientInterface;
use Divante\PimcoreIntegration\Exception\InvalidDataStructureException;
use Divante\PimcoreIntegration\Exception\InvalidTypeException;
use Divante\PimcoreIntegration\Http\Response\Transformator\ResponseTransformatorInterface;
use Divante\PimcoreIntegration\Http\UrlBuilderInterface;
use Divante\PimcoreIntegration\Queue\Action\Product\DataModifierInterface;
use Divante\PimcoreIntegration\Queue\Action\TypeStrategy\TypeStrategyFactory;
use Divante\PimcoreIntegration\Queue\ActionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Http\Response;

/**
 * Class UpdateProductAction
 */
class UpdateProductAction implements ActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RequestClientInterface
     */
    private $requestClient;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResponseTransformatorInterface
     */
    private $transformator;

    /**
     * @var UrlBuilderInterface
     */
    private $urlBuilder;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var array
     */
    private $dataModifiers;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var TypeStrategyFactory
     */
    private $typeStrategyFactory;

    /**
     * @var ActionResultFactory
     */
    private $actionResultFactory;

    /**
     * UpdateProductAction constructor.
     *
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param RequestClientInterface $requestClient
     * @param ResponseTransformatorInterface $transformator
     * @param StoreManagerInterface $storeManager
     * @param UrlBuilderInterface $urlBuilder
     * @param ManagerInterface $manager
     * @param TypeStrategyFactory $typeStrategyFactory
     * @param ActionResultFactory $actionResultFactory
     * @param array $dataModifiers
     */
    public function __construct(
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        RequestClientInterface $requestClient,
        ResponseTransformatorInterface $transformator,
        StoreManagerInterface $storeManager,
        UrlBuilderInterface $urlBuilder,
        ManagerInterface $manager,
        TypeStrategyFactory $typeStrategyFactory,
        ActionResultFactory $actionResultFactory,
        array $dataModifiers = []
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->requestClient = $requestClient;
        $this->storeManager = $storeManager;
        $this->transformator = $transformator;
        $this->urlBuilder = $urlBuilder;
        $this->eventManager = $manager;
        $this->typeStrategyFactory = $typeStrategyFactory;
        $this->dataModifiers = $dataModifiers;
        $this->actionResultFactory = $actionResultFactory;
    }

    /**
     * @param QueueInterface $queue
     * @param mixed $data
     *
     * @throws InvalidTypeException
     * @throws LocalizedException
     *
     * @return ActionResultInterface
     */
    public function execute(QueueInterface $queue, $data = null): ActionResultInterface
    {
        $response = $this->getProductDataFromPimcore($queue);

        if (!$response->isSuccess()) {
            throw new LocalizedException(
                __(
                    'Invalid product data fetch ID "%1", error code: "%2", context: %3',
                    $queue->getPimcoreId(),
                    $response->getStatusCode(),
                    $response->getContent()
                )
            );
        }

        $this->storeManager->setCurrentStore($queue->getStoreViewId());
        $rawResponse = json_decode($response->getBody(), true);

        $this->eventManager->dispatch('catalog_product_attribute_set_evaluate', [
            'products' => $rawResponse['data'] ?? [],
        ]);

        $this->eventManager->dispatch('catalog_product_attributes_evaluate', [
            'products' => $rawResponse['data'] ?? [],
        ]);

        /** @var array $transformedData */
        $transformedData = $this->transformator->transform($response)->getData();
        $pimcoreId = $this->getPimcoreIdFromTransformedData($transformedData);

        if (!$pimcoreId) {
            throw new InvalidDataStructureException(
                __('Invalid pimcore product response data structure. Missing pimcore object ID.')
            );
        }

        $pimcoreProduct = $transformedData[$pimcoreId];

        try {
            $product = $this->productRepository->getByPimId($pimcoreId);
        } catch (NoSuchEntityException $ex) {
            $product = $this->createProduct();
        }

        foreach ($this->dataModifiers as $dataModifier) {
            if (!($dataModifier instanceof DataModifierInterface)) {
                $invalid = get_class($dataModifier);
                throw new InvalidTypeException(
                    __("Invalid dataModifier '%1' should implement DataModifierInterface", $invalid)
                );
            }

            $dataModifier->handle($product, $pimcoreProduct);
        }

        $product->addData($pimcoreProduct->getData());
        // We manage linking category, related_products in after event
        $product->unsetData('category_ids');
        $product->unsetData('related_products');
        $product->setHasDataChanges(true);

        $strategy = $this->typeStrategyFactory->create($product->getTypeId());
        $product = $strategy->execute($product);

        $this->eventManager->dispatch(
            'pimcore_product_update_before',
            ['product' => $product, 'pimcore' => $pimcoreProduct]
        );

        if ($product->getIsSkip()) {
            return $this->actionResultFactory->create(['result' => ActionResultInterface::SKIPPED]);
        }

        $saved = $this->productRepository->save($product);
        $saved->addData($product->getData());

        $this->eventManager->dispatch(
            'pimcore_product_update_after',
            ['product' => $saved, 'pimcore' => $pimcoreProduct]
        );

        return $this->actionResultFactory->create(['result' => ActionResultInterface::SUCCESS]);
    }

    /**
     * @param QueueInterface|ProductQueueInterface $queue
     *
     * @return Response
     */
    private function getProductDataFromPimcore(QueueInterface $queue): Response
    {
        return $this->requestClient->setUri($this->urlBuilder->build('product'))
            ->setEventPrefix('product')
            ->setMethod('GET')
            ->setStoreViewId($queue->getStoreViewId())
            ->setQueryData(['id' => $queue->getProductId()])
            ->send();
    }

    /**
     * @param array $transformedData
     *
     * @return string|null
     */
    protected function getPimcoreIdFromTransformedData(array $transformedData)
    {
        reset($transformedData);

        return key($transformedData);
    }

    /**
     * @return Product
     */
    private function createProduct(): Product
    {
        $product = $this->productFactory->create();
        $product->setStatus(Status::STATUS_DISABLED);

        return $product;
    }
}
