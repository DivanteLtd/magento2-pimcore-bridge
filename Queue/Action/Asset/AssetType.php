<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Queue\Action\Asset;

/**
 * Class AssetType
 */
class AssetType
{
    /**
     * Available asset types to import
     */
    const THUMBNAIL     = 'thumbnail';
    const BASE_IMAGE    = 'image';
    const SMALL_IMAGE   = 'small_image';
    const GALLERY_IMAGE = 'media_gallery';

    /**
     *
     * @return array
     */
    public static function getAssetTypes(): array
    {
        return [
            self::THUMBNAIL,
            self::BASE_IMAGE,
            self::SMALL_IMAGE,
            self::GALLERY_IMAGE,
        ];
    }
}
