<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Product;

/**
 * Interface GalleryImagesUpdaterInterface
 */
interface GalleryImagesUpdaterInterface
{
    /**
     * @param array $prodImages
     * @param array $pimImages
     *
     * @return array
     */
    public function removeUnusedImages(array $prodImages, array $pimImages): array;
}