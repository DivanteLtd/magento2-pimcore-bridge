<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Http\Response\Transformator;

use Divante\PimcoreIntegration\Api\AttributeSetRepositoryInterface;
use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Divante\PimcoreIntegration\Http\Response\Transformator\Data\PropertyResolverInterface;
use Divante\PimcoreIntegration\Model\Pimcore\PimcoreProductFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Zend\Http\Response;

/**
 * Class ProductUpdateTransformator
 */
class ProductUpdateTransformator implements ResponseTransformatorInterface
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var PimcoreProductFactory
     */
    private $pimcoreProductFactory;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var ProductTypeResolver
     */
    private $productTypeResolver;

    /**
     * @var PropertyResolverInterface
     */
    private $propertyResolver;

    /**
     * ProductUpdateTransformator constructor.
     *
     * @param DataObjectFactory $dataObjectFactory
     * @param PimcoreProductFactory $pimcoreProductFactory
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param ProductTypeResolver $productTypeResolver
     * @param PropertyResolverInterface $propertyResolver
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        PimcoreProductFactory $pimcoreProductFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        ProductTypeResolver $productTypeResolver,
        PropertyResolverInterface $propertyResolver
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->pimcoreProductFactory = $pimcoreProductFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->productTypeResolver = $productTypeResolver;
        $this->propertyResolver = $propertyResolver;
    }

    /**
     * @param Response $response
     *
     * @return DataObject
     * @throws LocalizedException
     */
    public function transform(Response $response): DataObject
    {
        $dataObject = $this->dataObjectFactory->create();
        $rawResponse = json_decode($response->getBody(), true);
        /** @var array $rawData */
        $rawData = $rawResponse['data'];

        foreach ($rawData as $productId => $data) {
            /** @var PimcoreProductInterface $pimcoreProduct */
            $pimcoreProduct = $this->pimcoreProductFactory->create();
            $pimcoreProduct->setElements($data['elements']);

            $pimcoreProduct->setData('media_gallery', $this->mergeGalleryWithMediaTypes($pimcoreProduct));
            $pimcoreProduct->setData('pimcore_id', $productId);

            $pimcoreProduct->setData(
                'attribute_set_id',
                $this->attributeSetRepository->getByChecksum($data['attr_checksum']['value'])->getAttributeSetId()
            );

            $pimcoreProduct->setData('type_id', $this->productTypeResolver->resolveType($data));

            $property = $this->propertyResolver->getProperty('configurable_attributes', $data['properties'] ?? []);

            if ($property) {
                $pimcoreProduct->setData('options_property', $property);
            }

            if (isset($data['type']) && $data['type'] === 'variant' && isset($data['parentId'])) {
                $pimcoreProduct->setData('parent_id', $data['parentId']);
                $pimcoreProduct->setData('product_type', $data['type']);
            }

            $dataObject->setData($productId, $pimcoreProduct);
        }

        return $dataObject;
    }

    /**
     * @param PimcoreProductInterface $pimcoreProduct
     *
     * @return array
     */
    private function mergeGalleryWithMediaTypes(PimcoreProductInterface $pimcoreProduct): array
    {
        $mediaTypes = $this->getMediaTypes();
        $toGallery = [];

        foreach ($mediaTypes as $type) {
            if (null === $pimcoreProduct->getData($type)) {
                continue;
            }

            $toGallery[$type] = $pimcoreProduct->getData($type);
            $pimcoreProduct->unsetData($type);
        }

        $mediaGallery = $pimcoreProduct->getData('media_gallery') ?? [];
        $mediaGallery = array_merge($mediaGallery, $toGallery);
        $newMediaGallery = [];

        foreach ($mediaGallery as $key => $item) {
            if (!$item) {
                continue;
            }
            if (in_array((string) $key, $mediaTypes, true)) {
                $newMediaGallery[$item][] = $key;
            } else {
                $newMediaGallery[$item] = $newMediaGallery[$item] ?? ['media_gallery'];
            }
        }

        return $newMediaGallery;
    }

    /**
     * @return array|string[]
     */
    public function getMediaTypes(): array
    {
        return ['small_image', 'thumbnail', 'image'];
    }
}
