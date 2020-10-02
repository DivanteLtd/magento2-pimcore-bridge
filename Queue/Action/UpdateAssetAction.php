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
use Divante\PimcoreIntegration\Api\RequestClientInterface;
use Divante\PimcoreIntegration\Exception\InvalidChecksumException;
use Divante\PimcoreIntegration\Exception\InvalidQueueTypeException;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\AssetInterface;
use Divante\PimcoreIntegration\Http\Response\Transformator\ResponseTransformatorInterface;
use Divante\PimcoreIntegration\Http\UrlBuilderInterface;
use Divante\PimcoreIntegration\Queue\Action\Asset\ChecksumValidator;
use Divante\PimcoreIntegration\Queue\Action\Asset\Strategy\AssetHandlerStrategyFactory;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractorFactory;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataExtractorInterface;
use Divante\PimcoreIntegration\Queue\ActionInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class UpdateAssetAction
 */
class UpdateAssetAction implements ActionInterface
{
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
     * @var TypeMetadataExtractorFactory
     */
    private $metadataExtractorFactory;

    /**
     * @var ChecksumValidator
     */
    private $checksumValidator;

    /**
     * @var AssetHandlerStrategyFactory
     */
    private $strategyFactory;

    /**
     * UpdateAssetAction constructor.
     *
     * @param RequestClientInterface $requestClient
     * @param UrlBuilderInterface $urlBuilder
     * @param ResponseTransformatorInterface $transformator
     * @param TypeMetadataExtractorFactory $metadataExtractorFactory
     * @param ChecksumValidator $checksumValidator
     * @param AssetHandlerStrategyFactory $strategyFactory
     */
    public function __construct(
        RequestClientInterface $requestClient,
        UrlBuilderInterface $urlBuilder,
        ResponseTransformatorInterface $transformator,
        TypeMetadataExtractorFactory $metadataExtractorFactory,
        ChecksumValidator $checksumValidator,
        AssetHandlerStrategyFactory $strategyFactory
    ) {
        $this->request = $requestClient;
        $this->urlBuilder = $urlBuilder;
        $this->transformator = $transformator;
        $this->metadataExtractorFactory = $metadataExtractorFactory;
        $this->checksumValidator = $checksumValidator;
        $this->strategyFactory = $strategyFactory;
    }

    /**
     * @param QueueInterface $queue
     * @param null $data
     *
     * @throws InvalidQueueTypeException
     * @throws LocalizedException
     *
     * @return ActionResultInterface
     */
    public function execute(QueueInterface $queue, $data = null): ActionResultInterface
    {
        if (!($queue instanceof AssetQueueInterface)) {
            throw new InvalidQueueTypeException(__('Invalid type, expected %1', AssetQueueInterface::class));
        }

        $response = $this->prepareRequest($queue)->send();

        if (!$response->isSuccess()) {
            throw new LocalizedException(
                __(
                    'Invalid asset data fetch ID "%1", error code: "%2"',
                    $response->getStatusCode(),
                    $queue->getPimcoreId()
                )
            );
        }

        /** @var AssetInterface $dto */
        $dto = $this->transformator->transform($response);

        if (!$this->checksumValidator->isValid($dto->getChecksum(), $dto->getDecodedImage())) {
            throw new InvalidChecksumException(__('Checksum is not valid and image might be broken.'));
        }

        /** @var TypeMetadataExtractorInterface $metadataExtractor */
        $metadataExtractor = $this->metadataExtractorFactory->create(['typeString' => $queue->getType()]);

        if (null !== $queue->getQueueType() && null !== $queue->getTargetEntityId()) {
            if (!$metadataExtractor->isValid()) {
                throw new LocalizedException(__('Invalid asset type request "%1".', $queue->getType()));
            }

            if ($metadataExtractor->getEntityType() === Product::ENTITY) {
                $strategy = $this->strategyFactory->create(AssetHandlerStrategyFactory::PRODUCT_IMAGE_IMPORT);
            }

            if ($metadataExtractor->getEntityType() === Category::ENTITY) {
                $strategy = $this->strategyFactory->create(AssetHandlerStrategyFactory::CATEGORY_IMAGE_IMPORT);
            }
        } else {
            $strategy = $this->strategyFactory->create(AssetHandlerStrategyFactory::REPLACE_ASSET);
        }

        return $strategy->execute($dto, $metadataExtractor, $queue);
    }

    /**
     * @param AssetQueueInterface $queue
     *
     * @return RequestClientInterface
     */
    private function prepareRequest(AssetQueueInterface $queue): RequestClientInterface
    {
        $this->request->setUri($this->urlBuilder->build('asset'))
            ->setEventPrefix('asset')
            ->setMethod('GET')
            ->setQueryData(['id' => $queue->getAssetId()])
            ->setStoreViewId($queue->getStoreViewId());

        return $this->request;
    }
}
