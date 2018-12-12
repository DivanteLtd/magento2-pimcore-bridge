<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

use Divante\PimcoreIntegration\Queue\Action\Asset\File;

/**
 * Class GalleryImagesUpdater
 */
class GalleryImagesUpdater implements GalleryImagesUpdaterInterface
{
    /**
     * @var File
     */
    private $file;

    /**
     * GalleryImagesUpdater constructor.
     *
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @param array $prodImages
     * @param array $pimImages
     *
     * @return array
     */
    public function removeUnusedImages(array $prodImages, array $pimImages): array
    {
        foreach ($prodImages as $key => $image) {
            $toRemove = true;
            foreach (array_keys($pimImages) as $imageId) {
                if (false !== strpos($image['file'], $this->file->getFilenameWithDispretionPath($imageId))) {
                    $toRemove = false;
                    break;
                }
            }

            if ($toRemove) {
                unset($prodImages[$key]);
            }
        }

        return $prodImages;
    }
}