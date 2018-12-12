<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

use Divante\PimcoreIntegration\Api\Pimcore\PimcoreProductInterface;
use Divante\PimcoreIntegration\Api\Queue\AssetQueueRepositoryInterface;
use Divante\PimcoreIntegration\Model\Queue\Asset\AssetQueueFactory;
use Divante\PimcoreIntegration\Queue\Action\Asset\File;
use Divante\PimcoreIntegration\Queue\Action\Asset\FileFactory;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataBuilderFactory;
use Divante\PimcoreIntegration\Queue\Action\Asset\TypeMetadataBuilderInterface;
use Divante\PimcoreIntegration\Queue\Importer\AbstractImporter;
use Divante\PimcoreIntegration\Queue\QueueStatusInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Model\Product;

/**
 * Class MediaGalleryDataModifier
 */
class MediaGalleryDataModifier implements DataModifierInterface
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var AssetQueueRepositoryInterface
     */
    private $assetQueueRepository;

    /**
     * @var AssetQueueFactory
     */
    private $assetQueueFactory;

    /**
     * @var TypeMetadataBuilderInterface
     */
    private $metadataBuilderFactory;

    /**
     * @var AbstractImporter
     */
    private $queueImporter;

    /**
     * @var GalleryImagesUpdaterInterface
     */
    private $galleryImagesUpdater;

    /**
     * MediaGalleryDataModifier constructor.
     *
     * @param FileFactory $fileFactory
     * @param AssetQueueRepositoryInterface $assetQueueRepository
     * @param AssetQueueFactory $assetQueueFactory
     * @param TypeMetadataBuilderFactory $metadataBuilderFactory
     * @param AbstractImporter $queueImporter
     * @param GalleryImagesUpdaterInterface $galleryImagesUpdater
     */
    public function __construct(
        FileFactory $fileFactory,
        AssetQueueRepositoryInterface $assetQueueRepository,
        AssetQueueFactory $assetQueueFactory,
        TypeMetadataBuilderFactory $metadataBuilderFactory,
        AbstractImporter $queueImporter,
        GalleryImagesUpdaterInterface $galleryImagesUpdater
    ) {
        $this->fileFactory = $fileFactory;
        $this->assetQueueRepository = $assetQueueRepository;
        $this->assetQueueFactory = $assetQueueFactory;
        $this->metadataBuilderFactory = $metadataBuilderFactory;
        $this->queueImporter = $queueImporter;
        $this->galleryImagesUpdater = $galleryImagesUpdater;
    }

    /**
     * @param Product $product
     * @param PimcoreProductInterface $pimcoreProduct
     *
     * @return array
     */
    public function handle(Product $product, PimcoreProductInterface $pimcoreProduct): array
    {
        foreach ($pimcoreProduct->getData('media_gallery') as $id => $types) {
            if ($this->isAssetNew($product, $id) || $this->hasAssetChanged($product, $id, $types)) {
                $assetQueue = $this->assetQueueFactory->create();
                /** @var TypeMetadataBuilderInterface $metadataBuilder */
                $metadataBuilder = $this->metadataBuilderFactory->create([
                    'entityType' => Product::ENTITY,
                    'assetTypes' => $types,
                ]);

                $assetQueue->setAction(AbstractImporter::ACTION_INSERT_UPDATE)
                    ->setStoreViewId($product->getStoreId())
                    ->setTargetEntityId($pimcoreProduct->getData('pimcore_id'))
                    ->setType($metadataBuilder->getTypeMetadataString())
                    ->setStatus(QueueStatusInterface::PENDING)
                    ->setAssetId($id);

                if (!$this->queueImporter->isAlreadyQueued($assetQueue)) {
                    $this->assetQueueRepository->save($assetQueue);
                }
            }
        }

        $images = $product->getMediaGallery()['images'] ?? [];
        $images = $this->galleryImagesUpdater->removeUnusedImages($images, $pimcoreProduct->getMediaGallery());
        $this->updateImagesTypes($pimcoreProduct, $images);

        $pimcoreProduct->setData('media_gallery', ['images' => $images]);
        $pimcoreProduct->setCanSaveCustomOptions(true);
        $product->unsetData('media_gallery');

        return [$product, $pimcoreProduct];
    }

    /**
     * @param Product $product
     * @param string $id
     *
     * @return bool
     */
    private function isAssetNew(Product $product, string $id): bool
    {
        $isAssetNew = true;
        $mediaGalleryEntries = $product->getMediaGalleryEntries();
        /** @var File $file */
        $file = $this->fileFactory->create();
        $name = $file->getFilenameWithDispretionPath($id);

        if (null !== $mediaGalleryEntries) {
            /** @var ProductAttributeMediaGalleryEntryInterface $entry */
            foreach ($mediaGalleryEntries as $entry) {
                if (false !== strpos($entry->getFile(), $name)) {
                    $isAssetNew = false;
                }
            }
        }

        return $isAssetNew;
    }

    /**
     * @param Product $product
     * @param string $id
     * @param array $types
     *
     * @return bool
     */
    private function hasAssetChanged(Product $product, string $id, array $types): bool
    {
        /** @var File $file */
        $file = $this->fileFactory->create();
        $name = $file->getFilenameWithDispretionPath($id);
        $hasAssetTypeChanged = false;

        foreach ($types as $type) {
            if ($type === 'media_gallery') {
                continue;
            }

            if (false === strpos($product->getData($type), $name)) {
                $hasAssetTypeChanged = true;
                break;
            }
        }

        return $hasAssetTypeChanged;
    }

    /**
     * @param PimcoreProductInterface $pimcoreProduct
     * @param $images
     *
     * @return void
     */
    public function updateImagesTypes(PimcoreProductInterface $pimcoreProduct, &$images)
    {
        /** @var File $file */
        $file = $this->fileFactory->create();
        foreach ($images as &$image) {
            foreach ($pimcoreProduct->getMediaGallery() as $id => $type) {
                $name = $file->getFilenameWithDispretionPath($id);
                if (false !== strpos($image['file'], $name)) {
                    if (!isset($image['types'])) {
                        $image['types'] = $type;
                    } else {
                        array_merge($image['types'], $type);
                    }
                }
            }
        }
    }
}
