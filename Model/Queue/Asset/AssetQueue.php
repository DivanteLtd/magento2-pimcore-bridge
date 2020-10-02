<?php
/**
 * @package   Divante\PimcoreIntegration
 * @author    Bartosz Herba <bherba@divante.pl>
 * @copyright 2018 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\PimcoreIntegration\Model\Queue\Asset;

use Divante\PimcoreIntegration\Api\Queue\Data\AssetQueueInterface;
use Divante\PimcoreIntegration\Model\AbstractQueue;
use Divante\PimcoreIntegration\Model\Queue\Asset\ResourceModel\AssetQueue as AssetQueueResource;

/**
 * Class AssetQueue
 */
class AssetQueue extends AbstractQueue implements AssetQueueInterface
{
    /**
     * Queue type value
     */
    const TYPE = 'asset';

    /**
     * @return int|null
     */
    public function getPimcoreId()
    {
        return $this->getAssetId();
    }

    /**
     * @return string|null
     */
    public function getAssetId()
    {
        return $this->getData(self::ASSET_ID);
    }

    /**
     * @param string $assetId
     *
     * @return $this
     */
    public function setAssetId(string $assetId): AssetQueueInterface
    {
        $this->setData(self::ASSET_ID, $assetId);

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return (string) $this->getData(self::ASSET_TYPE);
    }

    /**
     * @param string $type
     *
     * @return AssetQueueInterface
     */
    public function setType(string $type): AssetQueueInterface
    {
        $this->setData(self::ASSET_TYPE, $type);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTargetEntityId()
    {
        return $this->getData(self::ASSET_TARGET_ENTITY_ID);
    }

    /**
     * @param int $entityId
     *
     * @return AssetQueueInterface
     */
    public function setTargetEntityId(int $entityId): AssetQueueInterface
    {
        $this->setData(self::ASSET_TARGET_ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @return string
     */
    public function getQueueType(): string
    {
        return self::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(AssetQueueResource::class);
    }
}
