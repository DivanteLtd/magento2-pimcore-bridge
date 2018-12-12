<?php
/**
 * @package  Divante\PimcoreIntegration
 * @author Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Asset;

/**
 * Interface AssetTypeInterface
 */
interface AssetTypeInterface
{
    /**
     * Asset types related to entities
     */
    const TYPE_CATEGORY = 'category';
    const TYPE_PRODUCT = 'product';
    const TYPE_CMS = 'cms';
}
