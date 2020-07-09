<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Mateusz Bukowski <mbukowski@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Api\Queue\Data;

/**
 * Interface AssetQueueInterface
 */
interface AssetQueueInterface extends QueueInterface
{
    /**
     * Table name for stored published products from Pimcore
     */
    const SCHEMA_NAME = 'divante_pimcore_asset_queue';

    /**
     * ID of published pimcore product
     */
    const ASSET_ID = 'asset_id';
    const ASSET_TYPE = 'asset_type';
    const ASSET_TARGET_ENTITY_ID = 'entity_id';

    /**
     * @return string|null
     */
    public function getAssetId();

    /**
     * @param string $assetId
     *
     * @return AssetQueueInterface
     */
    public function setAssetId(string $assetId): AssetQueueInterface;

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return AssetQueueInterface
     */
    public function setType(string $type): AssetQueueInterface;

    /**
     * @return int|null
     */
    public function getTargetEntityId();

    /**
     * @param int $entityId
     *
     * @return AssetQueueInterface
     */
    public function setTargetEntityId(int $entityId): AssetQueueInterface;
}
